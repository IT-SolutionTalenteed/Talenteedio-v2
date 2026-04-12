<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Entreprise;
use App\Models\Offre;
use Illuminate\Database\Seeder;

/**
 * Réassigne toutes les offres et articles orphelins (entreprise_id NULL)
 * vers l'entreprise "Solution Talenteed SARL".
 *
 * Idempotent — peut être rejoué sans créer de doublon.
 */
class SolutionTalenteedSeeder extends Seeder
{
    public function run(): void
    {
        // Trouver l'entreprise "Solution Talenteed SARL"
        $entreprise = Entreprise::where('nom', 'Solution Talenteed SARL')->first();

        if (!$entreprise) {
            $this->command->error("✗ Entreprise \"Solution Talenteed SARL\" introuvable. Veuillez la créer d'abord.");
            return;
        }

        // Réassigner les offres orphelines
        $offresCount = Offre::whereNull('entreprise_id')->update(['entreprise_id' => $entreprise->id]);

        // Réassigner les articles orphelins
        $articlesCount = Article::whereNull('entreprise_id')->update(['entreprise_id' => $entreprise->id]);

        $this->command->info("✓ Entreprise \"Solution Talenteed SARL\" ID={$entreprise->id}");
        $this->command->info("✓ {$offresCount} offres orphelines réassignées.");
        $this->command->info("✓ {$articlesCount} articles orphelins réassignés.");
    }
}
