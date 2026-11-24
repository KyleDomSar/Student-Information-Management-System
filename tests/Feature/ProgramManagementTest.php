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

    public function test_admin_can_create_and_deactivate_an_academic_program(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->post(route('admin.programs.store'), [
                'code' => 'BSIT',
                'name' => 'Bachelor of Science in Information Technology',
                'description' => 'Information technology academic program.',
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
}
