<?php

namespace Database\Seeders;

use App\Models\ActivitySector;
use App\Models\Entreprise;
use App\Models\Offre;
use Illuminate\Database\Seeder;

/**
 * Crée l'entreprise système "Solution Talenteed" (secteur RH / Recrutement)
 * et réassigne toutes les offres orphelines (entreprise_id NULL) vers elle.
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
            ['nom' => 'Solution Talenteed'],
            [
                'user_id'            => null,
                'description'        => 'Entreprise système de la plateforme Talenteed. Regroupe les offres publiées directement sur la plateforme.',
                'activity_sector_id' => $secteurRH->id,
            ]
        );

        // Réassigner les offres orphelines
        $count = Offre::whereNull('entreprise_id')->update(['entreprise_id' => $entreprise->id]);

        $this->command->info("✓ Entreprise \"Solution Talenteed\" ID={$entreprise->id} (secteur: {$secteurRH->name})");
        $this->command->info("✓ {$count} offres orphelines réassignées.");
    }
}
