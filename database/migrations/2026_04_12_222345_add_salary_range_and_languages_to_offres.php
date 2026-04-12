<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('offres', function (Blueprint $table) {
            $table->decimal('salaire_min', 10, 2)->nullable()->after('salaire');
            $table->decimal('salaire_max', 10, 2)->nullable()->after('salaire_min');
        });

        Schema::create('offre_language', function (Blueprint $table) {
            $table->foreignId('offre_id')->constrained('offres')->onDelete('cascade');
            $table->foreignId('language_id')->constrained()->onDelete('cascade');
            $table->primary(['offre_id', 'language_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offre_language');
        
        Schema::table('offres', function (Blueprint $table) {
            $table->dropColumn(['salaire_min', 'salaire_max']);
        });
    }
};
