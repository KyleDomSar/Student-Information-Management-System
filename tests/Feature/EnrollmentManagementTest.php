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
            ->assertOk()
            ->assertSee('SIMS-ENR-003')
            ->assertSee('2026-2027')
            ->assertSee('First Semester');

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

    public function test_admin_can_create_an_enrollment_record(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $student = $this->createStudent('SIMS-ENR-ADMIN-001');

        $this->actingAs($admin)
            ->post(route('admin.enrollments.store'), [
                'student_id' => $student->id,
                'academic_year' => '2026-2027',
                'semester' => 'Second Semester',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('enrollments', [
            'student_id' => $student->id,
            'academic_year' => '2026-2027',
            'semester' => 'Second Semester',
            'created_by' => $admin->id,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'action' => 'enrollment.created',
        ]);

        $enrollment = Enrollment::where('student_id', $student->id)->firstOrFail();

        $this->actingAs($admin)
            ->get(route('admin.enrollments.show', $enrollment))
            ->assertOk()
            ->assertSee('SIMS-ENR-ADMIN-001')
            ->assertSee('2026-2027')
            ->assertSee('Second Semester');
    }

    public function test_inactive_students_and_inactive_portal_accounts_cannot_be_enrolled(): void
    {
        $staff = User::factory()->create(['role' => User::ROLE_STAFF]);

        $inactiveStudent = $this->createStudent('SIMS-ENR-INACTIVE-001');
        $inactiveStudent->update(['status' => 'inactive']);

        $inactiveAccountStudent = $this->createStudent('SIMS-ENR-INACTIVE-002');
        $inactiveAccountStudent->user->update(['is_active' => false]);

        $this->actingAs($staff)
            ->get(route('admin.enrollments.create'))
            ->assertOk()
            ->assertDontSee('SIMS-ENR-INACTIVE-001')
            ->assertDontSee('SIMS-ENR-INACTIVE-002');

        $this->actingAs($staff)
            ->from(route('admin.enrollments.create'))
            ->post(route('admin.enrollments.store'), [
                'student_id' => $inactiveStudent->id,
                'academic_year' => '2026-2027',
                'semester' => 'First Semester',
            ])
            ->assertSessionHasErrors('student_id');

        $this->actingAs($staff)
            ->from(route('admin.enrollments.create'))
            ->post(route('admin.enrollments.store'), [
                'student_id' => $inactiveAccountStudent->id,
                'academic_year' => '2026-2027',
                'semester' => 'First Semester',
            ])
            ->assertSessionHasErrors('student_id');

        $this->assertDatabaseCount('enrollments', 0);
    }

    public function test_academic_year_must_be_a_valid_consecutive_year_range(): void
    {
        $staff = User::factory()->create(['role' => User::ROLE_STAFF]);
        $student = $this->createStudent('SIMS-ENR-YEAR-001');

        foreach (['2026', '2026-2026', '2027-2026', '2026-2028', 'abcd-efgh', ['2026-2027']] as $academicYear) {
            $this->actingAs($staff)
                ->from(route('admin.enrollments.create'))
                ->post(route('admin.enrollments.store'), [
                    'student_id' => $student->id,
                    'academic_year' => $academicYear,
                    'semester' => 'First Semester',
                ])
                ->assertSessionHasErrors('academic_year');
        }

        $this->assertDatabaseCount('enrollments', 0);
    }

    public function test_only_supported_semester_labels_are_accepted(): void
    {
        $staff = User::factory()->create(['role' => User::ROLE_STAFF]);
        $student = $this->createStudent('SIMS-ENR-SEMESTER-001');

        $this->actingAs($staff)
            ->from(route('admin.enrollments.create'))
            ->post(route('admin.enrollments.store'), [
                'student_id' => $student->id,
                'academic_year' => '2026-2027',
                'semester' => 'Summer',
            ])
            ->assertSessionHasErrors('semester');

        $this->assertDatabaseCount('enrollments', 0);
    }

    public function test_same_student_can_have_different_semesters_and_academic_years(): void
    {
        $staff = User::factory()->create(['role' => User::ROLE_STAFF]);
        $student = $this->createStudent('SIMS-ENR-MULTI-001');

        foreach ([
            ['2026-2027', 'First Semester'],
            ['2026-2027', 'Second Semester'],
            ['2026-2027', 'Midyear'],
            ['2027-2028', 'First Semester'],
        ] as [$academicYear, $semester]) {
            $this->actingAs($staff)
                ->post(route('admin.enrollments.store'), [
                    'student_id' => $student->id,
                    'academic_year' => $academicYear,
                    'semester' => $semester,
                ])
                ->assertRedirect();
        }

        $this->assertDatabaseCount('enrollments', 4);
    }

    public function test_enrollment_search_matches_student_number_name_academic_year_and_semester(): void
    {
        $staff = User::factory()->create(['role' => User::ROLE_STAFF]);
        $student = $this->createStudent('SIMS-ENR-SEARCH-001');
        $student->user->update(['name' => 'Enrollment Search Target']);

        Enrollment::create([
            'student_id' => $student->id,
            'academic_year' => '2026-2027',
            'semester' => 'Midyear',
            'created_by' => $staff->id,
        ]);

        foreach (['SIMS-ENR-SEARCH-001', 'Enrollment Search Target', '2026-2027', 'Midyear'] as $term) {
            $this->actingAs($staff)
                ->get(route('admin.enrollments.index', ['search' => $term]))
                ->assertOk()
                ->assertSee('Enrollment Search Target')
                ->assertSee('SIMS-ENR-SEARCH-001');
        }
    }

    public function test_student_enrollment_list_contains_only_the_logged_in_students_records(): void
    {
        $ownStudent = $this->createStudent('SIMS-ENR-OWN-001');
        $otherStudent = $this->createStudent('SIMS-ENR-OTHER-001');

        Enrollment::create([
            'student_id' => $ownStudent->id,
            'academic_year' => '2026-2027',
            'semester' => 'First Semester',
        ]);

        Enrollment::create([
            'student_id' => $otherStudent->id,
            'academic_year' => '2028-2029',
            'semester' => 'Second Semester',
        ]);

        $this->actingAs($ownStudent->user)
            ->get(route('student.enrollments.index'))
            ->assertOk()
            ->assertSee('2026-2027')
            ->assertSee('First Semester')
            ->assertDontSee('2028-2029')
            ->assertDontSee('Second Semester');
    }

    public function test_student_without_a_student_profile_cannot_view_enrollment_history(): void
    {
        $studentUser = User::factory()->create(['role' => User::ROLE_STUDENT]);

        $this->actingAs($studentUser)
            ->get(route('student.enrollments.index'))
            ->assertNotFound();
    }
}
