<?php

use Database\Seeders\CountrySeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

return new class extends Migration
{
    /**
     * Rejoue CountrySeeder après son extension à l'ensemble des pays du monde.
     *
     * La migration de seed initiale (000003) a déjà tourné dans les environnements
     * existants ; elle ne se relancera pas. Cette nouvelle migration garantit que
     * les pays ajoutés sont bien insérés partout au prochain `php artisan migrate`.
     * Le seeder est idempotent (firstOrCreate), donc aucun doublon.
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
        // Données de référence : pas de suppression au rollback.
    }
};
