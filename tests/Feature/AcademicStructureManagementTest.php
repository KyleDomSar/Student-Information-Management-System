<?php

namespace Tests\Feature;

use App\Models\College;
use App\Models\Department;
use App\Models\Program;
use App\Models\ProgramMajor;
use App\Models\User;
use App\Models\YearLevel;
use Database\Seeders\CvsuMainCampusAcademicStructureSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcademicStructureManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_main_campus_reference_seeder_creates_eleven_colleges_and_five_ceit_departments(): void
    {
        $this->seed(CvsuMainCampusAcademicStructureSeeder::class);

        $this->assertDatabaseCount('colleges', 11);
        $this->assertDatabaseCount('departments', 5);
        $this->assertDatabaseHas('departments', ['code' => 'DIT']);
        $this->assertDatabaseHas('colleges', [
            'campus' => 'Main Campus',
            'name' => 'College of Engineering & Information Technology',
        ]);
    }

    public function test_staff_can_view_academic_structure_but_cannot_create_colleges(): void
    {
        $this->seed(CvsuMainCampusAcademicStructureSeeder::class);
        $staff = User::factory()->create(['role' => User::ROLE_STAFF]);

        $this->actingAs($staff)
            ->get(route('admin.academic-structure.index'))
            ->assertOk()
            ->assertSee('College of Education');

        $this->actingAs($staff)
            ->post(route('admin.academic-structure.colleges.store'), [
                'name' => 'New College',
                'campus' => 'Main Campus',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('colleges', ['name' => 'New College']);
    }

    public function test_admin_can_add_a_college_and_department(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->post(route('admin.academic-structure.colleges.store'), [
                'name' => 'College of Test Studies',
                'campus' => 'Main Campus',
            ])
            ->assertRedirect();

        $college = College::where('name', 'College of Test Studies')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.academic-structure.departments.store'), [
                'college_id' => $college->id,
                'code' => 'DTS',
                'name' => 'Department of Test Studies',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('departments', [
            'college_id' => $college->id,
            'code' => 'DTS',
            'name' => 'Department of Test Studies',
        ]);
    }

    public function test_program_stores_college_department_year_levels_and_majors(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $college = College::create(['name' => 'College of Agriculture', 'campus' => 'Main Campus']);
        $department = Department::create([
            'college_id' => $college->id,
            'code' => 'DAFE',
            'name' => 'Department of Agriculture and Food Engineering',
        ]);
        YearLevel::create(['level_number' => 1, 'name' => 'First Year']);
        YearLevel::create(['level_number' => 2, 'name' => 'Second Year']);

        $this->actingAs($admin)
            ->post(route('admin.programs.store'), [
                'college_id' => $college->id,
                'department_id' => $department->id,
                'code' => 'BSA',
                'name' => 'Bachelor of Science in Agriculture',
                'year_levels' => [1, 2],
                'majors' => "Animal Science\nCrop Science",
            ])
            ->assertRedirect(route('admin.programs.index'));

        $program = Program::where('code', 'BSA')->firstOrFail();

        $this->assertSame($college->id, $program->college_id);
        $this->assertSame($department->id, $program->department_id);
        $this->assertEqualsCanonicalizing([1, 2], $program->yearLevels()->pluck('year_levels.level_number')->all());
        $this->assertEqualsCanonicalizing(
            ['Animal Science', 'Crop Science'],
            $program->majors()->pluck('name')->all(),
        );
    }

    public function test_program_cannot_be_assigned_a_department_from_another_college(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $college = College::create(['name' => 'College A', 'campus' => 'Main Campus']);
        $otherCollege = College::create(['name' => 'College B', 'campus' => 'Main Campus']);
        $department = Department::create([
            'college_id' => $otherCollege->id,
            'code' => 'DB',
            'name' => 'Department B',
        ]);
        YearLevel::create(['level_number' => 1, 'name' => 'First Year']);

        $this->actingAs($admin)
            ->from(route('admin.programs.create'))
            ->post(route('admin.programs.store'), [
                'college_id' => $college->id,
                'department_id' => $department->id,
                'code' => 'TEST',
                'name' => 'Test Program',
                'year_levels' => [1],
            ])
            ->assertSessionHasErrors('department_id');

        $this->assertDatabaseMissing('programs', ['code' => 'TEST']);
    }
}
