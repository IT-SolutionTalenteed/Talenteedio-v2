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
        Schema::create('evenement_demandes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entreprise_id')->constrained('entreprises')->onDelete('cascade');
            $table->foreignId('evenement_id')->constrained('evenements')->onDelete('cascade');
            $table->string('statut')->default('en_attente'); // en_attente, acceptee, refusee
            $table->text('message')->nullable();
            $table->timestamps();
            $table->unique(['entreprise_id', 'evenement_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evenement_demandes');
    }
};
