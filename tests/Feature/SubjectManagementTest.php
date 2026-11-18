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

    public function test_staff_can_create_a_subject(): void
    {
        $staff = User::factory()->create(['role' => User::ROLE_STAFF]);
        $program = Program::create([
            'code' => 'BSIT',
            'name' => 'Bachelor of Science in Information Technology',
            'is_active' => true,
        ]);

        $this->actingAs($staff)
            ->post(route('admin.subjects.store'), [
                'program_id' => $program->id,
                'code' => 'IT101',
                'title' => 'Introduction to Computing',
                'units' => 3,
            ])
            ->assertRedirect(route('admin.subjects.index'));

        $this->assertDatabaseHas('subjects', [
            'program_id' => $program->id,
            'code' => 'IT101',
            'title' => 'Introduction to Computing',
            'units' => 3,
            'is_active' => true,
        ]);
    }

    public function test_deactivating_subject_preserves_existing_catalog_record(): void
    {
        $staff = User::factory()->create(['role' => User::ROLE_STAFF]);
        $subject = Subject::create([
            'code' => 'IT102',
            'title' => 'Computer Programming 1',
            'units' => 3,
            'is_active' => true,
        ]);

        $this->actingAs($staff)
            ->delete(route('admin.subjects.destroy', $subject))
            ->assertRedirect(route('admin.subjects.index'));

        $this->assertDatabaseHas('subjects', [
            'id' => $subject->id,
            'code' => 'IT102',
            'is_active' => false,
        ]);
    }
}
