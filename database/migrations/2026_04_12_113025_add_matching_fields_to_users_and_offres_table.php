<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Champs talent — préférences de mobilité pour le matching
        Schema::table('users', function (Blueprint $table) {
            $table->json('pays_souhaites')->nullable()->after('pays');           // ex: ["France","Canada"] — null = peu importe
            $table->json('villes_souhaitees')->nullable()->after('pays_souhaites'); // ex: ["Paris","Lyon"] — null = peu importe
            $table->foreignId('secteur_souhaite_id')
                  ->nullable()
                  ->constrained('activity_sectors')
                  ->nullOnDelete()
                  ->after('villes_souhaitees');
        });

        // Secteur d'activité directement sur l'offre
        Schema::table('offres', function (Blueprint $table) {
            $table->foreignId('activity_sector_id')
                  ->nullable()
                  ->constrained('activity_sectors')
                  ->nullOnDelete()
                  ->after('entreprise_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['secteur_souhaite_id']);
            $table->dropColumn(['pays_souhaites', 'villes_souhaitees', 'secteur_souhaite_id']);
        });

        Schema::table('offres', function (Blueprint $table) {
            $table->dropForeign(['activity_sector_id']);
            $table->dropColumn('activity_sector_id');
        });
    }
};
