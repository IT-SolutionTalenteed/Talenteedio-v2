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
        Schema::table('users', function (Blueprint $table) {
            // Champ status pour le flux d'inscription (pending → active)
            $table->enum('status', ['pending', 'active', 'suspended'])
                  ->default('pending')
                  ->after('role');
            
            // Champs pour TALENTS
            $table->string('cv_path')->nullable()->after('pays');
            $table->text('competences')->nullable()->after('cv_path');
            $table->boolean('matching_completed')->default(false)->after('competences');
            
            // Champs pour ENTREPRISES
            $table->string('entreprise')->nullable()->after('matching_completed');
            $table->string('taille_entreprise')->nullable()->after('entreprise');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'status',
                'cv_path',
                'competences',
                'matching_completed',
                'entreprise',
                'taille_entreprise'
            ]);
        });
    }
};
