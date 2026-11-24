<?php

namespace Database\Seeders;

use App\Models\YearLevel;
use Illuminate\Database\Seeder;

class YearLevelSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            1 => 'First Year',
            2 => 'Second Year',
            3 => 'Third Year',
            4 => 'Fourth Year',
            5 => 'Fifth Year',
            6 => 'Sixth Year',
        ] as $number => $name) {
            YearLevel::updateOrCreate(
                ['level_number' => $number],
                ['name' => $name, 'is_active' => true],
            );
        }
    }
}
