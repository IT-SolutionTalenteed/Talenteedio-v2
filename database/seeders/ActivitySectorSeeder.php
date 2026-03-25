<?php

namespace Database\Seeders;

use App\Models\ActivitySector;
use Illuminate\Database\Seeder;

class ActivitySectorSeeder extends Seeder
{
    public function run(): void
    {
        // Secteurs extraits du fichier candidats.xls (encodage corrigé)
        $sectors = [
            'Aéronautique',
            'Aérospatial',
            'Assurance',
            'Automobile',
            'Autres',
            'Banque',
            'Bâtiments',
            'Conseil',
            'Défense',
            'Édition de logiciels',
            'Energie',
            'Environnement',
            'Grande distribution',
            'Infrastructure',
            'Logistique',
            'Pharmacie',
            'Secteur public',
            'Services',
            'Télécommunications',
        ];

        foreach ($sectors as $name) {
            ActivitySector::firstOrCreate(['name' => $name]);
        }
    }
}
