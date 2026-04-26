<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('auth_provider', ['local', 'google'])
                  ->default('local')
                  ->after('google_id');
            
            // Index pour améliorer les performances
            $table->index(['email', 'auth_provider']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['email', 'auth_provider']);
            $table->dropColumn('auth_provider');
        });
    }
};
