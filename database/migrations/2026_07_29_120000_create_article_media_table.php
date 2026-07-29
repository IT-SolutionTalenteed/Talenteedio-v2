<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Galerie d'images / vidéos supplémentaires attachées à un article.
     * La colonne `articles.image` reste l'image de couverture.
     */
    public function up(): void
    {
        Schema::create('article_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained('articles')->cascadeOnDelete();
            $table->enum('type', ['image', 'video']);
            $table->string('path');
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->index(['article_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('article_media');
    }
};
