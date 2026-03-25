<?php

namespace Database\Seeders;

use App\Models\StudyLevel;
use Illuminate\Database\Seeder;

class StudyLevelSeeder extends Seeder
{
    public function run(): void
    {
        $levels = ['Bac', 'Bac+2', 'Bac+3', 'Bac+4', 'Bac+5', 'Bac+7', 'Bac+8'];

        foreach ($levels as $name) {
            StudyLevel::firstOrCreate(['name' => $name]);
        }
    }
}
