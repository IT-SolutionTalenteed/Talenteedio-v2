<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('evenements', function (Blueprint $table) {
            $table->id();
            $table->string('titre');
            $table->string('image_mise_en_avant')->nullable();
            $table->text('description')->nullable();
            $table->text('details_supplementaires')->nullable();
            $table->date('date_debut');
            $table->date('date_fin');
            $table->time('heure_debut_journee');
            $table->time('heure_fin_journee');
            $table->foreignId('categorie_evenement_id')->nullable()->constrained('categorie_evenements')->onDelete('set null');
            $table->string('pays')->nullable();
            $table->string('ville')->nullable();
            $table->string('adresse')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
        });

        Schema::create('evenement_entreprise', function (Blueprint $table) {
            $table->foreignId('evenement_id')->constrained('evenements')->onDelete('cascade');
            $table->foreignId('entreprise_id')->constrained('entreprises')->onDelete('cascade');
            $table->primary(['evenement_id', 'entreprise_id']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('evenement_entreprise');
        Schema::dropIfExists('evenements');
    }
};
