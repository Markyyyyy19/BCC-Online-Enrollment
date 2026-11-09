<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    public function test_guest_is_sent_to_login_before_opening_student_workspace(): void
    {
        $this->get('/student/enrollments')
            ->assertRedirect('/login');
    }

    public function test_student_cannot_open_registrar_tools(): void
    {
        $student = new User([
            'id' => 1001,
            'role' => 'student',
            'is_active' => true,
        ]);

        $this->actingAs($student)
            ->get('/registrar/programs')
            ->assertRedirect('/student/enrollments');
    }

    public function test_registrar_cannot_open_student_workspace(): void
    {
        $registrar = new User([
            'id' => 1002,
            'role' => 'registrar',
            'is_active' => true,
        ]);

        $this->actingAs($registrar)
            ->get('/student/enrollments')
            ->assertRedirect('/dashboard');
    }
}
