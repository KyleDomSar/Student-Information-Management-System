<?php

namespace Tests\Feature;

use App\Models\Program;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubjectManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_view_subject_directory(): void
    {
        $staff = User::factory()->create(['role' => User::ROLE_STAFF]);

        $this->actingAs($staff)
            ->get(route('admin.subjects.index'))
            ->assertOk();
    }

    public function test_student_cannot_access_subject_management(): void
    {
        $student = User::factory()->create(['role' => User::ROLE_STUDENT]);

        $this->actingAs($student)
            ->get(route('admin.subjects.index'))
            ->assertForbidden();
    }

    public function test_staff_cannot_create_or_deactivate_subjects(): void
    {
        $staff = User::factory()->create(['role' => User::ROLE_STAFF]);
        $subject = Subject::create([
            'code' => 'IT102',
            'title' => 'Computer Programming 1',
            'units' => 3,
            'is_active' => true,
        ]);

        $this->actingAs($staff)
            ->post(route('admin.subjects.store'), [
                'code' => 'IT101',
                'title' => 'Introduction to Computing',
                'units' => 3,
            ])
            ->assertForbidden();

        $this->actingAs($staff)
            ->delete(route('admin.subjects.destroy', $subject))
            ->assertForbidden();

        $this->assertDatabaseMissing('subjects', ['code' => 'IT101']);
        $this->assertDatabaseHas('subjects', ['id' => $subject->id, 'is_active' => true]);
    }

    public function test_admin_can_create_and_deactivate_a_subject(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $program = Program::create([
            'code' => 'BSIT',
            'name' => 'Bachelor of Science in Information Technology',
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.subjects.store'), [
                'program_id' => $program->id,
                'code' => 'IT101',
                'title' => 'Introduction to Computing',
                'units' => 3,
            ])
            ->assertRedirect(route('admin.subjects.index'));

        $subject = Subject::where('code', 'IT101')->firstOrFail();

        $this->actingAs($admin)
            ->delete(route('admin.subjects.destroy', $subject))
            ->assertRedirect(route('admin.subjects.index'));

        $this->assertDatabaseHas('subjects', [
            'id' => $subject->id,
            'is_active' => false,
        ]);
    }
}
