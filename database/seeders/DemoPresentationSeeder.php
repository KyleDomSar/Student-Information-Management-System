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

class DemoPresentationSeeder extends Seeder
{
    /**
     * Seed fictional, repeatable presentation records.
     *
     * This seeder is intentionally excluded from DatabaseSeeder.
     * Run it explicitly with: php artisan db:seed --class=DemoPresentationSeeder
     */
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new RuntimeException(
                'Demo presentation data can only be seeded in local or testing environments.'
            );
        }

        DB::transaction(function (): void {
            $staff = User::updateOrCreate(
                ['email' => 'registrar.demo@sims.test'],
                [
                    'name' => 'Demo Registrar',
                    'password' => 'Demo@2026!',
                    'role' => User::ROLE_STAFF,
                    'is_active' => true,
                ],
            );

            $programs = Program::query()
                ->whereIn('code', ['BSIT', 'BSCS', 'BSCpE', 'BSCE'])
                ->where('is_active', true)
                ->get()
                ->keyBy('code');

            if (! $programs->has('BSIT')) {
                throw new RuntimeException(
                    'An active BSIT program is required. Check Programs before seeding demo data.'
                );
            }

            $yearLevels = YearLevel::query()
                ->whereIn('level_number', [1, 2, 3])
                ->where('is_active', true)
                ->orderBy('level_number')
                ->get()
                ->keyBy('level_number');

            if ($yearLevels->isEmpty()) {
                throw new RuntimeException(
                    'At least one active Year Level (1 to 3) is required. Check Year Levels before seeding demo data.'
                );
            }

            // Only create presentation sections under programs and year levels
            // that are already active. Existing academic setup is never reactivated.
            foreach ($programs as $program) {
                foreach ($yearLevels as $yearLevel) {
                    Section::updateOrCreate(
                        [
                            'program_id' => $program->id,
                            'year_level' => $yearLevel->level_number,
                            'code' => 'DEMO-'.$yearLevel->level_number.'A',
                        ],
                        ['is_active' => true],
                    );
                }
            }

            $students = [
                [
                    'number' => 'SIMS-DEMO-2026-001',
                    'name' => 'Alyssa Mae Santos',
                    'email' => 'alyssa.santos.demo@sims.test',
                    'program' => 'BSIT',
                    'year' => 3,
                    'active' => true,
                    'history' => true,
                ],
                [
                    'number' => 'SIMS-DEMO-2026-002',
                    'name' => 'Joshua Miguel Reyes',
                    'email' => 'joshua.reyes.demo@sims.test',
                    'program' => 'BSIT',
                    'year' => 2,
                    'active' => true,
                    'history' => true,
                ],
                [
                    'number' => 'SIMS-DEMO-2026-003',
                    'name' => 'Nicole Anne Dela Cruz',
                    'email' => 'nicole.delacruz.demo@sims.test',
                    'program' => 'BSCS',
                    'year' => 1,
                    'active' => true,
                    'history' => true,
                ],
                [
                    'number' => 'SIMS-DEMO-2026-004',
                    'name' => 'Gabriel Luis Tan',
                    'email' => 'gabriel.tan.demo@sims.test',
                    'program' => 'BSCpE',
                    'year' => 2,
                    'active' => true,
                    'history' => true,
                ],
                [
                    'number' => 'SIMS-DEMO-2026-005',
                    'name' => 'Bianca Rose Mendoza',
                    'email' => 'bianca.mendoza.demo@sims.test',
                    'program' => 'BSIT',
                    'year' => 1,
                    'active' => true,
                    'history' => false,
                ],
                [
                    'number' => 'SIMS-DEMO-2026-006',
                    'name' => 'Marco Paolo Garcia',
                    'email' => 'marco.garcia.demo@sims.test',
                    'program' => 'BSCE',
                    'year' => 1,
                    'active' => true,
                    'history' => true,
                ],
                [
                    'number' => 'SIMS-DEMO-2026-007',
                    'name' => 'Sofia Isabelle Flores',
                    'email' => 'sofia.flores.demo@sims.test',
                    'program' => 'BSCS',
                    'year' => 3,
                    'active' => true,
                    'history' => true,
                ],
                [
                    'number' => 'SIMS-DEMO-2026-008',
                    'name' => 'Daniel Miguel Cruz',
                    'email' => 'daniel.cruz.demo@sims.test',
                    'program' => 'BSCpE',
                    'year' => 1,
                    'active' => true,
                    'history' => false,
                ],
                [
                    'number' => 'SIMS-DEMO-2026-009',
                    'name' => 'Andrea Nicole Villanueva',
                    'email' => 'andrea.villanueva.demo@sims.test',
                    'program' => 'BSIT',
                    'year' => 2,
                    'active' => true,
                    'history' => true,
                ],
                [
                    'number' => 'SIMS-DEMO-2026-010',
                    'name' => 'Nathaniel James Ramos',
                    'email' => 'nathaniel.ramos.demo@sims.test',
                    'program' => 'BSIT',
                    'year' => 3,
                    'active' => true,
                    'history' => false,
                ],
                [
                    'number' => 'SIMS-DEMO-2026-011',
                    'name' => 'Carlo Miguel Bautista',
                    'email' => 'carlo.bautista.demo@sims.test',
                    'program' => 'BSIT',
                    'year' => 1,
                    'active' => false,
                    'history' => true,
                ],
            ];

            $demoStudents = [];

            foreach ($students as $index => $data) {
                // Fall back to BSIT if an optional CEIT program is inactive.
                $programCode = $programs->has($data['program']) ? $data['program'] : 'BSIT';
                $program = $programs->get($programCode);

                // Keep the dataset compatible with whichever early year levels
                // are currently active without changing their status.
                $yearLevel = $yearLevels->has($data['year'])
                    ? $yearLevels->get($data['year'])
                    : $yearLevels->first();

                $sectionCode = 'DEMO-'.$yearLevel->level_number.'A';

                $section = Section::query()
                    ->where('program_id', $program->id)
                    ->where('year_level', $yearLevel->level_number)
                    ->where('code', $sectionCode)
                    ->firstOrFail();

                $user = User::updateOrCreate(
                    ['email' => $data['email']],
                    [
                        'name' => $data['name'],
                        'password' => 'Demo@2026!',
                        'role' => User::ROLE_STUDENT,
                        'is_active' => $data['active'],
                    ],
                );

                $student = Student::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'student_number' => $data['number'],
                        'program_id' => $program->id,
                        'section_id' => $section->id,
                        'year_level' => $yearLevel->level_number,
                        'status' => $data['active'] ? 'active' : 'inactive',
                    ],
                );

                $demoStudents[] = $student;

                AuditLog::firstOrCreate(
                    [
                        'action' => 'student.created',
                        'target_type' => 'Student',
                        'target_id' => $student->id,
                        'description' => '[DEMO] Presentation sample student record.',
                    ],
                    [
                        'user_id' => $staff->id,
                        'created_at' => Carbon::parse('2026-11-24 09:00:00')->addMinutes($index),
                    ],
                );

                $terms = [];

                if ($data['active']) {
                    $terms[] = ['2026-2027', 'First Semester'];
                }

                if ($data['history']) {
                    $terms[] = ['2025-2026', 'Second Semester'];
                }

                if (in_array($index, [0, 2, 5], true)) {
                    $terms[] = ['2025-2026', 'Midyear'];
                }

                foreach ($terms as $termIndex => [$academicYear, $semester]) {
                    $enrollment = Enrollment::updateOrCreate(
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
                            'description' => '[DEMO] Presentation sample enrollment for '.$student->student_number.'.',
                        ],
                        [
                            'user_id' => $staff->id,
                            'created_at' => Carbon::parse('2026-11-24 10:00:00')
                                ->addMinutes(($index * 3) + $termIndex),
                        ],
                    );
                }
            }

            // Sample visitor time-out history for the first five demo students.
            $timeoutDates = [
                '2026-11-19 15:30:00',
                '2026-11-20 16:05:00',
                '2026-11-21 14:45:00',
                '2026-11-22 17:10:00',
                '2026-11-23 15:55:00',
            ];

            foreach (array_slice($demoStudents, 0, count($timeoutDates)) as $index => $student) {
                $timeout = StudentVisitorTimeout::firstOrCreate([
                    'student_id' => $student->id,
                    'time_out_at' => $timeoutDates[$index],
                ]);

                AuditLog::firstOrCreate(
                    [
                        'action' => 'student.visitor_timeout.recorded',
                        'target_type' => 'StudentVisitorTimeout',
                        'target_id' => $timeout->id,
                        'description' => '[DEMO] Presentation sample Visitor Time-Out record.',
                    ],
                    [
                        'user_id' => $staff->id,
                        'created_at' => Carbon::parse($timeoutDates[$index])->addMinutes(2),
                    ],
                );
            }

            $this->command?->info('Presentation demo data is ready.');
            $this->command?->line('Demo staff login: registrar.demo@sims.test');
            $this->command?->line('Demo student login: alyssa.santos.demo@sims.test');
            $this->command?->line('Demo password for both: Demo@2026!');
            $this->command?->line('Created or updated 11 fictional student profiles, sample sections, enrollments, visitor time-outs, and audit logs.');
        });
    }
}
