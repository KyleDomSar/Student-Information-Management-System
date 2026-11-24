<?php

namespace Database\\Seeders;

use App\\Models\\AuditLog;
use App\\Models\\Enrollment;
use App\\Models\\Program;
use App\\Models\\Section;
use App\\Models\\Student;
use App\\Models\\StudentVisitorTimeout;
use App\\Models\\User;
use App\\Models\\YearLevel;
use Illuminate\\Database\\Eloquent\\Model;
use Illuminate\\Database\\Seeder;
use Illuminate\\Support\\Carbon;
use Illuminate\\Support\\Facades\\DB;
use RuntimeException;

class PresentationDatabaseSeeder extends Seeder
{
    private const ADMIN_PASSWORD = 'Admin@SIMS2026!';
    private const STAFF_PASSWORD = 'Staff@SIMS2026!';
    private const STUDENT_PASSWORD = 'Student@SIMS2026!';

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

            $admin = $this->seedAdmin();
            $staff = $this->seedStaff($admin);

            $programs = Program::query()->orderBy('code')->get()->keyBy('code');
            $yearLevels = YearLevel::query()->orderBy('level_number')->get()->keyBy('level_number');

            // A sixth-year record is retained as an inactive historical option
            // so the Year Level and Section screens have an inactive example.
            $archivedYear = $yearLevels->get(6);
            if (! $archivedYear) {
                throw new RuntimeException('The default sixth year level was not seeded.');
            }
            $archivedYear->update(['is_active' => false]);

            foreach ($programs as $program) {
                $program->update([
                    'description' => $program->name.' program under the College of Engineering and Information Technology, included as fictional presentation reference data.',
                ]);

                $this->recordAudit(
                    $admin,
                    'program.created',
                    $program,
                    'Presentation academic program record: '.$program->code.' - '.$program->name.'.',
                    Carbon::parse('2026-11-10 08:00:00')->addMinutes($program->id),
                );
            }

            foreach ($yearLevels as $yearLevel) {
                $this->recordAudit(
                    $admin,
                    $yearLevel->is_active ? 'year_level.created' : 'year_level.deactivated',
                    $yearLevel,
                    ($yearLevel->is_active ? 'Active' : 'Archived').' presentation year level: '.$yearLevel->name.'.',
                    Carbon::parse('2026-11-10 09:00:00')->addMinutes($yearLevel->level_number),
                );
            }

            $sections = $this->seedSections($admin, $programs, $yearLevels);
            $students = $this->seedStudents($staff, $programs, $yearLevels, $sections);

            $this->seedEnrollments($staff, $students);
            $this->seedVisitorTimeouts($students);

