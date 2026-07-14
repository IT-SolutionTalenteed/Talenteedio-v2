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
        Schema::create('observatory_submissions', function (Blueprint $table) {
            $table->id();
            $table->string('prenom');
            $table->string('email');
            $table->string('pays_residence');
            $table->string('pays_origine');
            $table->string('secteur_activite');
            $table->string('lien_diaspora');
            $table->boolean('consent_data')->default(false);
            $table->boolean('consent_communications')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('observatory_submissions');
    }
};
