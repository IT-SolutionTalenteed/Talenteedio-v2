<?php

namespace Database\Seeders;

use App\Models\MediaCategory;
use App\Models\User;
use Illuminate\Database\Seeder;

class MediaCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer un admin par défaut s'il n'existe pas
        $admin = User::firstOrCreate(
            ['email' => 'admin@talenteedio.com'],
            [
                'name' => 'Admin',
                'password' => bcrypt('password'),
                'role' => 'admin'
            ]
        );

        $categories = [
            [
                'name' => 'Vidéo',
                'description' => 'Contenu vidéo incluant films, documentaires, clips',
                'is_active' => true
            ],
            [
                'name' => 'Audio',
                'description' => 'Contenu audio incluant musique, podcasts, livres audio',
                'is_active' => true
            ],
            [
                'name' => 'Image',
                'description' => 'Contenu visuel incluant photos, illustrations, graphiques',
                'is_active' => true
            ],
            [
                'name' => 'Document',
                'description' => 'Documents texte, PDF, présentations',
                'is_active' => true
            ],
            [
                'name' => 'Animation',
                'description' => 'Contenu animé, GIF, animations 2D/3D',
                'is_active' => true
            ]
        ];

        foreach ($categories as $category) {
            MediaCategory::firstOrCreate(
                ['name' => $category['name']],
                [
                    'description' => $category['description'],
                    'is_active' => $category['is_active'],
                    'created_by' => $admin->id
                ]
            );
        }
    }
}
