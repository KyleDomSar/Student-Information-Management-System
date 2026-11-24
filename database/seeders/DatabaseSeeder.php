<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Create initial accounts only when local .env values are supplied.
     * Never commit real credentials or use this seeder with real student data.
     */
    public function run(): void
    {
        $this->call([
            CeitProgramSeeder::class,
            YearLevelSeeder::class,
        ]);

        $admin = config('sims.seed_admin');

        if ($admin['email'] && $admin['password']) {
            User::updateOrCreate(
                ['email' => $admin['email']],
                [
                    'name' => $admin['name'],
                    'password' => $admin['password'],
                    'role' => User::ROLE_ADMIN,
                    'is_active' => true,
                ],
            );
            $this->command?->info('Initial SIMS administrator account is ready.');
        } else {
            $this->command?->warn('Admin account skipped. Set SIMS_ADMIN_EMAIL and SIMS_ADMIN_PASSWORD in your local .env.');
        }

        $student = config('sims.seed_student');

        if ($student['email'] && $student['password'] && $student['student_number']) {
            $studentUser = User::updateOrCreate(
                ['email' => $student['email']],
                [
                    'name' => $student['name'],
                    'password' => $student['password'],
                    'role' => User::ROLE_STUDENT,
                    'is_active' => true,
                ],
            );

            Student::updateOrCreate(
                ['user_id' => $studentUser->id],
                [
                    'student_number' => $student['student_number'],
                    'status' => 'active',
                ],
            );
            $this->command?->info('Optional demo student account and profile are ready.');
        }
    }
}
