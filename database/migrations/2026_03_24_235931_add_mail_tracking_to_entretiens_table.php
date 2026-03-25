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
        Schema::table('entretiens', function (Blueprint $table) {
            // M-05 : tracker l'envoi du rappel 1h avant
            $table->boolean('rappel_envoye')->default(false)->after('statut');
            // M-06 : tracker l'envoi de la demande de feedback 30min après
            $table->timestamp('feedback_demande_at')->nullable()->after('rappel_envoye');
        });
    }

    public function down(): void
    {
        Schema::table('entretiens', function (Blueprint $table) {
            $table->dropColumn(['rappel_envoye', 'feedback_demande_at']);
        });
    }
};
