<?php

namespace Tests\Feature;

use App\Models\Enrollment;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnrollmentManagementTest extends TestCase
{
    use RefreshDatabase;

    private function createStudent(string $number): Student
    {
        $user = User::factory()->create([
            'role' => User::ROLE_STUDENT,
            'is_active' => true,
        ]);

        return Student::create([
            'user_id' => $user->id,
            'student_number' => $number,
            'status' => 'active',
        ]);
    }

    public function test_staff_can_open_enrollment_management(): void
    {
        $staff = User::factory()->create(['role' => User::ROLE_STAFF]);

        $this->actingAs($staff)
            ->get(route('admin.enrollments.index'))
            ->assertOk();
    }

    public function test_student_cannot_access_enrollment_management(): void
    {
        $student = User::factory()->create(['role' => User::ROLE_STUDENT]);

        $this->actingAs($student)
            ->get(route('admin.enrollments.index'))
            ->assertForbidden();
    }

    public function test_staff_can_create_an_enrollment_record(): void
    {
        $staff = User::factory()->create(['role' => User::ROLE_STAFF]);
        $student = $this->createStudent('SIMS-ENR-001');

        $this->actingAs($staff)
            ->post(route('admin.enrollments.store'), [
                'student_id' => $student->id,
                'academic_year' => '2026-2027',
                'semester' => 'First Semester',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('enrollments', [
            'student_id' => $student->id,
            'academic_year' => '2026-2027',
            'semester' => 'First Semester',
            'created_by' => $staff->id,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $staff->id,
            'action' => 'enrollment.created',
        ]);
    }

    public function test_duplicate_student_term_is_rejected(): void
    {
        $staff = User::factory()->create(['role' => User::ROLE_STAFF]);
        $student = $this->createStudent('SIMS-ENR-002');

        Enrollment::create([
            'student_id' => $student->id,
            'academic_year' => '2026-2027',
            'semester' => 'First Semester',
            'created_by' => $staff->id,
        ]);

        $this->actingAs($staff)
            ->from(route('admin.enrollments.create'))
            ->post(route('admin.enrollments.store'), [
                'student_id' => $student->id,
                'academic_year' => '2026-2027',
                'semester' => 'First Semester',
            ])
            ->assertSessionHasErrors('student_id');

        $this->assertDatabaseCount('enrollments', 1);
    }

    public function test_student_can_view_own_enrollment_but_not_another_students_record(): void
    {
        $firstStudent = $this->createStudent('SIMS-ENR-003');
        $secondStudent = $this->createStudent('SIMS-ENR-004');

        $ownEnrollment = Enrollment::create([
            'student_id' => $firstStudent->id,
            'academic_year' => '2026-2027',
            'semester' => 'First Semester',
        ]);
        $otherEnrollment = Enrollment::create([
            'student_id' => $secondStudent->id,
            'academic_year' => '2026-2027',
            'semester' => 'First Semester',
        ]);

        $this->actingAs($firstStudent->user)
            ->get(route('student.enrollments.show', $ownEnrollment->id))
            ->assertOk();

        $this->actingAs($firstStudent->user)
            ->get(route('student.enrollments.show', $otherEnrollment->id))
            ->assertNotFound();
    }

    public function test_student_cannot_submit_or_modify_an_enrollment_record(): void
    {
        $student = $this->createStudent('SIMS-ENR-005');

        $this->actingAs($student->user)
            ->post(route('admin.enrollments.store'), [
                'student_id' => $student->id,
                'academic_year' => '2026-2027',
                'semester' => 'First Semester',
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('enrollments', 0);
    }
}
