<?php

namespace Database\Seeders;

use App\Models\Experience;
use Illuminate\Database\Seeder;

class ExperienceSeeder extends Seeder
{
    public function run(): void
    {
        $experiences = ['1 à 2 ans', '3 à 5 ans', '6 à 10 ans', '> 10 ans'];

        foreach ($experiences as $name) {
            Experience::firstOrCreate(['name' => $name]);
        }
    }
}
