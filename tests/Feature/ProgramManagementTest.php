<?php

namespace Tests\Feature;

use App\Models\Program;
use App\Models\User;
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

    public function test_instructor_cannot_manage_programs(): void
    {
        $instructor = User::factory()->create(['role' => User::ROLE_INSTRUCTOR]);

        $this->actingAs($instructor)
            ->get(route('admin.programs.index'))
            ->assertForbidden();
    }

    public function test_staff_can_create_an_academic_program(): void
    {
        $staff = User::factory()->create(['role' => User::ROLE_STAFF]);

        $this->actingAs($staff)
            ->post(route('admin.programs.store'), [
                'code' => 'BSIT',
                'name' => 'Bachelor of Science in Information Technology',
                'description' => 'Information technology academic program.',
            ])
            ->assertRedirect(route('admin.programs.index'));

        $this->assertDatabaseHas('programs', [
            'code' => 'BSIT',
            'name' => 'Bachelor of Science in Information Technology',
            'is_active' => true,
        ]);
    }

    public function test_staff_deactivates_program_without_deleting_it(): void
    {
        $staff = User::factory()->create(['role' => User::ROLE_STAFF]);
        $program = Program::create([
            'code' => 'BSCS',
            'name' => 'Bachelor of Science in Computer Science',
            'is_active' => true,
        ]);

        $this->actingAs($staff)
            ->delete(route('admin.programs.destroy', $program))
            ->assertRedirect(route('admin.programs.index'));

        $this->assertDatabaseHas('programs', [
            'id' => $program->id,
            'code' => 'BSCS',
            'is_active' => false,
        ]);
    }
}
