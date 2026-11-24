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

        $this->actingAs($staff)
            ->get(route('admin.students.index'))
            ->assertOk();
    }

    public function test_unknown_legacy_role_cannot_manage_student_records(): void
    {
        $legacyUser = User::factory()->create(['role' => 'instructor']);

        $this->actingAs($legacyUser)
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

    public function test_staff_can_register_a_student_account_and_profile(): void
    {
        $staff = User::factory()->create(['role' => User::ROLE_STAFF]);
        $program = Program::create([
            'code' => 'BSIT',
            'name' => 'Bachelor of Science in Information Technology',
            'is_active' => true,
        ]);
        $program->yearLevels()->attach(3);

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
                // A crafted value from Staff must be ignored. Newly registered accounts start active.
                'account_active' => '0',
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

    public function test_staff_cannot_change_student_account_status_or_delete_the_record(): void
    {
        $staff = User::factory()->create(['role' => User::ROLE_STAFF]);
        $studentUser = User::factory()->create(['role' => User::ROLE_STUDENT]);
        $student = Student::create([
            'user_id' => $studentUser->id,
            'student_number' => 'DEMO-2026-002',
            'status' => 'active',
        ]);

        $this->actingAs($staff)
            ->patch(route('admin.students.account-status', $student), [
                'is_active' => '0',
            ])
            ->assertForbidden();

        $this->actingAs($staff)
            ->delete(route('admin.students.destroy', $student))
            ->assertForbidden();

        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'student_number' => 'DEMO-2026-002',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $studentUser->id,
            'is_active' => true,
        ]);
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

        $this->actingAs($admin)
            ->patch(route('admin.students.account-status', $student), ['is_active' => '0'])
            ->assertRedirect(route('admin.students.show', $student));

        $this->assertDatabaseHas('users', ['id' => $studentUser->id, 'is_active' => false]);
        $this->assertDatabaseHas('students', ['id' => $student->id, 'status' => 'active']);

        $this->actingAs($admin)
            ->patch(route('admin.students.account-status', $student), ['is_active' => '1'])
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

        $this->actingAs($admin)
            ->delete(route('admin.students.destroy', $student))
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
            'status' => 'enrolled',
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.students.destroy', $student))
            ->assertSessionHasErrors('student');

        $this->assertDatabaseHas('students', ['id' => $student->id]);
        $this->assertDatabaseHas('users', ['id' => $studentUser->id]);
    }
}
