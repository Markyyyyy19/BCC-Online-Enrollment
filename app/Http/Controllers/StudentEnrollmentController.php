<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\Program;
use App\Models\Subject;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StudentEnrollmentController extends Controller
{
    public function index(Request $request): View
    {
        $enrollments = Enrollment::query()
            ->where('student_id', Auth::id())
            ->with(['program', 'major', 'subjects'])
            ->latest('submitted_at')
            ->paginate($this->tablePageSize($request))->withQueryString();

        return view('student.enrollments.index', compact('enrollments'));
    }

    public function create(Request $request): View
    {
        $programs = Program::query()->where('is_active', true)->orderBy('code')->get();
        $selectedProgram = Program::query()->whereKey($request->integer('program_id'))->where('is_active', true)->first();
        $schoolYear = now()->year.'-'.(now()->year + 1);
        $requestedSchoolYear = $request->query('school_year');
        if (is_string($requestedSchoolYear) && preg_match('/^\d{4}-\d{4}$/', $requestedSchoolYear)) {
            $schoolYear = $requestedSchoolYear;
        }
        $yearLevel = $request->integer('year_level', 1);
        $semester = $request->query('semester', '1st');
        $majors = $selectedProgram
            ? DB::table('program_majors')->where('program_id', $selectedProgram->id)->where('is_active', true)->orderBy('name')->get()
            : collect();
        $selectedMajorId = $request->integer('major_id') ?: null;
        $selectedMajor = $majors->firstWhere('id', $selectedMajorId);
        $subjects = collect();

        if ($selectedProgram && in_array($yearLevel, range(1, 6), true) && in_array($semester, ['1st', '2nd', 'Summer'], true)) {
            $subjectQuery = Subject::query()
                ->where('program_id', $selectedProgram->id)
                ->where('year_level', $yearLevel)
                ->where('semester', $semester)
                ->where('is_active', true)
                ->orderBy('code');

            if ($majors->isNotEmpty()) {
                if ($selectedMajor) {
                    $subjectQuery->where('major_id', $selectedMajorId);
                    $subjects = $subjectQuery->get();
                }
            } else {
                $subjects = $subjectQuery->whereNull('major_id')->get();
            }
        }

        return view('student.enrollments.create', [
            'programs' => $programs,
            'schoolYear' => $schoolYear,
            'selectedProgram' => $selectedProgram,
            'majors' => $majors,
            'selectedMajorId' => $selectedMajorId,
            'selectedYearLevel' => $yearLevel,
            'selectedSemester' => $semester,
            'subjects' => $subjects,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'program_id' => ['required', 'integer', 'exists:programs,id'],
            'major_id' => ['nullable', 'integer', 'exists:program_majors,id'],
            'school_year' => ['required', 'regex:/^\d{4}-\d{4}$/'],
            'semester' => ['required', 'in:1st,2nd,Summer'],
            'year_level' => ['required', 'integer', 'between:1,6'],
            'subject_ids' => ['required', 'array', 'min:1'],
            'subject_ids.*' => ['required', 'integer', 'distinct', 'exists:subjects,id'],
        ]);

        $program = Program::query()->whereKey($validated['program_id'])->where('is_active', true)->first();

        if (! $program) {
            return back()->withErrors(['program_id' => 'Choose an active program.'])->withInput();
        }

        $majors = DB::table('program_majors')->where('program_id', $program->id)->where('is_active', true)->get();

        if ($majors->isNotEmpty() && ! $majors->contains('id', $validated['major_id'] ?? null)) {
            return back()->withErrors(['major_id' => 'Choose a major for this program.'])->withInput();
        }

        $subjectCount = Subject::query()
            ->whereIn('id', $validated['subject_ids'])
            ->where('program_id', $program->id)
            ->where('year_level', $validated['year_level'])
            ->where('semester', $validated['semester'])
            ->where('is_active', true)
            ->when($majors->isNotEmpty(), fn ($query) => $query->where('major_id', $validated['major_id']))
            ->when($majors->isEmpty(), fn ($query) => $query->whereNull('major_id'))
            ->count();

        if ($subjectCount !== count($validated['subject_ids'])) {
            return back()->withErrors(['subject_ids' => 'Select only active subjects from your chosen program, major, year, and semester.'])->withInput();
        }

        $duplicate = Enrollment::query()
            ->where('student_id', Auth::id())
            ->where('school_year', $validated['school_year'])
            ->where('semester', $validated['semester'])
            ->exists();

        if ($duplicate) {
            return back()->withErrors(['semester' => 'You already have an application for that term.'])->withInput();
        }

        try {
            DB::transaction(function () use ($validated): void {
                $enrollment = Enrollment::query()->create([
                    'student_id' => Auth::id(),
                    'program_id' => $validated['program_id'],
                    'major_id' => $validated['major_id'] ?? null,
                    'school_year' => $validated['school_year'],
                    'semester' => $validated['semester'],
                    'year_level' => $validated['year_level'],
                    // Retain the existing default for the legacy, non-null database column.
                    'student_type' => 'Continuing',
                    'status' => 'submitted',
                    'submitted_at' => now(),
                    'updated_at' => now(),
                ]);
                $enrollment->subjects()->sync($validated['subject_ids']);
            });
        } catch (QueryException $exception) {
            if ($exception->getCode() !== '23505') {
                throw $exception;
            }

            return back()->withErrors(['semester' => 'You already have an application for that term.'])->withInput();
        }

        return redirect()->route('student.enrollments.index')->with('success', 'Your enrollment application has been submitted.');
    }

    public function withdraw(Enrollment $enrollment): RedirectResponse
    {
        abort_unless($enrollment->student_id === Auth::id(), 404);

        if (! in_array($enrollment->status, ['draft', 'submitted'], true)) {
            return back()->withErrors(['enrollment' => 'This application can no longer be withdrawn.']);
        }

        $enrollment->update(['status' => 'withdrawn', 'updated_at' => now()]);

        return back()->with('success', 'Your application has been withdrawn.');
    }

    public function grades(Request $request): View
    {
        $grades = DB::table('grades')
            ->join('enrollments', 'grades.enrollment_id', '=', 'enrollments.id')
            ->join('subjects', 'grades.subject_id', '=', 'subjects.id')
            ->join('programs', 'enrollments.program_id', '=', 'programs.id')
            ->where('enrollments.student_id', Auth::id())
            ->select('grades.*', 'subjects.code', 'subjects.title', 'subjects.units', 'enrollments.school_year', 'enrollments.semester', 'programs.code as program_code')
            ->orderByDesc('enrollments.school_year')
            ->orderBy('subjects.code')
            ->paginate($this->tablePageSize($request))
            ->withQueryString();

        return view('student.grades', compact('grades'));
    }

    public function subjects(Request $request): View
    {
        $subjects = Subject::query()
            ->with('program')
            ->where('is_active', true)
            ->orderBy('program_id')
            ->orderBy('year_level')
            ->orderBy('code')
            ->paginate($this->tablePageSize($request))->withQueryString();

        return view('student.subjects', compact('subjects'));
    }

    private function tablePageSize(Request $request): int
    {
        return 20;
    }
}
