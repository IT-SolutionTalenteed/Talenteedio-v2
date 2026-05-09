<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Plan;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Gratuit',
                'description' => 'Plan gratuit avec fonctionnalités de base',
                'price' => 0,
                'max_offres' => 3,
                'max_articles' => 5,
                'max_evenements' => 1,
                'max_entretiens_par_evenement' => 10,
                'max_candidatures_par_offre' => 50,
                'is_active' => true,
            ],
            [
                'name' => 'Standard',
                'description' => 'Plan standard pour les petites entreprises',
                'price' => 49.99,
                'max_offres' => 10,
                'max_articles' => 20,
                'max_evenements' => 5,
                'max_entretiens_par_evenement' => 50,
                'max_candidatures_par_offre' => 200,
                'is_active' => true,
            ],
            [
                'name' => 'Premium',
                'description' => 'Plan premium avec toutes les fonctionnalités',
                'price' => 99.99,
                'max_offres' => null, // Illimité
                'max_articles' => null, // Illimité
                'max_evenements' => null, // Illimité
                'max_entretiens_par_evenement' => null, // Illimité
                'max_candidatures_par_offre' => null, // Illimité
                'is_active' => true,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::firstOrCreate(
                ['name' => $plan['name']],
                $plan
            );
        }
    }
}
