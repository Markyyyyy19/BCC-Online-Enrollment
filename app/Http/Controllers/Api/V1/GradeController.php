<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\GradeResource;
use App\Models\Enrollment;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class GradeController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->validate([
            'student_id' => ['sometimes', 'integer'],
            'school_year' => ['sometimes', 'regex:/^\d{4}-\d{4}$/'],
            'semester' => ['sometimes', 'in:1st,2nd,Summer'],
            'per_page' => ['sometimes', 'integer', 'between:1,100'],
        ]);

        $query = $this->gradeQuery();

        if (Auth::user()->role === 'student') {
            $query->where('enrollments.student_id', Auth::id());
        } elseif (isset($filters['student_id'])) {
            $query->where('enrollments.student_id', $filters['student_id']);
        }

        $grades = $query
            ->when(isset($filters['school_year']), fn ($builder) => $builder->where('enrollments.school_year', $filters['school_year']))
            ->when(isset($filters['semester']), fn ($builder) => $builder->where('enrollments.semester', $filters['semester']))
            ->orderBy('users.last_name')
            ->orderBy('subjects.code')
            ->paginate($filters['per_page'] ?? 30)
            ->withQueryString();

        return GradeResource::collection($grades);
    }

    public function forStudent(User $student): AnonymousResourceCollection
    {
        $this->authorizeStudentRead($student);

        return GradeResource::collection(
            $this->gradeQuery()
                ->where('enrollments.student_id', $student->id)
                ->orderBy('enrollments.school_year')
                ->orderBy('subjects.code')
                ->get(),
        );
    }

    public function update(Request $request, Enrollment $enrollment, Subject $subject): GradeResource
    {
        $validated = $request->validate([
            'midterm' => ['nullable', 'numeric', 'between:1,5'],
            'final_term' => ['nullable', 'numeric', 'between:1,5'],
            'remarks' => ['sometimes', 'in:In progress,Passed,Failed,Incomplete'],
        ]);

        $isEligible = DB::table('enrollment_subjects')
            ->join('enrollments', 'enrollment_subjects.enrollment_id', '=', 'enrollments.id')
            ->where('enrollment_subjects.enrollment_id', $enrollment->id)
            ->where('enrollment_subjects.subject_id', $subject->id)
            ->where('enrollments.status', 'approved')
            ->exists();

        abort_unless($isEligible, Response::HTTP_NOT_FOUND);

        $existingGrade = DB::table('grades')
            ->where('enrollment_id', $enrollment->id)
            ->where('subject_id', $subject->id)
            ->first();

        $midterm = array_key_exists('midterm', $validated) ? $validated['midterm'] : $existingGrade?->midterm;
        $finalTerm = array_key_exists('final_term', $validated) ? $validated['final_term'] : $existingGrade?->final_term;

        $midterm = $midterm !== null && $midterm !== '' ? (float) $midterm : null;
        $finalTerm = $finalTerm !== null && $finalTerm !== '' ? (float) $finalTerm : null;

        $complete = ($midterm !== null && $finalTerm !== null);
        $finalGrade = $complete ? round(($midterm + $finalTerm) / 2, 2) : null;

        $chosenRemark = $validated['remarks'] ?? $existingGrade?->remarks;
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
                'encoded_by' => Auth::id(),
                'updated_at' => now(),
            ],
        );

        return new GradeResource(
            $this->gradeQuery()
                ->where('enrollments.id', $enrollment->id)
                ->where('subjects.id', $subject->id)
                ->firstOrFail(),
        );
    }

    private function gradeQuery(): Builder
    {
        return DB::table('enrollments')
            ->join('enrollment_subjects', 'enrollments.id', '=', 'enrollment_subjects.enrollment_id')
            ->join('users', 'enrollments.student_id', '=', 'users.id')
            ->join('programs', 'enrollments.program_id', '=', 'programs.id')
            ->join('subjects', 'enrollment_subjects.subject_id', '=', 'subjects.id')
            ->leftJoin('grades', function ($join): void {
                $join->on('grades.enrollment_id', '=', 'enrollment_subjects.enrollment_id')
                    ->on('grades.subject_id', '=', 'enrollment_subjects.subject_id');
            })
            ->where('enrollments.status', 'approved')
            ->select(
                'enrollments.id as enrollment_id',
                'enrollments.student_id',
                'enrollments.school_year',
                'enrollments.semester',
                'users.student_number',
                'users.first_name',
                'users.last_name',
                'programs.code as program_code',
                'subjects.id as subject_id',
                'subjects.code',
                'subjects.title',
                'grades.preliminary',
                'grades.midterm',
                'grades.final_term',
                'grades.final_grade',
                DB::raw("COALESCE(grades.remarks, 'In progress') as remarks"),
            );
    }

    private function authorizeStudentRead(User $student): void
    {
        abort_unless($student->role === 'student', Response::HTTP_NOT_FOUND);
        abort_unless(Auth::user()->role === 'registrar' || Auth::id() === $student->id, Response::HTTP_NOT_FOUND);
    }
}
