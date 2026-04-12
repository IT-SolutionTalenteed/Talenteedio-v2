<?php

namespace Database\Seeders;

use App\Models\ActivitySector;
use App\Models\Article;
use App\Models\Entreprise;
use App\Models\Offre;
use Illuminate\Database\Seeder;

/**
 * Crée l'entreprise système "Solution Talenteed SARL" (secteur RH / Recrutement)
 * et réassigne toutes les offres et articles orphelins (entreprise_id NULL) vers elle.
 *
 * Idempotent — peut être rejoué sans créer de doublon.
 */
class SolutionTalenteedSeeder extends Seeder
{
    public function run(): void
    {
        // Secteur RH — créé s'il n'existe pas
        $secteurRH = ActivitySector::firstOrCreate(
            ['name' => 'Ressources humaines / Recrutement']
        );

        // Entreprise système — sans compte user (entreprise plateforme)
        $entreprise = Entreprise::firstOrCreate(
            ['nom' => 'Solution Talenteed SARL'],
            [
                'user_id'            => null,
                'description'        => 'Entreprise système de la plateforme Talenteed. Regroupe les offres et articles publiés directement sur la plateforme.',
                'activity_sector_id' => $secteurRH->id,
            ]
        );

        // Réassigner les offres orphelines
        $offresCount = Offre::whereNull('entreprise_id')->update(['entreprise_id' => $entreprise->id]);

        // Réassigner les articles orphelins
        $articlesCount = Article::whereNull('entreprise_id')->update(['entreprise_id' => $entreprise->id]);

        $this->command->info("✓ Entreprise \"Solution Talenteed SARL\" ID={$entreprise->id} (secteur: {$secteurRH->name})");
        $this->command->info("✓ {$offresCount} offres orphelines réassignées.");
        $this->command->info("✓ {$articlesCount} articles orphelins réassignés.");
    }
}
