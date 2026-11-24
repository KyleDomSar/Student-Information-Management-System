<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_selection_page_is_available_to_guests(): void
    {
        $this->get(route('login'))->assertOk();
    }

    public function test_student_cannot_access_admin_dashboard(): void
    {
        $student = User::factory()->create(['role' => User::ROLE_STUDENT]);

        $this->actingAs($student)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_staff_cannot_access_student_portal(): void
    {
        $staff = User::factory()->create(['role' => User::ROLE_STAFF]);

        $this->actingAs($staff)
            ->get(route('student.dashboard'))
            ->assertForbidden();
    }

    public function test_student_credentials_are_rejected_by_admin_login(): void
    {
        $student = User::factory()->create([
            'role' => User::ROLE_STUDENT,
            'password' => 'password',
        ]);

        $this->post(route('admin.login.store'), [
            'email' => $student->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_instructor_cannot_sign_in_to_the_admin_staff_portal_yet(): void
    {
        $instructor = User::factory()->create([
            'role' => 'instructor',
            'password' => 'password',
        ]);

        $this->post(route('admin.login.store'), [
            'email' => $instructor->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_student_can_sign_in_to_student_portal(): void
    {
        $student = User::factory()->create([
            'role' => User::ROLE_STUDENT,
            'password' => 'password',
        ]);

        $this->post(route('student.login.store'), [
            'email' => $student->email,
            'password' => 'password',
        ])->assertRedirect(route('student.dashboard'));

        $this->assertAuthenticatedAs($student);
    }

    public function test_inactive_account_cannot_sign_in(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'is_active' => false,
            'password' => 'password',
        ]);

        $this->post(route('admin.login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }
}
