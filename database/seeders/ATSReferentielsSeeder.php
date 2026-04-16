<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ActivitySector;
use App\Models\Experience;

class ATSReferentielsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Secteurs d'activité
        $secteurs = [
            'Banque / Finance',
            'Assurance',
            'Technologie / IT',
            'Santé',
            'Industrie',
            'Commerce / Distribution',
            'BTP / Immobilier',
            'Énergie / Mines',
            'Agriculture / Agroalimentaire',
            'Télécommunications',
            'Consulting / Conseil',
            'Éducation / Formation',
            'Transport / Logistique',
            'Autre'
        ];

        foreach ($secteurs as $secteur) {
            ActivitySector::firstOrCreate(['name' => $secteur]);
        }

        $this->command->info('✅ ' . count($secteurs) . ' secteurs d\'activité créés.');

        // Niveaux d'expérience
        $experiences = [
            'Moins d\'1 an',
            '1–3 ans',
            '3–5 ans',
            '5–10 ans',
            '10+ ans'
        ];

        foreach ($experiences as $experience) {
            Experience::firstOrCreate(['name' => $experience]);
        }

        $this->command->info('✅ ' . count($experiences) . ' niveaux d\'expérience créés.');
    }
}
