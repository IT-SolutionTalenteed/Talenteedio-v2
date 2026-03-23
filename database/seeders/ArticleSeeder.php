<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\User;
use App\Models\MediaCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ArticleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer un admin pour créer les articles
        $admin = User::where('role', 'admin')->first();
        
        if (!$admin) {
            $this->command->warn('Aucun admin trouvé. Créez d\'abord un utilisateur admin.');
            return;
        }

        // Récupérer quelques catégories média
        $mediaCategories = MediaCategory::take(3)->get();

        $articles = [
            [
                'title' => 'Introduction aux Technologies Web Modernes',
                'content' => 'Les technologies web évoluent rapidement. Dans cet article, nous explorons les dernières tendances en développement web, incluant les frameworks JavaScript modernes, les architectures serverless et les meilleures pratiques en matière de performance.',
                'slug' => 'introduction-technologies-web-modernes',
                'is_published' => true,
            ],
            [
                'title' => 'Guide Complet du Développement Mobile',
                'content' => 'Le développement mobile offre de nombreuses opportunités. Découvrez les différentes approches : native, hybride et cross-platform. Nous couvrons React Native, Flutter et les considérations importantes pour chaque plateforme.',
                'slug' => 'guide-complet-developpement-mobile',
                'is_published' => true,
            ],
            [
                'title' => 'L\'Intelligence Artificielle dans le Développement',
                'content' => 'L\'IA transforme la façon dont nous développons des applications. Explorez comment intégrer des modèles de machine learning, utiliser des APIs d\'IA et créer des expériences utilisateur intelligentes.',
                'slug' => 'intelligence-artificielle-developpement',
                'is_published' => false,
            ],
            [
                'title' => 'Sécurité des Applications Web',
                'content' => 'La sécurité est cruciale dans le développement web. Apprenez les vulnérabilités communes, les meilleures pratiques de sécurisation et comment implémenter une authentification robuste.',
                'slug' => 'securite-applications-web',
                'is_published' => true,
            ],
        ];

        foreach ($articles as $articleData) {
            $article = Article::create([
                ...$articleData,
                'user_id' => $admin->id,
            ]);

            // Attacher des catégories média aléatoirement
            if ($mediaCategories->isNotEmpty()) {
                $randomCategories = $mediaCategories->random(rand(1, min(2, $mediaCategories->count())));
                $article->mediaCategories()->attach($randomCategories->pluck('id'));
            }
        }

        $this->command->info('Articles créés avec succès !');
    }
}