<?php

namespace Tests\Feature;

use App\Models\Enrollment;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentDashboardTest extends TestCase
{
    use RefreshDatabase;

    private function createStudent(int $enrollmentCount = 0): Student
    {
        $user = User::factory()->create(['role' => User::ROLE_STUDENT]);

        $student = Student::create([
            'user_id' => $user->id,
            'student_number' => 'SIMS-DASH-001',
            'status' => 'active',
        ]);

        for ($index = 0; $index < $enrollmentCount; $index++) {
            Enrollment::create([
                'student_id' => $student->id,
                'academic_year' => (2020 + $index).'-'.(2021 + $index),
                'semester' => 'First Semester',
            ]);
        }

        return $student;
    }

    public function test_student_navigation_includes_enrollment_history_without_admin_tools(): void
    {
        $student = $this->createStudent();

        $this->actingAs($student->user)
            ->get(route('student.dashboard'))
            ->assertOk()
            ->assertSee('href="/student/enrollments"', false)
            ->assertSee('Enrollment History')
            ->assertDontSee('User Management');
    }

    public function test_student_dashboard_shows_the_full_enrollment_count_not_only_the_latest_five(): void
    {
        $student = $this->createStudent(6);

        $this->actingAs($student->user)
            ->get(route('student.dashboard'))
            ->assertOk()
            ->assertSee('<div class="stat-value">6</div>', false);
    }

    public function test_staff_navigation_includes_enrollment_records_but_hides_admin_only_tools(): void
    {
        $staff = User::factory()->create(['role' => User::ROLE_STAFF]);

        $this->actingAs($staff)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('href="/admin/enrollments"', false)
            ->assertDontSee('User Management')
            ->assertDontSee('Audit Logs');
    }

    public function test_administrator_navigation_includes_user_management_and_audit_logs(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('href="/admin/users"', false)
            ->assertSee('href="/admin/audit-logs"', false);
    }
}
