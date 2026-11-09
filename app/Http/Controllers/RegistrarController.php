<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\Program;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegistrarController extends Controller
{
    public function index(Request $request): View
    {
        $statuses = ['submitted', 'under_review', 'approved', 'rejected', 'withdrawn'];
        $selectedStatus = $request->query('status', 'all');
        $query = Enrollment::query()->with(['student', 'program', 'major', 'subjects'])->latest('submitted_at');

        if (in_array($selectedStatus, $statuses, true)) {
            $query->where('status', $selectedStatus);
        } else {
            $selectedStatus = 'all';
        }

        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $query->whereHas('student', function ($studentQuery) use ($search): void {
                $studentQuery->where(function ($studentQuery) use ($search): void {
                    $studentQuery->where('student_number', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");

                    if (ctype_digit($search)) {
                        $studentQuery->orWhere('id', (int) $search);
                    }
                });
            });
        }

        return view('registrar.applications', [
            'enrollments' => $query->paginate($this->tablePageSize($request))->withQueryString(),
            'selectedStatus' => $selectedStatus,
            'search' => $search,
            'pendingCount' => Enrollment::query()->whereIn('status', ['submitted', 'under_review'])->count(),
        ]);
    }

    public function review(Request $request, Enrollment $enrollment): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:under_review,approved,rejected'],
            'registrar_note' => ['nullable', 'string', 'max:500'],
        ]);

        $enrollment->update($validated + [
            'reviewed_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Application status updated.');
    }

    public function students(Request $request): View
    {
        $studentNumber = trim((string) $request->query('student_number', ''));

        $students = User::query()
            ->where('role', 'student')
            ->when($studentNumber !== '', function ($query) use ($studentNumber): void {
                $query->where('student_number', 'like', "%{$studentNumber}%");
            })
            ->addSelect([
                'latest_program_code' => DB::table('enrollments')
                    ->join('programs', 'enrollments.program_id', '=', 'programs.id')
                    ->select('programs.code')
                    ->whereColumn('enrollments.student_id', 'users.id')
                    ->orderByDesc('enrollments.submitted_at')
                    ->limit(1),
                'latest_enrollment_status' => DB::table('enrollments')
                    ->select('status')
                    ->whereColumn('enrollments.student_id', 'users.id')
                    ->orderByDesc('enrollments.submitted_at')
                    ->limit(1),
            ])
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate($this->tablePageSize($request))->withQueryString();

        return view('registrar.students', [
            'students' => $students,
            'studentNumber' => $studentNumber,
        ]);
    }

    public function updateStudent(Request $request, User $student): RedirectResponse
    {
        abort_unless($student->role === 'student', 404);

        $validated = $request->validate([
            'student_number' => ['nullable', 'string', 'min:4', 'max:30', 'unique:users,student_number,'.$student->id],
            'first_name' => ['required', 'string', 'min:2', 'max:80'],
            'last_name' => ['required', 'string', 'min:2', 'max:80'],
            'email' => ['required', 'email', 'max:120', 'unique:users,email,'.$student->id],
        ]);

        $student->update($validated);

        return back()->with('success', 'Student profile updated.');
    }

    public function toggleStudentAccess(User $student): RedirectResponse
    {
        abort_unless($student->role === 'student', 404);
        $student->update(['is_active' => ! $student->is_active]);

        return back()->with('success', $student->is_active ? 'Student access restored.' : 'Student access deactivated.');
    }

    public function programs(Request $request): View
    {
        return view('registrar.programs', [
            'programs' => Program::query()->withCount('subjects')->orderBy('code')->paginate($this->tablePageSize($request))->withQueryString(),
        ]);
    }

    public function storeProgram(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20', 'unique:programs,code'],
            'name' => ['required', 'string', 'max:160'],
            'department' => ['required', 'string', 'max:120'],
            'duration_years' => ['required', 'integer', 'between:1,6'],
        ]);

        Program::query()->create($validated + ['is_active' => true]);

        return back()->with('success', 'Program added to the catalog.');
    }

    public function updateProgram(Request $request, Program $program): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20', 'unique:programs,code,'.$program->id],
            'name' => ['required', 'string', 'max:160'],
            'department' => ['required', 'string', 'max:120'],
            'duration_years' => ['required', 'integer', 'between:1,6'],
        ]);

        $program->update($validated);

        return back()->with('success', 'Program details updated.');
    }

    public function toggleProgram(Program $program): RedirectResponse
    {
        $program->update(['is_active' => ! $program->is_active]);

        return back()->with('success', $program->is_active ? 'Program is now open for applications.' : 'Program is now archived.');
    }

    public function subjects(Request $request): View
    {
        $programId = $request->integer('program_id') ?: null;
        $yearLevel = $request->integer('year_level') ?: null;
        $semester = $request->input('semester');
        if (! in_array($semester, ['1st', '2nd', 'Summer'], true)) {
            $semester = null;
        }

        $query = Subject::query()
            ->with('program')
            ->when($programId, fn ($q) => $q->where('program_id', $programId))
            ->when($yearLevel, fn ($q) => $q->where('year_level', $yearLevel))
            ->when($semester, fn ($q) => $q->where('semester', $semester))
            ->orderBy('program_id')
            ->orderBy('year_level')
            ->orderBy('semester')
            ->orderBy('code');

        return view('registrar.subjects', [
            'subjects' => $query->paginate($this->tablePageSize($request))->withQueryString(),
            'programs' => Program::query()->where('is_active', true)->orderBy('code')->get(),
            'selectedProgramId' => $programId,
            'selectedYearLevel' => $yearLevel,
            'selectedSemester' => $semester,
            'majors' => DB::table('program_majors')
                ->join('programs', 'program_majors.program_id', '=', 'programs.id')
                ->where('program_majors.is_active', true)
                ->select('program_majors.*', 'programs.code as program_code')
                ->orderBy('program_majors.name')
                ->get(),
        ]);
    }

    public function storeSubject(Request $request): RedirectResponse
    {
        $validated = $this->validateSubject($request);
        $now = now();

        Subject::query()->create($validated + ['is_active' => true, 'is_capstone' => false, 'created_at' => $now, 'updated_at' => $now]);

        return back()->with('success', 'Subject added to the curriculum.');
    }

    public function updateSubject(Request $request, Subject $subject): RedirectResponse
    {
        $validated = $this->validateSubject($request, $subject);
        $subject->update($validated + ['updated_at' => now()]);

        return back()->with('success', 'Subject details updated.');
    }

    public function toggleSubject(Subject $subject): RedirectResponse
    {
        $subject->update(['is_active' => ! $subject->is_active, 'updated_at' => now()]);

        return back()->with('success', $subject->is_active ? 'Subject restored.' : 'Subject archived.');
    }

    private function validateSubject(Request $request, ?Subject $subject = null): array
    {
        $validated = $request->validate([
            'program_id' => ['required', 'integer', 'exists:programs,id'],
            'major_id' => ['nullable', 'integer', 'exists:program_majors,id'],
            'code' => ['required', 'string', 'max:40'],
            'title' => ['required', 'string', 'max:255'],
            'units' => ['required', 'numeric', 'between:1,9'],
            'year_level' => ['required', 'integer', 'between:1,6'],
            'semester' => ['required', 'in:1st,2nd,Summer'],
        ]);

        $majorBelongsToProgram = empty($validated['major_id']) || DB::table('program_majors')
            ->where('id', $validated['major_id'])
            ->where('program_id', $validated['program_id'])
            ->where('is_active', true)
            ->exists();

        if (! $majorBelongsToProgram) {
            throw ValidationException::withMessages(['major_id' => 'Choose a major belonging to the selected program.']);
        }

        $duplicate = Subject::query()
            ->where('program_id', $validated['program_id'])
            ->where('code', $validated['code'])
            ->when($subject, fn ($query) => $query->where('id', '<>', $subject->id))
            ->exists();

        if ($duplicate) {
            throw ValidationException::withMessages(['code' => 'That subject code is already used in this program.']);
        }

        return $validated;
    }

    public function grades(Request $request): View
    {
        $studentId = $request->integer('student_id') ?: null;
        $search = trim((string) $request->input('search', ''));
        $programId = $request->integer('program_id') ?: null;

        // All students with approved enrollments for quick switching dropdown
        $eligibleStudents = User::query()
            ->where('role', 'student')
            ->whereHas('enrollments', fn ($q) => $q->where('status', 'approved'))
            ->with(['enrollments' => fn ($q) => $q->where('status', 'approved')->with('program')])
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $selectedStudent = null;
        $studentEnrollments = collect();
        $gradesBySubject = collect();
        $totalSubjectsCount = 0;
        $gradedSubjectsCount = 0;
        $pendingSubjectsCount = 0;

        if ($studentId) {
            $selectedStudent = User::query()
                ->where('role', 'student')
                ->where('id', $studentId)
                ->whereHas('enrollments', fn ($q) => $q->where('status', 'approved'))
                ->first();

            if ($selectedStudent) {
                $studentEnrollments = Enrollment::query()
                    ->where('student_id', $selectedStudent->id)
                    ->where('status', 'approved')
                    ->with(['program', 'major', 'subjects'])
                    ->orderByDesc('school_year')
                    ->orderByDesc('semester')
                    ->get();

                $enrollmentIds = $studentEnrollments->pluck('id');
                $gradesBySubject = DB::table('grades')
                    ->whereIn('enrollment_id', $enrollmentIds)
                    ->get()
                    ->keyBy(fn ($item) => "{$item->enrollment_id}-{$item->subject_id}");

                $totalSubjectsCount = $studentEnrollments->sum(fn ($e) => $e->subjects->count());
                $gradedSubjectsCount = DB::table('grades')
                    ->whereIn('enrollment_id', $enrollmentIds)
                    ->whereNotNull('final_grade')
                    ->count();
                $pendingSubjectsCount = max(0, $totalSubjectsCount - $gradedSubjectsCount);

                $allGradeRows = $studentEnrollments->flatMap(function ($enrollment) {
                    return $enrollment->subjects->map(fn ($subject) => [
                        'enrollment' => $enrollment,
                        'subject' => $subject,
                    ]);
                })->values();
                $gradeRows = new \Illuminate\Pagination\LengthAwarePaginator(
                    $allGradeRows->forPage($request->integer('page', 1), $this->tablePageSize($request))->values(),
                    $allGradeRows->count(),
                    $this->tablePageSize($request),
                    $request->integer('page', 1),
                    ['path' => route('registrar.grades'), 'query' => $request->query()]
                );
            }
        }

        // When no student is selected, prepare the student directory list with progress metrics
        $studentsList = null;
        if (! $selectedStudent) {
            $studentsQuery = User::query()
                ->where('role', 'student')
                ->whereHas('enrollments', function ($q) use ($programId) {
                    $q->where('status', 'approved')
                        ->when($programId, fn ($sub) => $sub->where('program_id', $programId));
                })
                ->when($search, function ($query, $search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('first_name', 'ilike', "%{$search}%")
                            ->orWhere('last_name', 'ilike', "%{$search}%")
                            ->orWhere('student_number', 'ilike', "%{$search}%");
                    });
                })
                ->with(['enrollments' => function ($q) {
                    $q->where('status', 'approved')
                        ->with(['program', 'subjects'])
                        ->orderByDesc('school_year')
                        ->orderByDesc('semester');
                }])
                ->orderBy('last_name')
                ->orderBy('first_name');

            $studentsList = $studentsQuery->paginate($this->tablePageSize($request))->withQueryString();

            $pageEnrollmentIds = $studentsList->getCollection()->flatMap(fn ($s) => $s->enrollments->pluck('id'))->unique();
            $gradedCountsByEnrollment = DB::table('grades')
                ->whereIn('enrollment_id', $pageEnrollmentIds)
                ->whereNotNull('final_grade')
                ->select('enrollment_id', DB::raw('count(*) as count'))
                ->groupBy('enrollment_id')
                ->pluck('count', 'enrollment_id');

            $studentsList->getCollection()->transform(function ($student) use ($gradedCountsByEnrollment) {
                $totalSubj = $student->enrollments->sum(fn ($e) => $e->subjects->count());
                $gradedSubj = $student->enrollments->sum(fn ($e) => $gradedCountsByEnrollment->get($e->id, 0));
                $student->total_subjects = $totalSubj;
                $student->graded_subjects = $gradedSubj;
                $student->pending_subjects = max(0, $totalSubj - $gradedSubj);

                return $student;
            });
        }

        $programs = Program::query()->where('is_active', true)->orderBy('code')->get();

        return view('registrar.grades', [
            'selectedStudent' => $selectedStudent,
            'selectedStudentId' => $selectedStudent?->id,
            'studentEnrollments' => $studentEnrollments,
            'gradesBySubject' => $gradesBySubject,
            'totalSubjectsCount' => $totalSubjectsCount,
            'gradedSubjectsCount' => $gradedSubjectsCount,
            'pendingSubjectsCount' => $pendingSubjectsCount,
            'gradeRows' => $gradeRows ?? collect(),
            'students' => $studentsList,
            'eligibleStudents' => $eligibleStudents,
            'programs' => $programs,
            'search' => $search,
            'selectedProgramId' => $programId,
        ]);
    }

    public function updateGrade(Request $request, Enrollment $enrollment, Subject $subject): RedirectResponse
    {
        $validated = $request->validate([
            'midterm' => ['nullable', 'numeric', 'between:1,5'],
            'final_term' => ['nullable', 'numeric', 'between:1,5'],
            'remarks' => ['nullable', 'string', 'in:In progress,Passed,Failed,Incomplete'],
        ]);

        $isEligible = DB::table('enrollment_subjects')
            ->join('enrollments', 'enrollment_subjects.enrollment_id', '=', 'enrollments.id')
            ->where('enrollment_subjects.enrollment_id', $enrollment->id)
            ->where('enrollment_subjects.subject_id', $subject->id)
            ->where('enrollments.status', 'approved')
            ->exists();

        abort_unless($isEligible, 404);

        $midterm = isset($validated['midterm']) && $validated['midterm'] !== '' ? (float) $validated['midterm'] : null;
        $finalTerm = isset($validated['final_term']) && $validated['final_term'] !== '' ? (float) $validated['final_term'] : null;

        $complete = ($midterm !== null && $finalTerm !== null);
        $finalGrade = $complete ? round(($midterm + $finalTerm) / 2, 2) : null;

        $chosenRemark = $validated['remarks'] ?? null;
        if ($chosenRemark === 'Incomplete') {
            $remarks = 'Incomplete';
        } elseif ($finalGrade !== null) {
            $remarks = $finalGrade <= 3.0 ? 'Passed' : 'Failed';
        } else {
            $remarks = 'In progress';
        }

        DB::table('grades')->updateOrInsert(
            ['enrollment_id' => $enrollment->id, 'subject_id' => $subject->id],
            [
                'preliminary' => null,
                'midterm' => $midterm,
                'final_term' => $finalTerm,
                'final_grade' => $finalGrade,
                'remarks' => $remarks,
                'encoded_by' => auth()->id(),
                'updated_at' => now(),
            ],
        );

        return redirect()->route('registrar.grades', ['student_id' => $enrollment->student_id])
            ->with('success', "Grade saved for {$subject->code}.");
    }

    private function tablePageSize(Request $request): int
    {
        return 20;
    }
}
