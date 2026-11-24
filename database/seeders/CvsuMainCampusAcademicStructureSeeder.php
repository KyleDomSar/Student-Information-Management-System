<?php

namespace Database\Seeders;

use App\Models\College;
use App\Models\Department;
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
            ->first();

        if (! $ceit) {
            return;
        }

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
    }
}
