<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('categorie_evenements', function (Blueprint $table) {
            $table->id();
            $table->string('titre');
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->string('video')->nullable();
            $table->json('galerie')->nullable();
            $table->json('liste_details')->nullable();
            $table->json('liste_temoignages')->nullable();
            $table->json('liste_faqs')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('categorie_evenements'); }
};
