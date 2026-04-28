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
                'name' => 'Bronze',
                'description' => 'Plan de base pour les petites entreprises',
                'price' => 49.99,
                'max_offres' => 5,
                'max_articles' => 3,
                'featured_events' => false,
                'priority_support' => false,
                'analytics' => false,
                'is_active' => true,
                'duration_days' => 30,
            ],
            [
                'name' => 'Silver',
                'description' => 'Plan intermédiaire avec plus de fonctionnalités',
                'price' => 99.99,
                'max_offres' => 15,
                'max_articles' => 10,
                'featured_events' => true,
                'priority_support' => false,
                'analytics' => true,
                'is_active' => true,
                'duration_days' => 30,
            ],
            [
                'name' => 'Gold',
                'description' => 'Plan premium avec toutes les fonctionnalités',
                'price' => 199.99,
                'max_offres' => null, // Illimité
                'max_articles' => null, // Illimité
                'featured_events' => true,
                'priority_support' => true,
                'analytics' => true,
                'is_active' => true,
                'duration_days' => 30,
            ],
            [
                'name' => 'Platinum',
                'description' => 'Plan entreprise avec support dédié',
                'price' => 499.99,
                'max_offres' => null,
                'max_articles' => null,
                'featured_events' => true,
                'priority_support' => true,
                'analytics' => true,
                'is_active' => true,
                'duration_days' => 30,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::create($plan);
        }
    }
}
