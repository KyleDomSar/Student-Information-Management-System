<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSecurityToolsTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_cannot_access_user_management_or_audit_logs(): void
    {
        $staff = User::factory()->create(['role' => User::ROLE_STAFF]);

        $this->actingAs($staff)
            ->get(route('admin.users.index'))
            ->assertForbidden();

        $this->actingAs($staff)
            ->get(route('admin.audit-logs.index'))
            ->assertForbidden();
    }

    public function test_admin_can_open_user_management_and_audit_logs(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('admin.audit-logs.index'))
            ->assertOk();
    }

    public function test_instructor_accounts_are_not_managed_through_admin_user_management(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $instructor = User::factory()->create(['role' => 'instructor']);

        $this->actingAs($admin)
            ->get(route('admin.users.edit', $instructor))
            ->assertNotFound();

        $this->actingAs($admin)
            ->put(route('admin.users.update', $instructor), [
                'name' => $instructor->name,
                'email' => $instructor->email,
                'role' => User::ROLE_STAFF,
                'is_active' => '1',
            ])
            ->assertNotFound();

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $instructor))
            ->assertNotFound();

        $this->assertDatabaseHas('users', [
            'id' => $instructor->id,
            'role' => 'instructor',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_create_a_staff_account(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->post(route('admin.users.store'), [
                'name' => 'Registrar Staff',
                'email' => 'registrar@example.test',
                'password' => 'A-Strong-Staff-Password-2026',
                'password_confirmation' => 'A-Strong-Staff-Password-2026',
                'role' => User::ROLE_STAFF,
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.users.index'));

        $staff = User::where('email', 'registrar@example.test')->first();

        $this->assertNotNull($staff);
        $this->assertSame(User::ROLE_STAFF, $staff->role);
        $this->assertTrue($staff->is_active);
        $this->assertTrue(password_verify('A-Strong-Staff-Password-2026', $staff->password));

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'action' => 'user.created',
            'target_type' => 'User',
            'target_id' => $staff->id,
        ]);
    }

    public function test_admin_cannot_deactivate_their_own_account(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $admin))
            ->assertSessionHasErrors('account');

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'is_active' => true,
        ]);
    }

    public function test_admin_cannot_change_own_role_or_deactivate_own_account(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $staff = User::factory()->create(['role' => User::ROLE_STAFF]);

        $this->actingAs($admin)
            ->put(route('admin.users.update', $admin), [
                'name' => $admin->name,
                'email' => $admin->email,
                'role' => User::ROLE_STAFF,
                'is_active' => '1',
            ])
            ->assertSessionHasErrors('role');

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $admin))
            ->assertSessionHasErrors('account');

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $staff->id,
            'role' => User::ROLE_STAFF,
        ]);
    }

    public function test_admin_can_deactivate_another_admin_while_remaining_active(): void
    {
        $actingAdmin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $otherAdmin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($actingAdmin)
            ->delete(route('admin.users.destroy', $otherAdmin))
            ->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', [
            'id' => $actingAdmin->id,
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $otherAdmin->id,
            'role' => User::ROLE_ADMIN,
            'is_active' => false,
        ]);
    }

    public function test_audit_log_search_displays_recorded_events(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        AuditLog::create([
            'user_id' => $admin->id,
            'action' => 'student.created',
            'target_type' => 'Student',
            'target_id' => 12,
            'description' => 'Created fictional student profile.',
            'created_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.audit-logs.index', ['search' => 'student.created']))
            ->assertOk()
            ->assertSee('student.created')
            ->assertSee('Created fictional student profile.');
    }
}