            $this->command?->info('Full SIMS presentation dataset is ready.');
            $this->command?->line('Accounts: 1 Admin, 10 Staff / Registrar, and 30 Student accounts.');
            $this->command?->line('Academic data: 10 programs, 6 year levels, and 102 sections.');
            $this->command?->line('Student data: 30 profiles, 86 enrollment records, and 33 Visitor Time-Out entries.');
            $this->command?->line('Primary logins: admin.presentation@sims.test, registrar.presentation@sims.test, alyssa.santos.presentation@sims.test');
            $this->command?->line('Passwords: Admin@SIMS2026!, Staff@SIMS2026!, Student@SIMS2026!');
        });
    }

    private function seedAdmin(): User
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin.presentation@sims.test'],
            [
                'name' => 'SIMS Presentation Administrator',
                'password' => self::ADMIN_PASSWORD,
                'role' => User::ROLE_ADMIN,
                'is_active' => true,
            ],
        );

        $this->recordAudit(
            $admin,
            'user.created',
            $admin,
            'Presentation Administrator account initialized.',
            Carbon::parse('2026-11-10 07:45:00'),
        );

        return $admin;
    }

    /**
     * @return array<int, User>
     */
    private function seedStaff(User $admin): array
    {
        $staffAccounts = [
            ['name' => 'Maria Elena Reyes', 'email' => 'registrar.presentation@sims.test', 'active' => true],
            ['name' => 'Roberto Miguel Santos', 'email' => 'roberto.santos.staff@sims.test', 'active' => true],
            ['name' => 'Catherine Anne Cruz', 'email' => 'catherine.cruz.staff@sims.test', 'active' => true],
            ['name' => 'Joseph Daniel Lim', 'email' => 'joseph.lim.staff@sims.test', 'active' => true],
            ['name' => 'Michelle Grace Dela Vega', 'email' => 'michelle.delavega.staff@sims.test', 'active' => true],
            ['name' => 'Ramon Paulo Mendoza', 'email' => 'ramon.mendoza.staff@sims.test', 'active' => true],
            ['name' => 'Patricia Louise Tan', 'email' => 'patricia.tan.staff@sims.test', 'active' => true],
            ['name' => 'Mark Anthony Flores', 'email' => 'mark.flores.staff@sims.test', 'active' => true],
            ['name' => 'Angela Marie Navarro', 'email' => 'angela.navarro.staff@sims.test', 'active' => true],
            ['name' => 'Victor Emmanuel Garcia', 'email' => 'victor.garcia.staff@sims.test', 'active' => false],
        ];

        $staffUsers = [];

        foreach ($staffAccounts as $index => $data) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => self::STAFF_PASSWORD,
                    'role' => User::ROLE_STAFF,
                    'is_active' => $data['active'],
                ],
            );

            $staffUsers[] = $user;

            $this->recordAudit(
                $admin,
                'user.created',
                $user,
                'Presentation Staff / Registrar account initialized: '.$user->name.'.',
                Carbon::parse('2026-11-10 10:00:00')->addMinutes($index),
            );
        }

        return $staffUsers;
    }

    /**
     * @param IlluminateSupportCollection<string, Program> $programs
     * @param IlluminateSupportCollection<int, YearLevel> $yearLevels
     * @return array<string, Section>
     */
    private function seedSections(User $admin, $programs, $yearLevels): array
    {
        $sections = [];

        foreach ($programs as $program) {
            foreach ([1, 2, 3, 4, 5] as $levelNumber) {
                $yearLevel = $yearLevels->get($levelNumber);
                if (! $yearLevel) {
                    continue;
                }

                foreach (['A', 'B'] as $suffix) {
                    $code = $levelNumber.$suffix;
                    $section = Section::updateOrCreate(
                        [
                            'program_id' => $program->id,
                            'year_level' => $levelNumber,
                            'code' => $code,
                        ],
                        ['is_active' => true],
                    );

                    $sections[$program->code.'|'.$levelNumber.'|'.$suffix] = $section;

                    $this->recordAudit(
                        $admin,
                        'section.created',
                        $section,
                        'Presentation section initialized: '.$program->code.' '.$yearLevel->name.' Section '.$code.'.',
                        Carbon::parse('2026-11-11 08:00:00')->addMinutes(count($sections)),
                    );
                }
            }
        }

        $bsit = $programs->get('BSIT');
        $thirdYear = $yearLevels->get(3);
        $sixthYear = $yearLevels->get(6);

        if ($bsit && $thirdYear) {
            $inactiveSection = Section::updateOrCreate(
                [
                    'program_id' => $bsit->id,
                    'year_level' => 3,
                    'code' => '3C',
                ],
                ['is_active' => false],
            );

            $sections['BSIT|3|C'] = $inactiveSection;

            $this->recordAudit(
                $admin,
                'section.deactivated',
                $inactiveSection,
                'Sample inactive section for demonstrating section status filters.',
                Carbon::parse('2026-11-11 10:30:00'),
            );
        }

        if ($bsit && $sixthYear) {
            $archivedSection = Section::updateOrCreate(
                [
                    'program_id' => $bsit->id,
                    'year_level' => 6,
                    'code' => '6A',
                ],
                ['is_active' => true],
            );

            $sections['BSIT|6|A'] = $archivedSection;

            $this->recordAudit(
                $admin,
                'section.created',
                $archivedSection,
                'Sample section under an inactive year level, displayed as effectively inactive.',
                Carbon::parse('2026-11-11 10:35:00'),
            );
        }

        return $sections;
    }

    /**
     * @param IlluminateSupportCollection<string, Program> $programs
     * @param IlluminateSupportCollection<int, YearLevel> $yearLevels
     * @param array<string, Section> $sections
     * @return array<int, Student>
     */
    private function seedStudents(User $staff, $programs, $yearLevels, array $sections): array
    {
        $studentData = [
            ['Alyssa Mae Santos', 'alyssa.santos.presentation@sims.test', 'BSIT', 3, 'A', 'active', true],
            ['Joshua Miguel Reyes', 'joshua.reyes.presentation@sims.test', 'BSIT', 2, 'A', 'active', true],
            ['Nicole Anne Dela Cruz', 'nicole.delacruz.presentation@sims.test', 'BSCS', 1, 'A', 'active', true],
            ['Gabriel Luis Tan', 'gabriel.tan.presentation@sims.test', 'BSCpE', 2, 'A', 'active', true],
            ['Bianca Rose Mendoza', 'bianca.mendoza.presentation@sims.test', 'BSCE', 1, 'B', 'active', true],
            ['Marco Paolo Garcia', 'marco.garcia.presentation@sims.test', 'BSEE', 4, 'A', 'active', true],
            ['Sofia Isabelle Flores', 'sofia.flores.presentation@sims.test', 'BSECE', 3, 'B', 'active', true],
            ['Daniel Miguel Cruz', 'daniel.cruz.presentation@sims.test', 'BSIE', 2, 'B', 'active', true],
            ['Andrea Nicole Villanueva', 'andrea.villanueva.presentation@sims.test', 'BSIndTech', 1, 'A', 'active', true],
            ['Nathaniel James Ramos', 'nathaniel.ramos.presentation@sims.test', 'BSABE', 4, 'B', 'active', true],
            ['Carlo Miguel Bautista', 'carlo.bautista.presentation@sims.test', 'BSArch', 3, 'A', 'active', true],
            ['Patricia Anne Lim', 'patricia.lim.presentation@sims.test', 'BSIT', 4, 'B', 'active', true],
            ['John Patrick Navarro', 'john.navarro.presentation@sims.test', 'BSCS', 2, 'B', 'active', true],
            ['Camille Grace Torres', 'camille.torres.presentation@sims.test', 'BSCpE', 3, 'A', 'active', true],
            ['Ethan Gabriel Rivera', 'ethan.rivera.presentation@sims.test', 'BSCE', 2, 'A', 'active', true],
            ['Isabella Marie Chua', 'isabella.chua.presentation@sims.test', 'BSEE', 1, 'A', 'active', true],
            ['Luis Fernando Castillo', 'luis.castillo.presentation@sims.test', 'BSECE', 4, 'B', 'active', true],
            ['Mikaela Joy Fernandez', 'mikaela.fernandez.presentation@sims.test', 'BSIE', 3, 'B', 'active', true],
            ['Adrian Paolo Mercado', 'adrian.mercado.presentation@sims.test', 'BSIndTech', 2, 'A', 'active', true],
            ['Denise Claire Aquino', 'denise.aquino.presentation@sims.test', 'BSABE', 1, 'B', 'active', true],
            ['Francis Matthew Ong', 'francis.ong.presentation@sims.test', 'BSArch', 2, 'B', 'active', true],
            ['Beatrice Anne Co', 'beatrice.co.presentation@sims.test', 'BSIT', 1, 'A', 'inactive', false],
            ['Sean Vincent Flores', 'sean.flores.presentation@sims.test', 'BSCS', 4, 'B', 'graduated', true],
            ['Katrina Louise Ramos', 'katrina.ramos.presentation@sims.test', 'BSCpE', 1, 'A', 'inactive', false],
            ['Miguel Andres Soriano', 'miguel.soriano.presentation@sims.test', 'BSCE', 3, 'B', 'graduated', true],
            ['Angela Mae Santiago', 'angela.santiago.presentation@sims.test', 'BSEE', 2, 'B', 'active', true],
            ['Rafael Joseph Bautista', 'rafael.bautista.presentation@sims.test', 'BSECE', 1, 'A', 'active', true],
            ['Jasmine Nicole Lim', 'jasmine.lim.presentation@sims.test', 'BSIE', 4, 'A', 'active', true],
            ['Paulo Miguel Herrera', 'paulo.herrera.presentation@sims.test', 'BSIT', 2, 'B', 'active', true],
            ['Chloe Patricia Garcia', 'chloe.garcia.presentation@sims.test', 'BSCS', 3, 'A', 'active', true],
        ];

        $students = [];

        foreach ($studentData as $index => [$name, $email, $programCode, $levelNumber, $suffix, $status, $accountActive]) {
            $program = $programs->get($programCode);
            $yearLevel = $yearLevels->get($levelNumber);
            $section = $sections[$programCode.'|'.$levelNumber.'|'.$suffix] ?? null;

            if (! $program || ! $yearLevel || ! $section) {
                throw new RuntimeException('Missing presentation academic assignment for '.$email.'.');
            }

            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => self::STUDENT_PASSWORD,
                    'role' => User::ROLE_STUDENT,
                    'is_active' => $accountActive,
                ],
            );

            $student = Student::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'student_number' => '2026-CEIT-'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT),
                    'program_id' => $program->id,
                    'section_id' => $section->id,
                    'year_level' => $yearLevel->level_number,
                    'status' => $status,
                ],
            );

            $students[] = $student;

            $this->recordAudit(
                $staff,
                'student.created',
                $student,
                'Presentation student record initialized: '.$student->student_number.' - '.$name.'.',
                Carbon::parse('2026-11-12 08:00:00')->addMinutes($index),
            );
        }

        return $students;
    }

    /**
     * @param array<int, Student> $students
     */
    private function seedEnrollments(User $staff, array $students): void
    {
        foreach ($students as $index => $student) {
            $isCurrentlyActive = $student->status === 'active' && $student->user->is_active;

            $terms = $isCurrentlyActive
                ? [
                    ['2025-2026', 'First Semester', '2025-08-15 09:00:00'],
                    ['2025-2026', 'Second Semester', '2026-01-15 09:00:00'],
                    ['2026-2027', 'First Semester', '2026-08-15 09:00:00'],
                ]
                : [
                    ['2024-2025', 'Second Semester', '2025-01-15 09:00:00'],
                    ['2025-2026', 'Second Semester', '2026-01-15 09:00:00'],
                ];

            foreach ($terms as $termIndex => [$academicYear, $semester, $createdAt]) {
                $timestamp = Carbon::parse($createdAt);

                $enrollment = Enrollment::firstOrCreate(
                    [
                        'student_id' => $student->id,
                        'academic_year' => $academicYear,
                        'semester' => $semester,
                    ],
                    [
                        'created_by' => $staff->id,
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                    ],
                );

                $this->recordAudit(
                    $staff,
                    'enrollment.created',
                    $enrollment,
                    'Presentation enrollment: '.$student->student_number.' for '.$academicYear.' '.$semester.'.',
                    $timestamp->copy()->addMinutes($index + $termIndex),
                );
            }
        }
    }

    /**
     * @param array<int, Student> $students
     */
    private function seedVisitorTimeouts(array $students): void
    {
        foreach (array_slice($students, 0, 16) as $index => $student) {
            $dates = $index === 0
                ? [
                    '2026-11-18 15:30:00',
                    '2026-11-20 16:00:00',
                    '2026-11-24 15:45:00',
                ]
                : [
                    Carbon::parse('2026-10-15 15:30:00')->addDays($index)->toDateTimeString(),
                    Carbon::parse('2026-10-24 16:00:00')->addDays($index)->toDateTimeString(),
                ];

            foreach ($dates as $timeOutAt) {
                $timeout = StudentVisitorTimeout::firstOrCreate(
                    [
                        'student_id' => $student->id,
                        'time_out_at' => $timeOutAt,
                    ],
                    [
                        'created_at' => Carbon::parse($timeOutAt),
                        'updated_at' => Carbon::parse($timeOutAt),
                    ],
                );

                $this->recordAudit(
                    $student->user,
                    'student.visitor_time_out_recorded',
                    $timeout,
                    'Presentation Visitor Time-Out recorded for '.$student->student_number.'.',
                    Carbon::parse($timeOutAt)->addMinutes(2),
                );
            }
        }
    }

    private function recordAudit(
        User $actor,
        string $action,
        Model $target,
        string $description,
        Carbon $createdAt,
    ): void {
        AuditLog::firstOrCreate(
            [
                'action' => $action,
                'target_type' => class_basename($target),
                'target_id' => $target->getKey(),
            ],
            [
                'user_id' => $actor->id,
                'description' => $description,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'SIMS Presentation Seeder',
                'created_at' => $createdAt,
            ],
        );
    }
}
