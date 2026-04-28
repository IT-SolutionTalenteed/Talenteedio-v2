<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Ex: Gold, Silver, Bronze, Premium
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->default(0); // Prix mensuel
            $table->integer('max_offres')->nullable(); // Nombre max d'offres
            $table->integer('max_articles')->nullable(); // Nombre max d'articles
            $table->boolean('featured_events')->default(false); // Accès événements premium
            $table->boolean('priority_support')->default(false); // Support prioritaire
            $table->boolean('analytics')->default(false); // Accès analytics avancés
            $table->boolean('is_active')->default(true);
            $table->integer('duration_days')->default(30); // Durée en jours
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
