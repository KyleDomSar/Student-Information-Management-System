<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class StudentVisitorTimeoutTest extends TestCase
{
    use RefreshDatabase;

    private function createStudent(string $number): array
    {
        $user = User::factory()->create(['role' => User::ROLE_STUDENT]);
        $student = Student::create([
            'user_id' => $user->id,
            'student_number' => $number,
            'status' => 'active',
        ]);

        return [$user, $student];
    }

    public function test_student_can_record_a_server_timestamp_for_their_own_profile(): void
    {
        [$studentUser, $student] = $this->createStudent('SIMS-TIMEOUT-001');
        [, $otherStudent] = $this->createStudent('SIMS-TIMEOUT-002');

        Carbon::setTestNow(Carbon::parse('2026-11-24 23:15:00', config('app.timezone')));

        try {
            $this->actingAs($studentUser)
                ->post(route('student.visitor-timeouts.store'), [
                    'student_id' => $otherStudent->id,
                    'time_out_at' => '2000-01-01 00:00:00',
                ])
                ->assertRedirect(route('student.visitor-timeouts.index'))
                ->assertSessionHasNoErrors();

            $this->assertDatabaseCount('student_visitor_timeouts', 1);
            $this->assertDatabaseHas('student_visitor_timeouts', [
                'student_id' => $student->id,
                'time_out_at' => '2026-11-24 23:15:00',
            ]);
            $this->assertDatabaseMissing('student_visitor_timeouts', [
                'student_id' => $otherStudent->id,
            ]);
            $this->assertDatabaseHas('audit_logs', [
                'user_id' => $studentUser->id,
                'action' => 'student.visitor_time_out_recorded',
                'target_type' => 'StudentVisitorTimeout',
            ]);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_student_can_view_only_their_own_time_out_history(): void
    {
        [$firstUser, $firstStudent] = $this->createStudent('SIMS-TIMEOUT-003');
        [, $secondStudent] = $this->createStudent('SIMS-TIMEOUT-004');

        $ownRecord = $firstStudent->visitorTimeouts()->create(['time_out_at' => now()->subHour()]);
        $otherRecord = $secondStudent->visitorTimeouts()->create(['time_out_at' => now()]);

        $this->actingAs($firstUser)
            ->get(route('student.visitor-timeouts.index'))
            ->assertOk()
            ->assertSee('<td class="fw-semibold">#'.$ownRecord->id.'</td>', false)
            ->assertDontSee('<td class="fw-semibold">#'.$otherRecord->id.'</td>', false);
    }

    public function test_staff_cannot_access_student_visitor_time_out_routes(): void
    {
        $staff = User::factory()->create(['role' => User::ROLE_STAFF]);

        $this->actingAs($staff)
            ->get(route('student.visitor-timeouts.index'))
            ->assertForbidden();

        $this->actingAs($staff)
            ->post(route('student.visitor-timeouts.store'))
            ->assertForbidden();
    }

    public function test_student_without_a_linked_profile_cannot_record_time_out(): void
    {
        $studentUser = User::factory()->create(['role' => User::ROLE_STUDENT]);

        $this->actingAs($studentUser)
            ->get(route('student.visitor-timeouts.index'))
            ->assertNotFound();

        $this->actingAs($studentUser)
            ->post(route('student.visitor-timeouts.store'))
            ->assertNotFound();

        $this->assertDatabaseCount('student_visitor_timeouts', 0);
    }

    public function test_student_with_time_out_history_cannot_be_permanently_deleted(): void
    {
        [$studentUser, $student] = $this->createStudent('SIMS-TIMEOUT-005');
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $student->visitorTimeouts()->create(['time_out_at' => now()]);

        $this->actingAs($admin)
            ->delete(route('admin.students.destroy', $student))
            ->assertSessionHasErrors('student');

        $this->assertDatabaseHas('students', ['id' => $student->id]);
        $this->assertDatabaseHas('users', ['id' => $studentUser->id]);
        $this->assertDatabaseCount('student_visitor_timeouts', 1);
    }
}
