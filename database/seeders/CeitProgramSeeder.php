<?php

namespace Database\Seeders;

use App\Models\Program;
use Illuminate\Database\Seeder;

class CeitProgramSeeder extends Seeder
{
    public function run(): void
    {
        $programs = [
            ['BSABE', 'Bachelor of Science in Agricultural and Biosystems Engineering'],
            ['BSArch', 'Bachelor of Science in Architecture'],
            ['BSCE', 'Bachelor of Science in Civil Engineering'],
            ['BSCpE', 'Bachelor of Science in Computer Engineering'],
            ['BSCS', 'Bachelor of Science in Computer Science'],
            ['BSEE', 'Bachelor of Science in Electrical Engineering'],
            ['BSECE', 'Bachelor of Science in Electronics and Communication Engineering'],
            ['BSIE', 'Bachelor of Science in Industrial Engineering'],
            ['BSIndTech', 'Bachelor of Science in Industrial Technology'],
            ['BSIT', 'Bachelor of Science in Information Technology'],
        ];

        foreach ($programs as [$code, $name]) {
            Program::updateOrCreate(
                ['code' => $code],
                ['name' => $name, 'is_active' => true],
            );
        }
    }
}
