<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    /**
     * Peuple la table countries avec l'ensemble des pays du monde.
     * `region` = continent (utile pour un éventuel filtrage/regroupement en admin).
     * Idempotent : firstOrCreate sur le nom, sûr à rejouer.
     */
    public function run(): void
    {
        $byRegion = [
            'europe' => [
                'Albanie', 'Allemagne', 'Andorre', 'Autriche', 'Belgique', 'Biélorussie',
                'Bosnie-Herzégovine', 'Bulgarie', 'Chypre', 'Croatie', 'Danemark', 'Espagne',
                'Estonie', 'Finlande', 'France', 'Grèce', 'Hongrie', 'Irlande', 'Islande',
                'Italie', 'Kosovo', 'Lettonie', 'Liechtenstein', 'Lituanie', 'Luxembourg',
                'Macédoine du Nord', 'Malte', 'Moldavie', 'Monaco', 'Monténégro', 'Norvège',
                'Pays-Bas', 'Pologne', 'Portugal', 'République tchèque', 'Roumanie',
                'Royaume-Uni', 'Russie', 'Saint-Marin', 'Serbie', 'Slovaquie', 'Slovénie',
                'Suède', 'Suisse', 'Ukraine', 'Vatican',
            ],
            'afrique' => [
                'Afrique du Sud', 'Algérie', 'Angola', 'Bénin', 'Botswana', 'Burkina Faso',
                'Burundi', 'Cameroun', 'Cap-Vert', 'Comores', 'Congo (Brazzaville)',
                'Congo (RDC)', "Côte d'Ivoire", 'Djibouti', 'Égypte', 'Érythrée', 'Eswatini',
                'Éthiopie', 'Gabon', 'Gambie', 'Ghana', 'Guinée', 'Guinée équatoriale',
                'Guinée-Bissau', 'Kenya', 'Lesotho', 'Liberia', 'Libye', 'Madagascar',
                'Malawi', 'Mali', 'Maroc', 'Maurice', 'Mauritanie', 'Mozambique', 'Namibie',
                'Niger', 'Nigéria', 'Ouganda', 'République centrafricaine', 'Rwanda',
                'Sao Tomé-et-Principe', 'Sénégal', 'Seychelles', 'Sierra Leone', 'Somalie',
                'Soudan', 'Soudan du Sud', 'Tanzanie', 'Tchad', 'Togo', 'Tunisie', 'Zambie',
                'Zimbabwe',
            ],
            'amerique' => [
                'Antigua-et-Barbuda', 'Argentine', 'Bahamas', 'Barbade', 'Belize', 'Bolivie',
                'Brésil', 'Canada', 'Chili', 'Colombie', 'Costa Rica', 'Cuba', 'Dominique',
                'El Salvador', 'Équateur', 'États-Unis', 'Grenade', 'Guatemala', 'Guyana',
                'Haïti', 'Honduras', 'Jamaïque', 'Mexique', 'Nicaragua', 'Panama', 'Paraguay',
                'Pérou', 'République dominicaine', 'Saint-Christophe-et-Niévès',
                'Saint-Vincent-et-les-Grenadines', 'Sainte-Lucie', 'Suriname',
                'Trinité-et-Tobago', 'Uruguay', 'Venezuela',
            ],
            'asie' => [
                'Afghanistan', 'Arabie saoudite', 'Arménie', 'Azerbaïdjan', 'Bahreïn',
                'Bangladesh', 'Bhoutan', 'Birmanie (Myanmar)', 'Brunei', 'Cambodge', 'Chine',
                'Corée du Nord', 'Corée du Sud', 'Émirats arabes unis', 'Géorgie', 'Inde',
                'Indonésie', 'Irak', 'Iran', 'Israël', 'Japon', 'Jordanie', 'Kazakhstan',
                'Kirghizistan', 'Koweït', 'Laos', 'Liban', 'Malaisie', 'Maldives', 'Mongolie',
                'Népal', 'Oman', 'Ouzbékistan', 'Pakistan', 'Palestine', 'Philippines',
                'Qatar', 'Singapour', 'Sri Lanka', 'Syrie', 'Tadjikistan', 'Taïwan',
                'Thaïlande', 'Timor oriental', 'Turkménistan', 'Turquie', 'Viêt Nam', 'Yémen',
            ],
            'oceanie' => [
                'Australie', 'Fidji', 'Îles Marshall', 'Îles Salomon', 'Kiribati',
                'Micronésie', 'Nauru', 'Nouvelle-Zélande', 'Palaos',
                'Papouasie-Nouvelle-Guinée', 'Samoa', 'Tonga', 'Tuvalu', 'Vanuatu',
            ],
        ];

        foreach ($byRegion as $region => $names) {
            foreach ($names as $name) {
                Country::firstOrCreate(['name' => $name], ['region' => $region]);
            }
        }
    }
}
