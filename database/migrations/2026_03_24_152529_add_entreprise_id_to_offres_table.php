<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('offres', 'entreprise_id')) {
            Schema::table('offres', function (Blueprint $table) {
                $table->foreignId('entreprise_id')->nullable()->constrained('entreprises')->nullOnDelete()->after('id');
            });
        }
    }

    public function down(): void
    {
        Schema::table('offres', function (Blueprint $table) {
            $table->dropForeign(['entreprise_id']);
            $table->dropColumn('entreprise_id');
        });
    }
};
