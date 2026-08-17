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
        Schema::table('legal_pages', function (Blueprint $table) {
            // L'index unique posé avec la colonne doit partir en premier : SQLite
            // refuse de supprimer une colonne encore référencée par un index.
            $table->dropUnique('legal_pages_type_unique');
            $table->dropColumn('type');
        });
    }

    public function down(): void
    {
        Schema::table('legal_pages', function (Blueprint $table) {
            $table->string('type')->nullable()->after('slug');
        });
    }
};
