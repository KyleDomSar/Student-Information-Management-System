<?php

namespace Tests\\Feature;

use App\\Models\\AuditLog;
use App\\Models\\Enrollment;
use App\\Models\\Program;
use App\\Models\\Section;
use App\\Models\\Student;
use App\\Models\\StudentVisitorTimeout;
use App\\Models\\User;
use App\\Models\\YearLevel;
use Database\\Seeders\\PresentationDatabaseSeeder;
use Illuminate\\Foundation\\Testing\\RefreshDatabase;
use Illuminate\\Support\\Facades\\Hash;
use Tests\\TestCase;

class PresentationDatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_presentation_seeder_populates_all_current_sims_modules(): void
    {
        $this->seed(PresentationDatabaseSeeder::class);

        // User Management: 1 administrator, 10 staff, and 30 student accounts.
        $this->assertDatabaseCount('users', 41);
        $this->assertSame(1, User::where('role', User::ROLE_ADMIN)->count());
        $this->assertSame(10, User::where('role', User::ROLE_STAFF)->count());
        $this->assertSame(30, User::where('role', User::ROLE_STUDENT)->count());

        // Academic Structure: programs, year levels, and enough sections for pagination.
        $this->assertSame(10, Program::count());
        $this->assertSame(6, YearLevel::count());
        $this->assertSame(5, YearLevel::where('is_active', true)->count());
        $this->assertSame(1, YearLevel::where('is_active', false)->count());
        $this->assertSame(102, Section::count());

        // Student Records and Reports: enough data for multiple pages and status filters.
        $this->assertSame(30, Student::count());
        $this->assertSame(2, Student::where('status', 'inactive')->count());
        $this->assertSame(2, Student::where('status', 'graduated')->count());

        // Enrollment and Student Portal modules.
        $this->assertSame(86, Enrollment::count());
        $this->assertSame(33, StudentVisitorTimeout::count());
        $this->assertGreaterThan(150, AuditLog::count());

        $this->assertDatabaseHas('students', [
            'student_number' => '2026-CEIT-0001',
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('students', [
            'student_number' => '2026-CEIT-0022',
            'status' => 'inactive',
        ]);
        $this->assertDatabaseHas('students', [
            'student_number' => '2026-CEIT-0023',
            'status' => 'graduated',
        ]);
        $this->assertDatabaseHas('sections', [
            'code' => '3C',
            'is_active' => false,
        ]);
    }

    public function test_primary_accounts_have_distinct_credentials_and_can_sign_in_to_their_portals(): void
    {
        $this->seed(PresentationDatabaseSeeder::class);

        $admin = User::where('email', 'admin.presentation@sims.test')->firstOrFail();
        $staff = User::where('email', 'registrar.presentation@sims.test')->firstOrFail();
        $student = User::where('email', 'alyssa.santos.presentation@sims.test')->firstOrFail();

        $this->assertTrue(Hash::check('Admin@SIMS2026!', $admin->password));
        $this->assertTrue(Hash::check('Staff@SIMS2026!', $staff->password));
        $this->assertTrue(Hash::check('Student@SIMS2026!', $student->password));

        $this->post(route('admin.login.store'), [
            'email' => $admin->email,
            'password' => 'Admin@SIMS2026!',
        ])->assertRedirect(route('admin.dashboard'));

        $this->post(route('logout'));

        $this->post(route('admin.login.store'), [
            'email' => $staff->email,
            'password' => 'Staff@SIMS2026!',
        ])->assertRedirect(route('admin.dashboard'));

        $this->post(route('logout'));

        $this->post(route('student.login.store'), [
            'email' => $student->email,
            'password' => 'Student@SIMS2026!',
        ])->assertRedirect(route('student.dashboard'));
    }

    public function test_primary_student_profile_has_enrollment_and_visitor_time_out_history(): void
    {
        $this->seed(PresentationDatabaseSeeder::class);

        $user = User::where('email', 'alyssa.santos.presentation@sims.test')->firstOrFail();
        $student = Student::where('user_id', $user->id)->firstOrFail();

        $this->assertSame('2026-CEIT-0001', $student->student_number);
        $this->assertSame('BSIT', $student->program()->value('code'));
        $this->assertSame(3, $student->year_level);
        $this->assertSame('3A', $student->section()->value('code'));
        $this->assertSame(3, $student->enrollments()->count());
        $this->assertSame(3, $student->visitorTimeouts()->count());

        $this->actingAs($user)
            ->get(route('student.dashboard'))
            ->assertOk()
            ->assertSee('Alyssa Mae Santos')
            ->assertSee('2026-CEIT-0001')
            ->assertSee('Enrollment history')
            ->assertSee('Visitor time-out');
    }

    public function test_presentation_seeder_is_repeatable_without_duplicate_records(): void
    {
        $this->seed(PresentationDatabaseSeeder::class);
        $firstCounts = $this->recordCounts();

        $this->seed(PresentationDatabaseSeeder::class);

        $this->assertSame($firstCounts, $this->recordCounts());
    }

    private function recordCounts(): array
    {
        return [
            'users' => User::count(),
            'programs' => Program::count(),
            'year_levels' => YearLevel::count(),
            'sections' => Section::count(),
            'students' => Student::count(),
            'enrollments' => Enrollment::count(),
            'visitor_timeouts' => StudentVisitorTimeout::count(),
            'audit_logs' => AuditLog::count(),
        ];
    }
}
