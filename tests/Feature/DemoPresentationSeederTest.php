<?php

namespace Tests\Feature;

use App\Models\Enrollment;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentVisitorTimeout;
use App\Models\User;
use Database\Seeders\CeitProgramSeeder;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoPresentationSeeder;
use Database\Seeders\YearLevelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DemoPresentationSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_presentation_seeder_creates_fictional_presentation_records(): void
    {
        $this->seed([
            CeitProgramSeeder::class,
            YearLevelSeeder::class,
        ]);

        $this->seed(DemoPresentationSeeder::class);

        $this->assertDatabaseHas('users', [
            'email' => 'registrar.demo@sims.test',
            'role' => User::ROLE_STAFF,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('students', [
            'student_number' => 'SIMS-DEMO-2026-001',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('students', [
            'student_number' => 'SIMS-DEMO-2026-011',
            'status' => 'inactive',
        ]);

        $this->assertDatabaseHas('enrollments', [
            'academic_year' => '2026-2027',
            'semester' => 'First Semester',
        ]);

        $this->assertGreaterThanOrEqual(1, Section::where('code', 'DEMO-1A')->count());
        $this->assertSame(11, Student::where('student_number', 'like', 'SIMS-DEMO-2026-%')->count());
        $this->assertSame(5, StudentVisitorTimeout::whereIn(
            'student_id',
            Student::where('student_number', 'like', 'SIMS-DEMO-2026-%')->select('id')
        )->count());

        $demoRegistrar = User::where('email', 'registrar.demo@sims.test')->firstOrFail();
        $this->assertTrue(Hash::check('Demo@2026!', $demoRegistrar->password));

        $demoStudent = User::where('email', 'alyssa.santos.demo@sims.test')->firstOrFail();
        $this->assertTrue(Hash::check('Demo@2026!', $demoStudent->password));
    }

    public function test_demo_presentation_seeder_is_repeatable_without_duplicate_records(): void
    {
        $this->seed([
            CeitProgramSeeder::class,
            YearLevelSeeder::class,
        ]);

        $this->seed(DemoPresentationSeeder::class);
        $firstCounts = $this->demoRecordCounts();

        $this->seed(DemoPresentationSeeder::class);
        $secondCounts = $this->demoRecordCounts();

        $this->assertSame($firstCounts, $secondCounts);
    }

    public function test_default_database_seeder_does_not_create_presentation_demo_records(): void
    {
        config([
            'sims.seed_admin.email' => null,
            'sims.seed_admin.password' => null,
            'sims.seed_student.email' => null,
            'sims.seed_student.password' => null,
            'sims.seed_student.student_number' => null,
        ]);

        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseMissing('users', [
            'email' => 'registrar.demo@sims.test',
        ]);

        $this->assertDatabaseMissing('students', [
            'student_number' => 'SIMS-DEMO-2026-001',
        ]);
    }

    private function demoRecordCounts(): array
    {
        $studentIds = Student::where('student_number', 'like', 'SIMS-DEMO-2026-%')->select('id');

        return [
            'users' => User::where('email', 'like', '%demo@sims.test')->count(),
            'students' => Student::where('student_number', 'like', 'SIMS-DEMO-2026-%')->count(),
            'sections' => Section::where('code', 'like', 'DEMO-%')->count(),
            'enrollments' => Enrollment::whereIn('student_id', $studentIds)->count(),
            'visitor_timeouts' => StudentVisitorTimeout::whereIn('student_id', $studentIds)->count(),
        ];
    }
}
