<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('candidatures', function (Blueprint $table) {
            // Modifier la colonne statut pour accepter le nouveau statut
            $table->string('statut')->default('en_attente')->change();
            // Note: Les valeurs possibles sont maintenant:
            // - 'en_attente' : candidature validée automatiquement (score >= 80%)
            // - 'en_attente_validation' : candidature en attente de validation admin (score < 80%)
            // - 'acceptee' : candidature acceptée par l'entreprise
            // - 'refusee' : candidature refusée
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('candidatures', function (Blueprint $table) {
            // Pas de changement à reverser, juste un commentaire sur les valeurs
        });
    }
};
