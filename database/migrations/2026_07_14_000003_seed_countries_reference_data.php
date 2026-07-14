<?php

use Database\Seeders\CountrySeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

return new class extends Migration
{
    /**
     * Peuple la table countries avec les pays de référence.
     *
     * On passe par une migration (et non par le déploiement) pour garantir
     * que la donnée existe dans TOUS les environnements : `php artisan migrate`
     * tourne à chaque déploiement, contrairement au seed qui n'est pas lancé.
     * Le seeder est idempotent (firstOrCreate), donc sûr à rejouer.
     */
    public function up(): void
    {
        Artisan::call('db:seed', [
            '--class' => CountrySeeder::class,
            '--force' => true,
        ]);
    }

    public function down(): void
    {
        // On ne supprime pas les pays au rollback : ce sont des données de référence
        // potentiellement déjà utilisées par des soumissions.
    }
};
