<?php

namespace Tests\Feature;

use App\Models\Enrollment;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class GradeCalculationTest extends TestCase
{
    public function test_grade_averaging_midterm_and_final(): void
    {
        $registrar = User::where('role', 'registrar')->first();
        $student = User::where('role', 'student')->first();

        if (! $registrar || ! $student) {
            $this->markTestSkipped('Registrar or student not found in test database.');
        }

        $enrollment = Enrollment::where('student_id', $student->id)->where('status', 'approved')->first();
        if (! $enrollment) {
            $this->markTestSkipped('No approved enrollment found.');
        }

        $subject = $enrollment->subjects()->first();
        if (! $subject) {
            $this->markTestSkipped('No enrolled subject found.');
        }

        // Test passing grade: Midterm 1.75, Final 1.25 -> Average 1.50 (Passed)
        $response = $this->actingAs($registrar)
            ->patch("/registrar/grades/{$enrollment->id}/{$subject->id}", [
                'midterm' => '1.75',
                'final_term' => '1.25',
                'remarks' => 'In progress',
            ]);

        $response->assertRedirect();

        $grade = DB::table('grades')
            ->where('enrollment_id', $enrollment->id)
            ->where('subject_id', $subject->id)
            ->first();

        $this->assertNotNull($grade);
        $this->assertEquals(1.50, (float) $grade->final_grade);
        $this->assertEquals('Passed', $grade->remarks);
        $this->assertNull($grade->preliminary);
    }

    public function test_failing_grade_sets_remark_to_failed(): void
    {
        $registrar = User::where('role', 'registrar')->first();
        $student = User::where('role', 'student')->first();

        if (! $registrar || ! $student) {
            $this->markTestSkipped('Registrar or student not found in test database.');
        }

        $enrollment = Enrollment::where('student_id', $student->id)->where('status', 'approved')->first();
        if (! $enrollment) {
            $this->markTestSkipped('No approved enrollment found.');
        }

        $subject = $enrollment->subjects()->first();
        if (! $subject) {
            $this->markTestSkipped('No enrolled subject found.');
        }

        // Test failing grade: Midterm 3.50, Final 3.25 -> Average 3.38 (Failed)
        $response = $this->actingAs($registrar)
            ->patch("/registrar/grades/{$enrollment->id}/{$subject->id}", [
                'midterm' => '3.50',
                'final_term' => '3.25',
                'remarks' => 'In progress',
            ]);

        $response->assertRedirect();

        $grade = DB::table('grades')
            ->where('enrollment_id', $enrollment->id)
            ->where('subject_id', $subject->id)
            ->first();

        $this->assertNotNull($grade);
        $this->assertEquals(3.38, (float) $grade->final_grade);
        $this->assertEquals('Failed', $grade->remarks);
    }
}
