<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Enrollment;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentVisitorTimeout;
use App\Models\User;
use Database\Seeders\PresentationDatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PresentationDatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_presentation_seeder_creates_exactly_one_account_for_each_role_and_sample_student_data(): void
    {
        $this->seed(PresentationDatabaseSeeder::class);

        $this->assertDatabaseCount('users', 3);
        $this->assertDatabaseHas('users', [
            'email' => 'admin.presentation@sims.test',
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);
        $this->assertDatabaseHas('users', [
            'email' => 'registrar.presentation@sims.test',
            'role' => User::ROLE_STAFF,
            'is_active' => true,
        ]);
        $this->assertDatabaseHas('users', [
            'email' => 'alyssa.santos.presentation@sims.test',
            'role' => User::ROLE_STUDENT,
            'is_active' => true,
        ]);

        $this->assertTrue(Hash::check(
            'Admin@SIMS2026!',
            User::where('email', 'admin.presentation@sims.test')->firstOrFail()->password,
        ));
        $this->assertTrue(Hash::check(
            'Staff@SIMS2026!',
            User::where('email', 'registrar.presentation@sims.test')->firstOrFail()->password,
        ));
        $studentUser = User::where('email', 'alyssa.santos.presentation@sims.test')->firstOrFail();
        $this->assertTrue(Hash::check('Student@SIMS2026!', $studentUser->password));

        $student = Student::where('user_id', $studentUser->id)->firstOrFail();
        $this->assertSame('2026-CEIT-IT-0001', $student->student_number);
        $this->assertSame('active', $student->status);
        $this->assertSame('BSIT', $student->program()->value('code'));
        $this->assertSame(3, $student->year_level);
        $this->assertSame('3A', $student->section()->value('code'));

        $this->assertSame(2, Enrollment::where('student_id', $student->id)->count());
        $this->assertSame(1, StudentVisitorTimeout::where('student_id', $student->id)->count());
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'student.created',
            'target_type' => 'Student',
            'target_id' => $student->id,
        ]);
    }

    public function test_presentation_seeder_can_be_run_again_without_adding_duplicate_accounts_or_records(): void
    {
        $this->seed(PresentationDatabaseSeeder::class);
        $firstCounts = $this->recordCounts();

        $this->seed(PresentationDatabaseSeeder::class);

        $this->assertSame($firstCounts, $this->recordCounts());
    }

    public function test_seeded_accounts_can_sign_in_to_their_respective_portals(): void
    {
        $this->seed(PresentationDatabaseSeeder::class);

        $this->post(route('admin.login.store'), [
            'email' => 'admin.presentation@sims.test',
            'password' => 'Admin@SIMS2026!',
        ])->assertRedirect(route('admin.dashboard'));

        $this->post(route('logout'));

        $this->post(route('admin.login.store'), [
            'email' => 'registrar.presentation@sims.test',
            'password' => 'Staff@SIMS2026!',
        ])->assertRedirect(route('admin.dashboard'));

        $this->post(route('logout'));

        $this->post(route('student.login.store'), [
            'email' => 'alyssa.santos.presentation@sims.test',
            'password' => 'Student@SIMS2026!',
        ])->assertRedirect(route('student.dashboard'));
    }

    private function recordCounts(): array
    {
        return [
            'users' => User::count(),
            'students' => Student::count(),
            'sections' => Section::count(),
            'enrollments' => Enrollment::count(),
            'visitor_timeouts' => StudentVisitorTimeout::count(),
            'audit_logs' => AuditLog::count(),
        ];
    }
}
