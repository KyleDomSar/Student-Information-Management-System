<?php

namespace Tests\Feature;

use App\Models\Enrollment;
use App\Models\EnrollmentSubject;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GradeManagementTest extends TestCase
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

    private function createEnrollmentSubject(Student $student, string $code, string $status = 'enrolled', string $semester = 'First Semester'): EnrollmentSubject
    {
        $subject = Subject::create([
            'code' => $code,
            'title' => 'Subject '.$code,
            'units' => 3,
            'is_active' => true,
        ]);

        $enrollment = Enrollment::create([
            'student_id' => $student->id,
            'academic_year' => '2026-2027',
            'semester' => $semester,
            'status' => $status,
        ]);

        return EnrollmentSubject::create([
            'enrollment_id' => $enrollment->id,
            'subject_id' => $subject->id,
        ]);
    }

    public function test_staff_can_open_gradebook(): void
    {
        $staff = User::factory()->create(['role' => User::ROLE_STAFF]);

        $this->actingAs($staff)
            ->get(route('admin.grades.index'))
            ->assertOk();
    }

    public function test_student_cannot_open_admin_gradebook(): void
    {
        $student = User::factory()->create(['role' => User::ROLE_STUDENT]);

        $this->actingAs($student)
            ->get(route('admin.grades.index'))
            ->assertForbidden();
    }

    public function test_staff_can_encode_grade_for_approved_enrollment(): void
    {
        $staff = User::factory()->create(['role' => User::ROLE_STAFF]);
        $student = $this->createStudent('SIMS-GRADE-001');
        $item = $this->createEnrollmentSubject($student, 'IT101');

        $this->actingAs($staff)
            ->put(route('admin.grades.update', $item), [
                'final_grade' => '89.50',
                'remarks' => 'Completed',
            ])
            ->assertRedirect(route('admin.grades.index'));

        $this->assertDatabaseHas('enrollment_subjects', [
            'id' => $item->id,
            'final_grade' => '89.50',
            'remarks' => 'Completed',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $staff->id,
            'action' => 'grade.updated',
            'target_type' => 'EnrollmentSubject',
            'target_id' => $item->id,
        ]);
    }

    public function test_pending_enrollment_grade_cannot_be_edited(): void
    {
        $staff = User::factory()->create(['role' => User::ROLE_STAFF]);
        $student = $this->createStudent('SIMS-GRADE-002');
        $item = $this->createEnrollmentSubject($student, 'IT102', 'pending');

        $this->actingAs($staff)
            ->get(route('admin.grades.edit', $item))
            ->assertNotFound();

        $this->actingAs($staff)
            ->put(route('admin.grades.update', $item), [
                'final_grade' => '95',
                'remarks' => 'Should not save',
            ])
            ->assertNotFound();

        $this->assertDatabaseHas('enrollment_subjects', [
            'id' => $item->id,
            'final_grade' => null,
        ]);
    }

    public function test_student_sees_only_their_own_approved_enrollment_grades(): void
    {
        $firstStudent = $this->createStudent('SIMS-GRADE-003');
        $secondStudent = $this->createStudent('SIMS-GRADE-004');

        $ownItem = $this->createEnrollmentSubject($firstStudent, 'IT103');
        $ownItem->update(['final_grade' => 91.25]);

        $otherItem = $this->createEnrollmentSubject($secondStudent, 'ACCT999');
        $otherItem->update(['final_grade' => 88.00]);

        $pendingItem = $this->createEnrollmentSubject($firstStudent, 'IT104', 'pending', 'Second Semester');
        $pendingItem->update(['final_grade' => 90.00]);

        $this->actingAs($firstStudent->user)
            ->get(route('student.grades.index'))
            ->assertOk()
            ->assertSee('IT103')
            ->assertDontSee('ACCT999')
            ->assertDontSee('IT104');
    }

    public function test_student_cannot_update_a_grade(): void
    {
        $student = $this->createStudent('SIMS-GRADE-005');
        $item = $this->createEnrollmentSubject($student, 'IT105');

        $this->actingAs($student->user)
            ->put(route('admin.grades.update', $item), [
                'final_grade' => '100',
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('enrollment_subjects', [
            'id' => $item->id,
            'final_grade' => null,
        ]);
    }
}
