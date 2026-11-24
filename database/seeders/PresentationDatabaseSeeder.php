<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\Enrollment;
use App\Models\Program;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentVisitorTimeout;
use App\Models\User;
use App\Models\YearLevel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PresentationDatabaseSeeder extends Seeder
{
    /**
     * Seed a clean, fictional presentation environment.
     *
     * Run after a fresh migration with:
     * php artisan migrate:fresh --seed --seeder=PresentationDatabaseSeeder
     */
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new RuntimeException(
                'Presentation data can only be seeded in local or testing environments.'
            );
        }

        DB::transaction(function (): void {
            $this->call([
                CeitProgramSeeder::class,
                YearLevelSeeder::class,
            ]);

            $program = Program::query()
                ->where('code', 'BSIT')
                ->where('is_active', true)
                ->firstOrFail();

            $yearLevel = YearLevel::query()
                ->where('level_number', 3)
                ->where('is_active', true)
                ->firstOrFail();

            $section = Section::updateOrCreate(
                [
                    'program_id' => $program->id,
                    'year_level' => $yearLevel->level_number,
                    'code' => '3A',
                ],
                ['is_active' => true],
            );

            $admin = User::updateOrCreate(
                ['email' => 'admin.presentation@sims.test'],
                [
                    'name' => 'SIMS Presentation Administrator',
                    'password' => 'Admin@SIMS2026!',
                    'role' => User::ROLE_ADMIN,
                    'is_active' => true,
                ],
            );

            $staff = User::updateOrCreate(
                ['email' => 'registrar.presentation@sims.test'],
                [
                    'name' => 'Maria Elena Reyes',
                    'password' => 'Staff@SIMS2026!',
                    'role' => User::ROLE_STAFF,
                    'is_active' => true,
                ],
            );

            $studentUser = User::updateOrCreate(
                ['email' => 'alyssa.santos.presentation@sims.test'],
                [
                    'name' => 'Alyssa Mae Santos',
                    'password' => 'Student@SIMS2026!',
                    'role' => User::ROLE_STUDENT,
                    'is_active' => true,
                ],
            );

            $student = Student::updateOrCreate(
                ['user_id' => $studentUser->id],
                [
                    'student_number' => '2026-CEIT-IT-0001',
                    'program_id' => $program->id,
                    'year_level' => $yearLevel->level_number,
                    'section_id' => $section->id,
                    'status' => 'active',
                ],
            );

            $enrollments = [
                ['2026-2027', 'First Semester'],
                ['2025-2026', 'Second Semester'],
            ];

            foreach ($enrollments as [$academicYear, $semester]) {
                $enrollment = Enrollment::firstOrCreate(
                    [
                        'student_id' => $student->id,
                        'academic_year' => $academicYear,
                        'semester' => $semester,
                    ],
                    ['created_by' => $staff->id],
                );

                AuditLog::firstOrCreate(
                    [
                        'action' => 'enrollment.created',
                        'target_type' => 'Enrollment',
                        'target_id' => $enrollment->id,
                    ],
                    [
                        'user_id' => $staff->id,
                        'description' => '[PRESENTATION] Sample enrollment for '.$student->student_number.' ('.$academicYear.', '.$semester.').',
                        'created_at' => Carbon::parse($academicYear === '2026-2027'
                            ? '2026-11-25 09:15:00'
                            : '2026-06-15 09:15:00'),
                    ],
                );
            }

            $visitorTimeout = $student->visitorTimeouts()->firstOrCreate([
                'time_out_at' => Carbon::parse('2026-11-24 15:30:00'),
            ]);

            AuditLog::firstOrCreate(
                [
                    'action' => 'student.visitor_time_out_recorded',
                    'target_type' => 'StudentVisitorTimeout',
                    'target_id' => $visitorTimeout->id,
                ],
                [
                    'user_id' => $studentUser->id,
                    'description' => '[PRESENTATION] Sample Visitor Time-Out recorded for '.$student->student_number.'.',
                    'created_at' => Carbon::parse('2026-11-24 15:32:00'),
                ],
            );

            AuditLog::firstOrCreate(
                [
                    'action' => 'student.created',
                    'target_type' => 'Student',
                    'target_id' => $student->id,
                ],
                [
                    'user_id' => $staff->id,
                    'description' => '[PRESENTATION] Created the sample student profile '.$student->student_number.'.',
                    'created_at' => Carbon::parse('2026-11-24 08:45:00'),
                ],
            );

            $this->command?->info('Fresh SIMS presentation database data is ready.');
            $this->command?->line('Created exactly three presentation accounts: Admin, Staff, and Student.');
            $this->command?->line('Student profile: '.$student->student_number.' | BSIT | Third Year | Section 3A');
        });
    }
}
