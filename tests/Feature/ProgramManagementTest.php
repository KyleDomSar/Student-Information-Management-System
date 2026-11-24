<?php

namespace Tests\Feature;

use App\Models\Program;
use App\Models\Section;
use App\Models\Student;
use App\Models\User;
use App\Models\YearLevel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProgramManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_view_program_directory(): void
    {
        $staff = User::factory()->create(['role' => User::ROLE_STAFF]);

        $this->actingAs($staff)
            ->get(route('admin.programs.index'))
            ->assertOk();
    }

    public function test_staff_cannot_create_or_deactivate_programs(): void
    {
        $staff = User::factory()->create(['role' => User::ROLE_STAFF]);
        $program = Program::create([
            'code' => 'BSCS',
            'name' => 'Bachelor of Science in Computer Science',
            'is_active' => true,
        ]);

        $this->actingAs($staff)
            ->post(route('admin.programs.store'), [
                'code' => 'BSIT',
                'name' => 'Bachelor of Science in Information Technology',
            ])
            ->assertForbidden();

        $this->actingAs($staff)
            ->delete(route('admin.programs.destroy', $program))
            ->assertForbidden();

        $this->assertDatabaseMissing('programs', ['code' => 'BSIT']);
        $this->assertDatabaseHas('programs', ['id' => $program->id, 'is_active' => true]);
    }

    public function test_admin_can_create_and_deactivate_a_program(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->post(route('admin.programs.store'), [
                'code' => 'BSIT',
                'name' => 'Bachelor of Science in Information Technology',
                'description' => 'Information technology program.',
            ])
            ->assertRedirect(route('admin.programs.index'));

        $program = Program::where('code', 'BSIT')->firstOrFail();

        $this->assertTrue($program->is_active);

        $this->actingAs($admin)
            ->delete(route('admin.programs.destroy', $program))
            ->assertRedirect(route('admin.programs.index'));

        $this->assertDatabaseHas('programs', [
            'id' => $program->id,
            'code' => 'BSIT',
            'is_active' => false,
        ]);
    }

    public function test_unknown_legacy_role_cannot_view_program_directory(): void
    {
        $legacyUser = User::factory()->create(['role' => 'instructor']);

        $this->actingAs($legacyUser)
            ->get(route('admin.programs.index'))
            ->assertForbidden();
    }

    public function test_admin_can_search_programs_and_filter_by_status(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        Program::create([
            'code' => 'BSIT',
            'name' => 'Bachelor of Science in Information Technology',
            'is_active' => true,
        ]);

        Program::create([
            'code' => 'BSCS',
            'name' => 'Bachelor of Science in Computer Science',
            'is_active' => false,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.programs.index', ['search' => 'BSIT']))
            ->assertOk()
            ->assertSee('BSIT')
            ->assertDontSee('BSCS');

        $this->actingAs($admin)
            ->get(route('admin.programs.index', ['search' => 'Computer Science']))
            ->assertOk()
            ->assertSee('BSCS')
            ->assertDontSee('BSIT');

        $this->actingAs($admin)
            ->get(route('admin.programs.index', ['status' => 'inactive']))
            ->assertOk()
            ->assertSee('BSCS')
            ->assertDontSee('BSIT');
    }

    public function test_sections_under_inactive_programs_are_shown_as_inactive_and_filtered_correctly(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $program = Program::create([
            'code' => 'BSTESTING',
            'name' => 'Test Program',
            'is_active' => true,
        ]);
        YearLevel::create([
            'level_number' => 1,
            'name' => 'First Year',
            'is_active' => true,
        ]);
        $section = Section::create([
            'program_id' => $program->id,
            'year_level' => 1,
            'code' => '1A',
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.programs.destroy', $program))
            ->assertRedirect(route('admin.programs.index'));

        $this->assertDatabaseHas('sections', [
            'id' => $section->id,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.sections.index'))
            ->assertOk()
            ->assertSee('BSTESTING')
            ->assertSee('1A')
            ->assertSee('Program inactive')
            ->assertSee('Inactive');

        $this->actingAs($admin)
            ->get(route('admin.sections.index', ['status' => 'active']))
            ->assertOk()
            ->assertDontSee('1A');

        $this->actingAs($admin)
            ->get(route('admin.sections.index', ['status' => 'inactive']))
            ->assertOk()
            ->assertSee('1A');
    }

    public function test_student_creation_does_not_offer_sections_under_inactive_programs(): void
    {
        $staff = User::factory()->create(['role' => User::ROLE_STAFF]);
        $activeProgram = Program::create([
            'code' => 'BSACTIVE',
            'name' => 'Active Test Program',
            'is_active' => true,
        ]);
        $inactiveProgram = Program::create([
            'code' => 'BSINACTIVE',
            'name' => 'Inactive Test Program',
            'is_active' => false,
        ]);
        YearLevel::create([
            'level_number' => 1,
            'name' => 'First Year',
            'is_active' => true,
        ]);
        Section::create([
            'program_id' => $activeProgram->id,
            'year_level' => 1,
            'code' => 'ACTIVE-1A',
            'is_active' => true,
        ]);
        Section::create([
            'program_id' => $inactiveProgram->id,
            'year_level' => 1,
            'code' => 'INACTIVE-1A',
            'is_active' => true,
        ]);

        $this->actingAs($staff)
            ->get(route('admin.students.create'))
            ->assertOk()
            ->assertSee('ACTIVE-1A')
            ->assertDontSee('INACTIVE-1A');
    }

    public function test_student_cannot_be_reassigned_to_another_section_under_an_inactive_program(): void
    {
        $staff = User::factory()->create(['role' => User::ROLE_STAFF]);
        $studentUser = User::factory()->create(['role' => User::ROLE_STUDENT]);
        $program = Program::create([
            'code' => 'BSINACTIVE',
            'name' => 'Inactive Test Program',
            'is_active' => false,
        ]);
        YearLevel::create([
            'level_number' => 1,
            'name' => 'First Year',
            'is_active' => true,
        ]);

        $currentSection = Section::create([
            'program_id' => $program->id,
            'year_level' => 1,
            'code' => 'CURRENT-1A',
            'is_active' => true,
        ]);
        $otherSection = Section::create([
            'program_id' => $program->id,
            'year_level' => 1,
            'code' => 'OTHER-1A',
            'is_active' => true,
        ]);
        $student = Student::create([
            'user_id' => $studentUser->id,
            'student_number' => 'SIMS-INACTIVE-PROGRAM-001',
            'program_id' => $program->id,
            'year_level' => 1,
            'section_id' => $currentSection->id,
            'status' => 'active',
        ]);

        $this->actingAs($staff)
            ->put(route('admin.students.update', $student), [
                'name' => $studentUser->name,
                'email' => $studentUser->email,
                'student_number' => $student->student_number,
                'program_id' => $program->id,
                'year_level' => 1,
                'section_id' => $otherSection->id,
                'status' => 'active',
            ])
            ->assertSessionHasErrors('section_id');

        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'section_id' => $currentSection->id,
        ]);
    }
}
