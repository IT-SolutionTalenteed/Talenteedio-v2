<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offres', function (Blueprint $table) {
            $table->id();
            $table->string('titre');
            $table->text('mission')->nullable();
            $table->string('client')->nullable();
            $table->text('profil_recherche')->nullable();
            $table->text('a_propos')->nullable();
            $table->text('liste_offre')->nullable();
            $table->text('description')->nullable();
            $table->date('date_mise_en_ligne')->nullable();
            $table->date('date_limite')->nullable();
            $table->decimal('salaire', 10, 2)->nullable();
            $table->string('fourchette_salariale')->nullable();
            $table->string('localisation')->nullable();
            $table->integer('nombre_candidatures')->default(0);
            $table->timestamps();
        });

        Schema::create('offre_job_contract', function (Blueprint $table) {
            $table->foreignId('offre_id')->constrained('offres')->onDelete('cascade');
            $table->foreignId('job_contract_id')->constrained()->onDelete('cascade');
            $table->primary(['offre_id', 'job_contract_id']);
        });

        Schema::create('offre_job_mode', function (Blueprint $table) {
            $table->foreignId('offre_id')->constrained('offres')->onDelete('cascade');
            $table->foreignId('job_mode_id')->constrained()->onDelete('cascade');
            $table->primary(['offre_id', 'job_mode_id']);
        });

        Schema::create('offre_skill', function (Blueprint $table) {
            $table->foreignId('offre_id')->constrained('offres')->onDelete('cascade');
            $table->foreignId('skill_id')->constrained()->onDelete('cascade');
            $table->primary(['offre_id', 'skill_id']);
        });

        Schema::create('offre_study_level', function (Blueprint $table) {
            $table->foreignId('offre_id')->constrained('offres')->onDelete('cascade');
            $table->foreignId('study_level_id')->constrained()->onDelete('cascade');
            $table->primary(['offre_id', 'study_level_id']);
        });

        Schema::create('offre_experience', function (Blueprint $table) {
            $table->foreignId('offre_id')->constrained('offres')->onDelete('cascade');
            $table->foreignId('experience_id')->constrained()->onDelete('cascade');
            $table->primary(['offre_id', 'experience_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offre_experience');
        Schema::dropIfExists('offre_study_level');
        Schema::dropIfExists('offre_skill');
        Schema::dropIfExists('offre_job_mode');
        Schema::dropIfExists('offre_job_contract');
        Schema::dropIfExists('offres');
    }
};
