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
                'name'                         => 'Starter',
                'description'                  => 'Plan de démarrage pour les petites structures',
                'price'                        => 49.99,
                'max_offres'                   => 3,
                'max_articles'                 => 2,
                'max_evenements'               => 1,
                'max_entretiens_par_evenement' => 5,
                'max_candidatures_par_offre'   => 20,
                'is_active'                    => true,
            ],
            [
                'name'                         => 'Business',
                'description'                  => 'Plan intermédiaire pour les entreprises en croissance',
                'price'                        => 99.99,
                'max_offres'                   => 10,
                'max_articles'                 => 5,
                'max_evenements'               => 3,
                'max_entretiens_par_evenement' => 15,
                'max_candidatures_par_offre'   => 50,
                'is_active'                    => true,
            ],
            [
                'name'                         => 'Premium',
                'description'                  => 'Plan complet pour les recruteurs actifs',
                'price'                        => 199.99,
                'max_offres'                   => null,
                'max_articles'                 => null,
                'max_evenements'               => null,
                'max_entretiens_par_evenement' => 30,
                'max_candidatures_par_offre'   => null,
                'is_active'                    => true,
            ],
            [
                'name'                         => 'Enterprise',
                'description'                  => 'Plan illimité pour les grandes entreprises',
                'price'                        => 499.99,
                'max_offres'                   => null,
                'max_articles'                 => null,
                'max_evenements'               => null,
                'max_entretiens_par_evenement' => null,
                'max_candidatures_par_offre'   => null,
                'is_active'                    => true,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::create($plan);
        }
    }
}
