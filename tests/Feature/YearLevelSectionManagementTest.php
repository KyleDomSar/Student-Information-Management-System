<?php

namespace Tests\Feature;

use App\Models\Program;
use App\Models\Section;
use App\Models\Student;
use App\Models\User;
use App\Models\YearLevel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class YearLevelSectionManagementTest extends TestCase
{
    use RefreshDatabase;

    private function createYearLevels(): void
    {
        foreach (range(1, 6) as $number) {
            YearLevel::firstOrCreate(
                ['level_number' => $number],
                ['name' => $number.' Year', 'is_active' => true],
            );
        }
    }

    private function createProgram(): Program
    {
        return Program::create([
            'code' => 'BSIT',
            'name' => 'Bachelor of Science in Information Technology',
            'is_active' => true,
        ]);
    }

    public function test_staff_can_view_year_levels_and_sections(): void
    {
        $this->createYearLevels();
        $staff = User::factory()->create(['role' => User::ROLE_STAFF]);

        $this->actingAs($staff)
            ->get(route('admin.year-levels.index'))
            ->assertOk()
            ->assertSee('1 Year');

        $this->actingAs($staff)
            ->get(route('admin.sections.index'))
            ->assertOk();
    }

    public function test_staff_cannot_create_or_update_year_levels(): void
    {
        $this->createYearLevels();
        $staff = User::factory()->create(['role' => User::ROLE_STAFF]);
        $yearLevel = YearLevel::firstWhere('level_number', 1);

        $this->actingAs($staff)
            ->post(route('admin.year-levels.store'), [
                'level_number' => 5,
                'name' => 'Fifth Year',
            ])
            ->assertForbidden();

        $this->actingAs($staff)
            ->put(route('admin.year-levels.update', $yearLevel), [
                'level_number' => 1,
                'name' => 'Updated First Year',
                'is_active' => '1',
            ])
            ->assertForbidden();
    }

    public function test_admin_can_create_and_deactivate_a_year_level(): void
    {
        $this->createYearLevels();
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->post(route('admin.year-levels.store'), [
                'level_number' => 7,
                'name' => 'Seventh Year',
            ])
            ->assertRedirect(route('admin.year-levels.index'));

        $yearLevel = YearLevel::firstWhere('level_number', 7);
        $this->assertNotNull($yearLevel);

        $this->actingAs($admin)
            ->delete(route('admin.year-levels.destroy', $yearLevel))
            ->assertRedirect(route('admin.year-levels.index'));

        $this->assertDatabaseHas('year_levels', [
            'id' => $yearLevel->id,
            'name' => 'Seventh Year',
            'is_active' => false,
        ]);
    }

    public function test_staff_cannot_create_or_deactivate_sections(): void
    {
        $this->createYearLevels();
        $program = $this->createProgram();
        $section = Section::create([
            'program_id' => $program->id,
            'year_level' => 1,
            'code' => '1A',
            'is_active' => true,
        ]);
        $staff = User::factory()->create(['role' => User::ROLE_STAFF]);

        $this->actingAs($staff)
            ->post(route('admin.sections.store'), [
                'program_id' => $program->id,
                'year_level' => 1,
                'code' => '1B',
            ])
            ->assertForbidden();

        $this->actingAs($staff)
            ->delete(route('admin.sections.destroy', $section))
            ->assertForbidden();

        $this->assertDatabaseHas('sections', ['id' => $section->id, 'is_active' => true]);
    }

    public function test_admin_can_create_a_section_for_a_program_and_year_level(): void
    {
        $this->createYearLevels();
        $program = $this->createProgram();
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->post(route('admin.sections.store'), [
                'program_id' => $program->id,
                'year_level' => 1,
                'code' => '1A',
            ])
            ->assertRedirect(route('admin.sections.index'));

        $this->assertDatabaseHas('sections', [
            'program_id' => $program->id,
            'year_level' => 1,
            'code' => '1A',
            'is_active' => true,
        ]);
    }

    public function test_duplicate_section_code_is_rejected_within_program_and_year(): void
    {
        $this->createYearLevels();
        $program = $this->createProgram();
        Section::create([
            'program_id' => $program->id,
            'year_level' => 1,
            'code' => '1A',
            'is_active' => true,
        ]);
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->from(route('admin.sections.create'))
            ->post(route('admin.sections.store'), [
                'program_id' => $program->id,
                'year_level' => 1,
                'code' => '1A',
            ])
            ->assertSessionHasErrors('code');

        $this->assertDatabaseCount('sections', 1);
    }

    public function test_student_can_be_assigned_only_to_a_section_matching_program_and_year(): void
    {
        $this->createYearLevels();
        $program = $this->createProgram();
        $section = Section::create([
            'program_id' => $program->id,
            'year_level' => 2,
            'code' => '2A',
            'is_active' => true,
        ]);
        $staff = User::factory()->create(['role' => User::ROLE_STAFF]);

        $this->actingAs($staff)
            ->post(route('admin.students.store'), [
                'name' => 'Section Student',
                'email' => 'section.student@example.test',
                'password' => 'A-Strong-Student-Password-2026',
                'password_confirmation' => 'A-Strong-Student-Password-2026',
                'student_number' => 'SIMS-SECTION-001',
                'program_id' => $program->id,
                'year_level' => 2,
                'section_id' => $section->id,
                'status' => 'active',
            ])
            ->assertRedirect();

        $student = Student::where('student_number', 'SIMS-SECTION-001')->firstOrFail();
        $this->assertSame($section->id, $student->section_id);

        $this->actingAs($staff)
            ->post(route('admin.students.store'), [
                'name' => 'Mismatched Student',
                'email' => 'mismatched.student@example.test',
                'password' => 'A-Strong-Student-Password-2026',
                'password_confirmation' => 'A-Strong-Student-Password-2026',
                'student_number' => 'SIMS-SECTION-002',
                'program_id' => $program->id,
                'year_level' => 1,
                'section_id' => $section->id,
                'status' => 'active',
            ])
            ->assertSessionHasErrors('section_id');

        $this->assertDatabaseMissing('students', ['student_number' => 'SIMS-SECTION-002']);
    }

    public function test_admin_can_search_year_levels_by_number_or_name_and_filter_status(): void
    {
        $this->createYearLevels();
        YearLevel::where('level_number', 4)->update(['is_active' => false]);

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->get(route('admin.year-levels.index', ['search' => '3']))
            ->assertOk()
            ->assertSee('3 Year')
            ->assertDontSee('2 Year');

        $this->actingAs($admin)
            ->get(route('admin.year-levels.index', ['search' => 'Second Year']))
            ->assertOk()
            ->assertSee('2 Year')
            ->assertDontSee('3 Year');

        $this->actingAs($admin)
            ->get(route('admin.year-levels.index', ['status' => 'inactive']))
            ->assertOk()
            ->assertSee('4 Year')
            ->assertDontSee('3 Year');
    }

    public function test_admin_can_search_and_filter_sections_by_program_year_level_and_status(): void
    {
        $this->createYearLevels();
        $program = $this->createProgram();

        Section::create([
            'program_id' => $program->id,
            'year_level' => 1,
            'code' => '1A',
            'is_active' => true,
        ]);

        Section::create([
            'program_id' => $program->id,
            'year_level' => 2,
            'code' => '2A',
            'is_active' => true,
        ]);

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->get(route('admin.sections.index', ['search' => '2 Year']))
            ->assertOk()
            ->assertSee('2A')
            ->assertDontSee('1A');

        $this->actingAs($admin)
            ->get(route('admin.sections.index', ['search' => 'BSIT']))
            ->assertOk()
            ->assertSee('1A')
            ->assertSee('2A');

        $this->actingAs($admin)
            ->get(route('admin.sections.index', [
                'program_id' => $program->id,
                'year_level' => 2,
                'status' => 'active',
            ]))
            ->assertOk()
            ->assertSee('2A')
            ->assertDontSee('1A');
    }

    public function test_admin_can_update_and_deactivate_a_section_without_deleting_it(): void
    {
        $this->createYearLevels();
        $program = $this->createProgram();
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $section = Section::create([
            'program_id' => $program->id,
            'year_level' => 1,
            'code' => '1A',
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->put(route('admin.sections.update', $section), [
                'program_id' => $program->id,
                'year_level' => 1,
                'code' => '1B',
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.sections.index'));

        $this->assertDatabaseHas('sections', [
            'id' => $section->id,
            'code' => '1B',
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.sections.destroy', $section))
            ->assertRedirect(route('admin.sections.index'));

        $this->assertDatabaseHas('sections', [
            'id' => $section->id,
            'code' => '1B',
            'is_active' => false,
        ]);
    }
}
