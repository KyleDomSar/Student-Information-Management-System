<?php

namespace Tests\Feature;

use App\Models\Enrollment;
use App\Models\Program;
use App\Models\Student;
use App\Models\User;
use App\Models\YearLevel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentRecordManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (range(1, 6) as $number) {
            YearLevel::firstOrCreate(
                ['level_number' => $number],
                ['name' => $number.' Year', 'is_active' => true],
            );
        }
    }

    public function test_staff_can_view_student_directory(): void
    {
        $staff = User::factory()->create(['role' => User::ROLE_STAFF]);
        $this->actingAs($staff)->get(route('admin.students.index'))->assertOk();
    }

    public function test_unknown_legacy_role_cannot_manage_student_records(): void
    {
        $legacyUser = User::factory()->create(['role' => 'instructor']);
        $this->actingAs($legacyUser)->get(route('admin.students.index'))->assertForbidden();
    }

    public function test_student_cannot_manage_student_records(): void
    {
        $student = User::factory()->create(['role' => User::ROLE_STUDENT]);
        $this->actingAs($student)->get(route('admin.students.index'))->assertForbidden();
    }

    public function test_staff_can_register_a_student_account_and_profile(): void
    {
        $staff = User::factory()->create(['role' => User::ROLE_STAFF]);
        $program = Program::create([
            'code' => 'BSIT',
            'name' => 'Bachelor of Science in Information Technology',
            'is_active' => true,
        ]);

        $this->actingAs($staff)->post(route('admin.students.store'), [
            'name' => 'Sample Student',
            'email' => 'sample.student@example.test',
            'password' => 'A-Strong-Demo-Password-2026',
            'password_confirmation' => 'A-Strong-Demo-Password-2026',
            'student_number' => 'DEMO-2026-001',
            'program_id' => $program->id,
            'year_level' => 3,
            'status' => 'active',
            'account_active' => '0',
        ])->assertRedirect();

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

    public function test_staff_cannot_change_student_account_status_or_delete_the_record(): void
    {
        $staff = User::factory()->create(['role' => User::ROLE_STAFF]);
        $studentUser = User::factory()->create(['role' => User::ROLE_STUDENT]);
        $student = Student::create([
            'user_id' => $studentUser->id,
            'student_number' => 'DEMO-2026-002',
            'status' => 'active',
        ]);

        $this->actingAs($staff)->patch(route('admin.students.account-status', $student), ['is_active' => '0'])->assertForbidden();
        $this->actingAs($staff)->delete(route('admin.students.destroy', $student))->assertForbidden();

        $this->assertDatabaseHas('students', ['id' => $student->id, 'student_number' => 'DEMO-2026-002', 'status' => 'active']);
        $this->assertDatabaseHas('users', ['id' => $studentUser->id, 'is_active' => true]);
    }

    public function test_admin_can_deactivate_and_reactivate_a_student_portal_account(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $studentUser = User::factory()->create(['role' => User::ROLE_STUDENT]);
        $student = Student::create([
            'user_id' => $studentUser->id,
            'student_number' => 'DEMO-2026-003',
            'status' => 'active',
        ]);

        $this->actingAs($admin)->patch(route('admin.students.account-status', $student), ['is_active' => '0'])
            ->assertRedirect(route('admin.students.show', $student));
        $this->assertDatabaseHas('users', ['id' => $studentUser->id, 'is_active' => false]);

        $this->actingAs($admin)->patch(route('admin.students.account-status', $student), ['is_active' => '1'])
            ->assertRedirect(route('admin.students.show', $student));
        $this->assertDatabaseHas('users', ['id' => $studentUser->id, 'is_active' => true]);
    }

    public function test_admin_can_delete_a_student_record_without_enrollment_history(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $studentUser = User::factory()->create(['role' => User::ROLE_STUDENT]);
        $student = Student::create([
            'user_id' => $studentUser->id,
            'student_number' => 'DEMO-2026-004',
            'status' => 'active',
        ]);

        $this->actingAs($admin)->delete(route('admin.students.destroy', $student))
            ->assertRedirect(route('admin.students.index'));

        $this->assertDatabaseMissing('students', ['id' => $student->id]);
        $this->assertDatabaseMissing('users', ['id' => $studentUser->id]);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'action' => 'student.deleted',
            'target_type' => 'Student',
            'target_id' => $student->id,
        ]);
    }

    public function test_admin_cannot_delete_student_record_with_enrollment_history(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $studentUser = User::factory()->create(['role' => User::ROLE_STUDENT]);
        $student = Student::create([
            'user_id' => $studentUser->id,
            'student_number' => 'DEMO-2026-005',
            'status' => 'active',
        ]);
        Enrollment::create([
            'student_id' => $student->id,
            'academic_year' => '2026-2027',
            'semester' => 'First Semester',
        ]);

        $this->actingAs($admin)->delete(route('admin.students.destroy', $student))
            ->assertSessionHasErrors('student');

        $this->assertDatabaseHas('students', ['id' => $student->id]);
        $this->assertDatabaseHas('users', ['id' => $studentUser->id]);
    }

    public function test_staff_can_update_authorized_student_record_fields_but_cannot_manage_account_access_or_password(): void
    {
        $staff = User::factory()->create(['role' => User::ROLE_STAFF]);
        $studentUser = User::factory()->create([
            'role' => User::ROLE_STUDENT,
            'password' => 'Original-Student-Password-2026',
            'is_active' => true,
        ]);
        $student = Student::create([
            'user_id' => $studentUser->id,
            'student_number' => 'DEMO-2026-006',
            'status' => 'active',
        ]);

        $this->actingAs($staff)->put(route('admin.students.update', $student), [
            'name' => 'Updated by Registrar',
            'email' => 'updated.by.registrar@example.test',
            'student_number' => 'DEMO-2026-006-UPDATED',
            'status' => 'graduated',
            'account_active' => '0',
            'password' => 'Attempted-Unauthorized-Password-2026',
            'password_confirmation' => 'Attempted-Unauthorized-Password-2026',
        ])->assertRedirect(route('admin.students.show', $student));

        $this->assertDatabaseHas('users', [
            'id' => $studentUser->id,
            'name' => 'Updated by Registrar',
            'email' => 'updated.by.registrar@example.test',
            'is_active' => true,
        ]);
        $this->assertTrue(password_verify('Original-Student-Password-2026', $studentUser->fresh()->password));
        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'student_number' => 'DEMO-2026-006-UPDATED',
            'status' => 'graduated',
        ]);
    }

    public function test_student_cannot_open_or_update_another_student_record_through_admin_routes(): void
    {
        $studentUser = User::factory()->create(['role' => User::ROLE_STUDENT]);
        $otherStudentUser = User::factory()->create(['role' => User::ROLE_STUDENT]);
        $otherStudent = Student::create([
            'user_id' => $otherStudentUser->id,
            'student_number' => 'DEMO-2026-007',
            'status' => 'active',
        ]);

        $this->actingAs($studentUser)->get(route('admin.students.show', $otherStudent))->assertForbidden();
        $this->actingAs($studentUser)->put(route('admin.students.update', $otherStudent), [
            'name' => 'Unauthorized Change',
            'email' => 'unauthorized.change@example.test',
            'student_number' => 'UNAUTHORIZED-001',
            'status' => 'graduated',
        ])->assertForbidden();

        $this->assertDatabaseHas('users', [
            'id' => $otherStudentUser->id,
            'name' => $otherStudentUser->name,
            'email' => $otherStudentUser->email,
        ]);
        $this->assertDatabaseHas('students', [
            'id' => $otherStudent->id,
            'student_number' => 'DEMO-2026-007',
            'status' => 'active',
        ]);
    }
}
