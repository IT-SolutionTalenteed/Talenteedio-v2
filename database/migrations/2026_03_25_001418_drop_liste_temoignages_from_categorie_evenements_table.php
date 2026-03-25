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
        Schema::table('categorie_evenements', function (Blueprint $table) {
            $table->dropColumn('liste_temoignages');
        });
    }

    public function down(): void
    {
        Schema::table('categorie_evenements', function (Blueprint $table) {
            $table->json('liste_temoignages')->nullable();
        });
    }
};
