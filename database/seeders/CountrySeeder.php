<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        // Pays de résidence (Europe)
        $europe = [
            'Allemagne', 'Autriche', 'Belgique', 'Danemark', 'Espagne', 'Finlande',
            'France', 'Irlande', 'Italie', 'Luxembourg', 'Norvège', 'Pays-Bas',
            'Portugal', 'Royaume-Uni', 'Suède', 'Suisse',
        ];

        // Pays d'origine ou d'attachement (Afrique)
        $afrique = [
            'Afrique du Sud', 'Algérie', 'Angola', 'Bénin', 'Burkina Faso', 'Burundi',
            'Cameroun', 'Cap-Vert', 'Congo (Brazzaville)', 'Congo (RDC)', "Côte d'Ivoire",
            'Égypte', 'Éthiopie', 'Gabon', 'Ghana', 'Guinée', 'Kenya', 'Madagascar',
            'Mali', 'Maroc', 'Maurice', 'Mauritanie', 'Niger', 'Nigéria', 'Ouganda',
            'Rwanda', 'Sénégal', 'Somalie', 'Soudan', 'Tanzanie', 'Tchad', 'Togo',
            'Tunisie', 'Zambie', 'Zimbabwe',
        ];

        foreach ($europe as $name) {
            Country::firstOrCreate(['name' => $name], ['region' => 'europe']);
        }

        foreach ($afrique as $name) {
            Country::firstOrCreate(['name' => $name], ['region' => 'afrique']);
        }
    }
}
