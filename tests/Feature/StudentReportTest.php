<?php

namespace Tests\Feature;

use App\Models\Program;
use App\Models\Section;
use App\Models\Student;
use App\Models\User;
use App\Models\YearLevel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentReportTest extends TestCase
{
    use RefreshDatabase;

    private function createReportData(): array
    {
        $yearLevel = YearLevel::create([
            'level_number' => 1,
            'name' => 'First Year',
            'is_active' => true,
        ]);

        $program = Program::create([
            'code' => 'BSIT',
            'name' => 'Bachelor of Science in Information Technology',
            'is_active' => true,
        ]);

        $section = Section::create([
            'program_id' => $program->id,
            'year_level' => $yearLevel->level_number,
            'code' => '1A',
            'is_active' => true,
        ]);

        $studentUser = User::factory()->create([
            'name' => 'Sample Report Student',
            'email' => 'report.student@example.test',
            'role' => User::ROLE_STUDENT,
        ]);

        $student = Student::create([
            'user_id' => $studentUser->id,
            'student_number' => 'SIMS-REPORT-001',
            'program_id' => $program->id,
            'year_level' => $yearLevel->level_number,
            'section_id' => $section->id,
            'status' => 'active',
        ]);

        return compact('yearLevel', 'program', 'section', 'studentUser', 'student');
    }

    public function test_staff_can_view_filtered_student_information_reports(): void
    {
        $data = $this->createReportData();
        $staff = User::factory()->create(['role' => User::ROLE_STAFF]);

        $this->actingAs($staff)
            ->get(route('admin.reports.students', [
                'program_id' => $data['program']->id,
                'year_level' => $data['yearLevel']->level_number,
                'section_id' => $data['section']->id,
                'status' => 'active',
            ]))
            ->assertOk()
            ->assertSee('SIMS-REPORT-001')
            ->assertSee('Sample Report Student')
            ->assertSee('1A');
    }

    public function test_staff_can_export_student_information_as_csv(): void
    {
        $this->createReportData();
        $staff = User::factory()->create(['role' => User::ROLE_STAFF]);

        $this->actingAs($staff)
            ->get(route('admin.reports.students.export'))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8')
            ->assertHeader('content-disposition');

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $staff->id,
            'action' => 'student.report_exported',
        ]);
    }

    public function test_student_cannot_access_student_information_reports_or_exports(): void
    {
        $student = User::factory()->create(['role' => User::ROLE_STUDENT]);

        $this->actingAs($student)
            ->get(route('admin.reports.students'))
            ->assertForbidden();

        $this->actingAs($student)
            ->get(route('admin.reports.students.export'))
            ->assertForbidden();
    }
}
