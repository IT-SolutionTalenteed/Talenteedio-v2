<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('entreprises', function (Blueprint $table) {
            $table->string('status')->default('active')->after('nom');
            $table->string('taille')->nullable()->after('status');
            $table->string('poste_contact')->nullable()->after('taille');
        });
    }

    public function down(): void
    {
        Schema::table('entreprises', function (Blueprint $table) {
            $table->dropColumn(['status', 'taille', 'poste_contact']);
        });
    }
};
