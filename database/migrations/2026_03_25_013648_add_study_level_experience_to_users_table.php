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
            $table->foreignId('study_level_id')->nullable()->after('ref_ancien_crm')->constrained('study_levels')->nullOnDelete();
            $table->foreignId('experience_id')->nullable()->after('study_level_id')->constrained('experiences')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['study_level_id']);
            $table->dropForeign(['experience_id']);
            $table->dropColumn(['study_level_id', 'experience_id']);
        });
    }
};
