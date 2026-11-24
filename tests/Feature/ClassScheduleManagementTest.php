<?php

namespace Tests\Feature;

use App\Models\ClassSchedule;
use App\Models\Enrollment;
use App\Models\EnrollmentSubject;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClassScheduleManagementTest extends TestCase
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

    public function test_staff_can_open_class_schedule_management(): void
    {
        $staff = User::factory()->create(['role' => User::ROLE_STAFF]);

        $this->actingAs($staff)
            ->get(route('admin.schedules.index'))
            ->assertOk();
    }

    public function test_student_cannot_access_admin_schedule_management(): void
    {
        $student = User::factory()->create(['role' => User::ROLE_STUDENT]);

        $this->actingAs($student)
            ->get(route('admin.schedules.index'))
            ->assertForbidden();
    }

    public function test_staff_can_create_schedule_for_approved_enrollment_subject(): void
    {
        $staff = User::factory()->create(['role' => User::ROLE_STAFF]);
        $student = $this->createStudent('SIMS-SCHED-001');
        $item = $this->createEnrollmentSubject($student, 'IT201');

        $this->actingAs($staff)
            ->post(route('admin.schedules.store'), [
                'enrollment_subject_id' => $item->id,
                'day_of_week' => 'Monday',
                'start_time' => '08:00',
                'end_time' => '10:00',
                'room' => 'Laboratory 1',
            ])
            ->assertRedirect(route('admin.schedules.index'));

        $schedule = ClassSchedule::where('enrollment_subject_id', $item->id)->first();

        $this->assertNotNull($schedule);
        $this->assertSame('Monday', $schedule->day_of_week);
        $this->assertSame('08:00', substr($schedule->start_time, 0, 5));
        $this->assertSame('10:00', substr($schedule->end_time, 0, 5));
        $this->assertSame('Laboratory 1', $schedule->room);
    }

    public function test_schedule_cannot_be_created_for_pending_enrollment(): void
    {
        $staff = User::factory()->create(['role' => User::ROLE_STAFF]);
        $student = $this->createStudent('SIMS-SCHED-002');
        $item = $this->createEnrollmentSubject($student, 'IT202', 'pending');

        $this->actingAs($staff)
            ->from(route('admin.schedules.create'))
            ->post(route('admin.schedules.store'), [
                'enrollment_subject_id' => $item->id,
                'day_of_week' => 'Tuesday',
                'start_time' => '09:00',
                'end_time' => '11:00',
                'room' => 'Room 202',
            ])
            ->assertSessionHasErrors('enrollment_subject_id');

        $this->assertDatabaseCount('class_schedules', 0);
    }

    public function test_end_time_must_be_after_start_time(): void
    {
        $staff = User::factory()->create(['role' => User::ROLE_STAFF]);
        $student = $this->createStudent('SIMS-SCHED-003');
        $item = $this->createEnrollmentSubject($student, 'IT203');

        $this->actingAs($staff)
            ->from(route('admin.schedules.create'))
            ->post(route('admin.schedules.store'), [
                'enrollment_subject_id' => $item->id,
                'day_of_week' => 'Wednesday',
                'start_time' => '10:00',
                'end_time' => '09:00',
                'room' => 'Room 203',
            ])
            ->assertSessionHasErrors('end_time');

        $this->assertDatabaseCount('class_schedules', 0);
    }

    public function test_student_sees_only_own_approved_class_schedules(): void
    {
        $firstStudent = $this->createStudent('SIMS-SCHED-004');
        $secondStudent = $this->createStudent('SIMS-SCHED-005');

        $ownItem = $this->createEnrollmentSubject($firstStudent, 'IT204');
        $otherItem = $this->createEnrollmentSubject($secondStudent, 'IT205');
        $pendingItem = $this->createEnrollmentSubject($firstStudent, 'IT206', 'pending', 'Second Semester');

        ClassSchedule::create([
            'enrollment_subject_id' => $ownItem->id,
            'day_of_week' => 'Thursday',
            'start_time' => '08:00',
            'end_time' => '10:00',
            'room' => 'Room 204',
        ]);

        ClassSchedule::create([
            'enrollment_subject_id' => $otherItem->id,
            'day_of_week' => 'Friday',
            'start_time' => '08:00',
            'end_time' => '10:00',
            'room' => 'Room 205',
        ]);

        ClassSchedule::create([
            'enrollment_subject_id' => $pendingItem->id,
            'day_of_week' => 'Saturday',
            'start_time' => '08:00',
            'end_time' => '10:00',
            'room' => 'Room 206',
        ]);

        $this->actingAs($firstStudent->user)
            ->get(route('student.schedules.index'))
            ->assertOk()
            ->assertSee('IT204')
            ->assertDontSee('IT205')
            ->assertDontSee('IT206');
    }
}
