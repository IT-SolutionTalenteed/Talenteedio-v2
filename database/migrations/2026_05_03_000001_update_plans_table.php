<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn(['featured_events', 'priority_support', 'analytics']);
            $table->integer('max_evenements')->nullable()->after('max_articles');
            $table->integer('max_entretiens_par_evenement')->nullable()->after('max_evenements');
            $table->integer('max_candidatures_par_offre')->nullable()->after('max_entretiens_par_evenement');
        });
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn(['max_evenements', 'max_entretiens_par_evenement', 'max_candidatures_par_offre']);
            $table->boolean('featured_events')->default(false)->after('max_articles');
            $table->boolean('priority_support')->default(false)->after('featured_events');
            $table->boolean('analytics')->default(false)->after('priority_support');
        });
    }
};
