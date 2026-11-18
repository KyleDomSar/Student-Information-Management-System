<?php

namespace Tests\Feature;

use App\Models\Program;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentRecordManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_view_student_directory(): void
    {
        $staff = User::factory()->create(['role' => User::ROLE_STAFF]);

        $this->actingAs($staff)
            ->get(route('admin.students.index'))
            ->assertOk();
    }

    public function test_instructor_cannot_manage_student_records(): void
    {
        $instructor = User::factory()->create(['role' => User::ROLE_INSTRUCTOR]);

        $this->actingAs($instructor)
            ->get(route('admin.students.index'))
            ->assertForbidden();
    }

    public function test_student_cannot_manage_student_records(): void
    {
        $student = User::factory()->create(['role' => User::ROLE_STUDENT]);

        $this->actingAs($student)
            ->get(route('admin.students.index'))
            ->assertForbidden();
    }

    public function test_staff_can_create_a_student_account_and_profile(): void
    {
        $staff = User::factory()->create(['role' => User::ROLE_STAFF]);
        $program = Program::create([
            'code' => 'BSIT',
            'name' => 'Bachelor of Science in Information Technology',
            'is_active' => true,
        ]);

        $this->actingAs($staff)
            ->post(route('admin.students.store'), [
                'name' => 'Sample Student',
                'email' => 'sample.student@example.test',
                'password' => 'A-Strong-Demo-Password-2026',
                'password_confirmation' => 'A-Strong-Demo-Password-2026',
                'student_number' => 'DEMO-2026-001',
                'program_id' => $program->id,
                'year_level' => 3,
                'status' => 'active',
                'account_active' => '1',
            ])
            ->assertRedirect();

        $user = User::where('email', 'sample.student@example.test')->first();

        $this->assertNotNull($user);
        $this->assertSame(User::ROLE_STUDENT, $user->role);
        $this->assertTrue($user->is_active);
        $this->assertTrue(password_verify('A-Strong-Demo-Password-2026', $user->password));

        $this->assertDatabaseHas('students', [
            'user_id' => $user->id,
            'student_number' => 'DEMO-2026-001',
            'program_id' => $program->id,
            'year_level' => 3,
        ]);
    }

    public function test_deactivating_a_student_preserves_the_profile(): void
    {
        $staff = User::factory()->create(['role' => User::ROLE_STAFF]);
        $studentUser = User::factory()->create(['role' => User::ROLE_STUDENT]);
        $student = Student::create([
            'user_id' => $studentUser->id,
            'student_number' => 'DEMO-2026-002',
            'status' => 'active',
        ]);

        $this->actingAs($staff)
            ->delete(route('admin.students.destroy', $student))
            ->assertRedirect(route('admin.students.index'));

        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'student_number' => 'DEMO-2026-002',
            'status' => 'inactive',
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $studentUser->id,
            'is_active' => false,
        ]);
    }
}
