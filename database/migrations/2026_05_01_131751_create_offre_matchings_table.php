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
        Schema::create('offre_matchings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('talent_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('offre_id')->constrained('offres')->onDelete('cascade');
            $table->string('cv_path')->nullable(); // Chemin du CV utilisé
            $table->integer('score'); // Score global 0-100
            $table->text('raison'); // Raison du score
            $table->json('details')->nullable(); // Détails par catégorie
            $table->timestamps();
            
            // Index pour recherche rapide
            $table->unique(['talent_id', 'offre_id']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offre_matchings');
    }
};
