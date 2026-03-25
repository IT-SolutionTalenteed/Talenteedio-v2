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
        Schema::create('categorie_evenement_temoignage', function (Blueprint $table) {
            $table->foreignId('categorie_evenement_id')->constrained('categorie_evenements')->onDelete('cascade');
            $table->foreignId('temoignage_id')->constrained('temoignages')->onDelete('cascade');
            $table->primary(['categorie_evenement_id', 'temoignage_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categorie_evenement_temoignage');
    }
};
