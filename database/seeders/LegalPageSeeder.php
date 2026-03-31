<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LegalPage;

class LegalPageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pages = [
            [
                'title' => 'Conditions générales',
                'type'  => 'terms',
                'description' => '<p>Contenu à remplir...</p>'
            ],
            [
                'title' => 'Confidentialité',
                'type'  => 'privacy',
                'description' => '<p>Contenu à remplir...</p>'
            ]
        ];

        foreach ($pages as $page) {
            LegalPage::firstOrCreate(
                ['type' => $page['type']],
                ['title' => $page['title'], 'description' => $page['description']]
            );
        }
    }
}
