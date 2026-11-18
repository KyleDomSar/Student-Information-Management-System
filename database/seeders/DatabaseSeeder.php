<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Create initial accounts only when local .env values are supplied.
     *
     * Never commit real credentials or use this seeder with real student data.
     */
    public function run(): void
    {
        $adminEmail = env('SIMS_ADMIN_EMAIL');
        $adminPassword = env('SIMS_ADMIN_PASSWORD');

        if ($adminEmail && $adminPassword) {
            User::updateOrCreate(
                ['email' => $adminEmail],
                [
                    'name' => env('SIMS_ADMIN_NAME', 'SIMS Administrator'),
                    'password' => $adminPassword,
                    'role' => User::ROLE_ADMIN,
                    'is_active' => true,
                ],
            );

            $this->command?->info('Initial SIMS administrator account is ready.');
        } else {
            $this->command?->warn('Admin account skipped. Set SIMS_ADMIN_EMAIL and SIMS_ADMIN_PASSWORD in your local .env.');
        }

        $studentEmail = env('SIMS_STUDENT_EMAIL');
        $studentPassword = env('SIMS_STUDENT_PASSWORD');
        $studentNumber = env('SIMS_STUDENT_NUMBER');

        if ($studentEmail && $studentPassword && $studentNumber) {
            $studentUser = User::updateOrCreate(
                ['email' => $studentEmail],
                [
                    'name' => env('SIMS_STUDENT_NAME', 'Demo Student'),
                    'password' => $studentPassword,
                    'role' => User::ROLE_STUDENT,
                    'is_active' => true,
                ],
            );

            Student::updateOrCreate(
                ['user_id' => $studentUser->id],
                [
                    'student_number' => $studentNumber,
                    'status' => 'active',
                ],
            );

            $this->command?->info('Optional demo student account and profile are ready.');
        }
    }
}
