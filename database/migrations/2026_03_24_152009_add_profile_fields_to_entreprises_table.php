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
        Schema::table('entreprises', function (Blueprint $table) {
            $table->string('logo')->nullable()->after('nom');
            $table->text('description')->nullable()->after('logo');
            $table->string('site_web')->nullable()->after('description');
            $table->string('telephone')->nullable()->after('site_web');
            $table->string('adresse')->nullable()->after('telephone');
            $table->string('ville')->nullable()->after('adresse');
            $table->string('pays')->nullable()->after('ville');
            $table->foreignId('activity_sector_id')->nullable()->constrained('activity_sectors')->nullOnDelete()->after('pays');
        });
    }

    public function down(): void
    {
        Schema::table('entreprises', function (Blueprint $table) {
            $table->dropForeign(['activity_sector_id']);
            $table->dropColumn(['logo', 'description', 'site_web', 'telephone', 'adresse', 'ville', 'pays', 'activity_sector_id']);
        });
    }
};
