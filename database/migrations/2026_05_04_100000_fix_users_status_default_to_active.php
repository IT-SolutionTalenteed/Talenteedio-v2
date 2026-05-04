<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Changer le default de pending → active
        Schema::table('users', function (Blueprint $table) {
            $table->enum('status', ['pending', 'active', 'suspended'])
                  ->default('active')
                  ->change();
        });

        // Mettre à jour les admins et consultants qui seraient bloqués en pending
        DB::table('users')
            ->whereIn('role', ['admin', 'consultant_externe'])
            ->where('status', 'pending')
            ->update(['status' => 'active']);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('status', ['pending', 'active', 'suspended'])
                  ->default('pending')
                  ->change();
        });
    }
};
