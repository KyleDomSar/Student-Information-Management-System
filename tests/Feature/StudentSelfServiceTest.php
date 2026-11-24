<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StudentSelfServiceTest extends TestCase
{
    use RefreshDatabase;

    private function createStudent(): Student
    {
        $user = User::factory()->create([
            'role' => User::ROLE_STUDENT,
            'password' => 'Current-Student-Password-2026',
        ]);

        return Student::create([
            'user_id' => $user->id,
            'student_number' => 'SIMS-SELF-001',
            'year_level' => 2,
            'status' => 'active',
        ]);
    }

    public function test_student_can_update_only_their_permitted_personal_details(): void
    {
        $student = $this->createStudent();

        $this->actingAs($student->user)
            ->patch(route('student.profile.update'), [
                'name' => 'Updated Student Name',
                'email' => 'updated.student@example.test',
                // Academic fields sent by a crafted request are not accepted or changed.
                'student_number' => 'CHANGED-001',
                'year_level' => 6,
                'status' => 'graduated',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $student->user_id,
            'name' => 'Updated Student Name',
            'email' => 'updated.student@example.test',
        ]);

        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'student_number' => 'SIMS-SELF-001',
            'year_level' => 2,
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $student->user_id,
            'action' => 'student.profile_updated',
        ]);
    }

    public function test_student_can_change_password_with_current_password(): void
    {
        $student = $this->createStudent();
        $newPassword = 'New-Student-Password-2026';

        $this->actingAs($student->user)
            ->put(route('student.password.update'), [
                'current_password' => 'Current-Student-Password-2026',
                'password' => $newPassword,
                'password_confirmation' => $newPassword,
            ])
            ->assertRedirect();

        $this->assertTrue(Hash::check($newPassword, $student->user->fresh()->password));
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $student->user_id,
            'action' => 'student.password_changed',
        ]);
    }

    public function test_student_cannot_change_password_with_an_incorrect_current_password(): void
    {
        $student = $this->createStudent();

        $this->actingAs($student->user)
            ->from(route('student.profile'))
            ->put(route('student.password.update'), [
                'current_password' => 'incorrect-current-password',
                'password' => 'Another-Student-Password-2026',
                'password_confirmation' => 'Another-Student-Password-2026',
            ])
            ->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check(
            'Current-Student-Password-2026',
            $student->user->fresh()->password,
        ));
    }
}
