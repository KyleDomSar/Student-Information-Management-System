<?php

namespace Database\Seeders;

use App\Models\College;
use App\Models\Department;
use App\Models\Program;
use App\Models\ProgramMajor;
use Illuminate\Database\Seeder;

class CvsuMainCampusAcademicStructureSeeder extends Seeder
{
    public function run(): void
    {
        $collegeNames = [
            'College of Agriculture, Food, Environment & Natural Resources',
            'College of Arts and Sciences',
            'College of Criminal Justice',
            'College of Economics, Management & Development Studies',
            'College of Education',
            'College of Tourism and Hospitality Management',
            'College of Engineering & Information Technology',
            'College of Medicine',
            'College of Nursing',
            'College of Sports, Physical Education & Recreation',
            'College of Veterinary Medicine & Biomedical Science',
        ];

        foreach ($collegeNames as $name) {
            College::firstOrCreate(
                ['campus' => 'Main Campus', 'name' => $name],
                ['is_active' => true],
            );
        }

        $ceit = College::where('campus', 'Main Campus')
            ->where('name', 'College of Engineering & Information Technology')
            ->firstOrFail();

        $departments = [
            ['code' => 'DAFE', 'name' => 'Department of Agriculture and Food Engineering'],
            ['code' => 'DCEA', 'name' => 'Department of Civil Engineering'],
            ['code' => 'DCEEE', 'name' => 'Department of Computer, Electronics, and Electrical Engineering'],
            ['code' => 'DIET', 'name' => 'Department of Industrial Engineering and Technology'],
            ['code' => 'DIT', 'name' => 'Department of Information Technology'],
        ];

        foreach ($departments as $department) {
            Department::firstOrCreate(
                ['code' => $department['code']],
                [
                    'college_id' => $ceit->id,
                    'name' => $department['name'],
                    'is_active' => true,
                ],
            );
        }

        // These are SIMS identifiers derived from degree names, not verified registrar-issued codes.
        // Department assignments and year-level mappings are intentionally left for Administrator review.
        $programsByCollege = [
            'College of Agriculture, Food, Environment & Natural Resources' => [
                ['BAE', 'Bachelor in Agricultural Entrepreneurship', ['Animal Production Technology', 'Crop Production Technology']],
                ['BSAGRI', 'Bachelor of Science in Agriculture', ['Animal Science', 'Crop Science']],
                ['BSES', 'Bachelor of Science in Environmental Science', []],
                ['BSFT', 'Bachelor of Science in Food Technology', []],
            ],
            'College of Arts and Sciences' => [
                ['BACOMM', 'Bachelor of Arts in Communication', []],
                ['BAELS', 'Bachelor of Arts in English Language Studies', []],
                ['BAJ', 'Bachelor of Arts in Journalism', []],
                ['BAPS', 'Bachelor of Arts in Political Science', []],
                ['BSAM', 'Bachelor of Science in Applied Mathematics', []],
                ['BSBIO', 'Bachelor of Science in Biology', ['Ecology', 'Medical Biology', 'Genetics', 'Microbiology']],
                ['BSDC', 'Bachelor of Science in Development Communication', []],
                ['BSPsych', 'Bachelor of Science in Psychology', []],
                ['BSSW', 'Bachelor of Science in Social Work', []],
            ],
            'College of Criminal Justice' => [
                ['BSCrim', 'Bachelor of Science in Criminology', []],
                ['BSISM', 'Bachelor of Science in Industrial Security Management', []],
            ],
            'College of Economics, Management & Development Studies' => [
                ['BSACT', 'Bachelor of Science in Accountancy', []],
                ['BSBA', 'Bachelor of Science in Business Administration', ['Financial Management', 'Human Resource Management', 'Marketing Management', 'Operations Management', 'Service Management']],
                ['BSDM', 'Bachelor of Science in Development Management', ['Rural Development Management', 'Training Program Management']],
                ['BSECON', 'Bachelor of Science in Economics', []],
                ['BSIS', 'Bachelor of Science in International Studies', ['Asian Studies', 'European Studies']],
                ['BSOA', 'Bachelor of Science in Office Administration', []],
            ],
            'College of Education' => [
                ['BECEd', 'Bachelor of Early Childhood Education', []],
                ['BEEd', 'Bachelor of Elementary Education', []],
                ['BSEd', 'Bachelor of Secondary Education', ['English', 'Filipino', 'Mathematics', 'Science', 'Social Studies']],
                ['BSNEd', 'Bachelor of Special Needs Education', []],
                ['BTLEd', 'Bachelor of Technology and Livelihood Education', ['Home Economics']],
                ['TCP', 'Teacher Certificate Program', []],
            ],
            'College of Tourism and Hospitality Management' => [
                ['BSHM', 'Bachelor of Science in Hospitality Management', []],
                ['BSTM', 'Bachelor of Science in Tourism Management', []],
            ],
            'College of Engineering & Information Technology' => [
                ['BSABE', 'Bachelor of Science in Agricultural and Biosystems Engineering', []],
                ['BSArch', 'Bachelor of Science in Architecture', []],
                ['BSCE', 'Bachelor of Science in Civil Engineering', ['Construction Engineering and Management', 'Structural Engineering']],
                ['BSCpE', 'Bachelor of Science in Computer Engineering', []],
                ['BSCS', 'Bachelor of Science in Computer Science', []],
                ['BSEE', 'Bachelor of Science in Electrical Engineering', []],
                ['BSECE', 'Bachelor of Science in Electronics and Communication Engineering', []],
                ['BSIE', 'Bachelor of Science in Industrial Engineering', []],
                ['BSIndTech', 'Bachelor of Science in Industrial Technology', ['Automotive Technology', 'Electrical Technology', 'Electronics Technology']],
                ['BSIT', 'Bachelor of Science in Information Technology', []],
            ],
            'College of Medicine' => [
                ['MD', 'Doctor of Medicine', []],
            ],
            'College of Nursing' => [
                ['BSMT', 'Bachelor of Science in Medical Technology', []],
                ['BSMid', 'Bachelor of Science in Midwifery', []],
                ['BSN', 'Bachelor of Science in Nursing', []],
            ],
            'College of Sports, Physical Education & Recreation' => [
                ['BPE', 'Bachelor of Physical Education', []],
                ['BSESS', 'Bachelor of Science in Exercise and Sports Sciences', ['Fitness and Sports Coaching', 'Fitness and Sports Management']],
            ],
            'College of Veterinary Medicine & Biomedical Science' => [
                ['DVM', 'Doctor of Veterinary Medicine', []],
            ],
        ];

        foreach ($programsByCollege as $collegeName => $programs) {
            $college = College::where('campus', 'Main Campus')
                ->where('name', $collegeName)
                ->firstOrFail();

            foreach ($programs as [$code, $name, $majors]) {
                $program = Program::firstOrCreate(
                    ['code' => $code],
                    [
                        'college_id' => $college->id,
                        'name' => $name,
                        'is_active' => true,
                    ],
                );

                foreach ($majors as $majorName) {
                    ProgramMajor::firstOrCreate(
                        ['program_id' => $program->id, 'name' => $majorName],
                        ['is_active' => true],
                    );
                }
            }
        }
    }
}
