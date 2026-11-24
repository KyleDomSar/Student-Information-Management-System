<?php

namespace Tests\Feature;

use App\Models\Enrollment;
use App\Models\Program;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnrollmentManagementTest extends TestCase
{
    use RefreshDatabase;

    private function createStudent(string $number, ?Program $program = null): Student
    {
        $user = User::factory()->create([
            'role' => User::ROLE_STUDENT,
            'is_active' => true,
        ]);

        return Student::create([
            'user_id' => $user->id,
            'student_number' => $number,
            'program_id' => $program?->id,
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
        $studentUser = User::factory()->create(['role' => User::ROLE_STUDENT]);

        $this->actingAs($studentUser)
            ->get(route('admin.enrollments.index'))
            ->assertForbidden();
    }

    public function test_staff_can_create_pending_enrollment_with_subjects(): void
    {
        $staff = User::factory()->create(['role' => User::ROLE_STAFF]);
        $program = Program::create([
            'code' => 'BSIT',
            'name' => 'Bachelor of Science in Information Technology',
            'is_active' => true,
        ]);
        $student = $this->createStudent('SIMS-ENR-001', $program);
        $subject = Subject::create([
            'program_id' => $program->id,
            'code' => 'IT101',
            'title' => 'Introduction to Computing',
            'units' => 3,
            'is_active' => true,
        ]);

        $this->actingAs($staff)
            ->post(route('admin.enrollments.store'), [
                'student_id' => $student->id,
                'academic_year' => '2026-2027',
                'semester' => 'First',
                'subjects' => [$subject->id],
            ])
            ->assertRedirect();

        $enrollment = Enrollment::with('enrollmentSubjects')->first();

        $this->assertNotNull($enrollment);
        $this->assertSame('pending', $enrollment->status);
        $this->assertSame($student->id, $enrollment->student_id);
        $this->assertCount(1, $enrollment->enrollmentSubjects);
        $this->assertSame($staff->id, $enrollment->created_by);
    }

    public function test_duplicate_student_term_is_rejected(): void
    {
        $staff = User::factory()->create(['role' => User::ROLE_STAFF]);
        $student = $this->createStudent('SIMS-ENR-002');
        $subject = Subject::create([
            'code' => 'GEN101',
            'title' => 'General Education',
            'units' => 3,
            'is_active' => true,
        ]);

        Enrollment::create([
            'student_id' => $student->id,
            'academic_year' => '2026-2027',
            'semester' => 'First',
            'status' => 'pending',
            'created_by' => $staff->id,
        ]);

        $this->actingAs($staff)
            ->from(route('admin.enrollments.create'))
            ->post(route('admin.enrollments.store'), [
                'student_id' => $student->id,
                'academic_year' => '2026-2027',
                'semester' => 'First',
                'subjects' => [$subject->id],
            ])
            ->assertSessionHasErrors('student_id');

        $this->assertDatabaseCount('enrollments', 1);
    }

    public function test_subject_from_another_program_is_rejected(): void
    {
        $staff = User::factory()->create(['role' => User::ROLE_STAFF]);
        $studentProgram = Program::create([
            'code' => 'BSIT',
            'name' => 'Information Technology',
            'is_active' => true,
        ]);
        $otherProgram = Program::create([
            'code' => 'BSCS',
            'name' => 'Computer Science',
            'is_active' => true,
        ]);
        $student = $this->createStudent('SIMS-ENR-003', $studentProgram);
        $subject = Subject::create([
            'program_id' => $otherProgram->id,
            'code' => 'CS101',
            'title' => 'Computer Science Foundations',
            'units' => 3,
            'is_active' => true,
        ]);

        $this->actingAs($staff)
            ->from(route('admin.enrollments.create'))
            ->post(route('admin.enrollments.store'), [
                'student_id' => $student->id,
                'academic_year' => '2026-2027',
                'semester' => 'First',
                'subjects' => [$subject->id],
            ])
            ->assertSessionHasErrors('subjects');

        $this->assertDatabaseCount('enrollments', 0);
    }

    public function test_staff_can_approve_a_pending_enrollment(): void
    {
        $staff = User::factory()->create(['role' => User::ROLE_STAFF]);
        $student = $this->createStudent('SIMS-ENR-004');
        $enrollment = Enrollment::create([
            'student_id' => $student->id,
            'academic_year' => '2026-2027',
            'semester' => 'Second',
            'status' => 'pending',
            'created_by' => $staff->id,
        ]);

        $this->actingAs($staff)
            ->patch(route('admin.enrollments.update-status', $enrollment), [
                'status' => 'enrolled',
            ])
            ->assertRedirect(route('admin.enrollments.show', $enrollment));

        $this->assertDatabaseHas('enrollments', [
            'id' => $enrollment->id,
            'status' => 'enrolled',
        ]);
    }

    public function test_student_can_view_own_enrollment_but_not_another_students_enrollment(): void
    {
        $firstStudent = $this->createStudent('SIMS-ENR-005');
        $secondStudent = $this->createStudent('SIMS-ENR-006');

        $ownEnrollment = Enrollment::create([
            'student_id' => $firstStudent->id,
            'academic_year' => '2026-2027',
            'semester' => 'First',
            'status' => 'enrolled',
        ]);

        $otherEnrollment = Enrollment::create([
            'student_id' => $secondStudent->id,
            'academic_year' => '2026-2027',
            'semester' => 'First',
            'status' => 'enrolled',
        ]);

        $this->actingAs($firstStudent->user)
            ->get(route('student.enrollments.show', $ownEnrollment->id))
            ->assertOk();

        $this->actingAs($firstStudent->user)
            ->get(route('student.enrollments.show', $otherEnrollment->id))
            ->assertNotFound();
    }

    public function test_student_cannot_submit_enrollment_status_changes(): void
    {
        $student = $this->createStudent('SIMS-ENR-007');
        $enrollment = Enrollment::create([
            'student_id' => $student->id,
            'academic_year' => '2026-2027',
            'semester' => 'Summer',
            'status' => 'pending',
        ]);

        $this->actingAs($student->user)
            ->patch(route('admin.enrollments.update-status', $enrollment), [
                'status' => 'enrolled',
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('enrollments', [
            'id' => $enrollment->id,
            'status' => 'pending',
        ]);
    }
}
