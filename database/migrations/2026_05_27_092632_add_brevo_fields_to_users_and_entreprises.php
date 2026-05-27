<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('brevo_id')->nullable()->after('secteur_souhaite_id')->comment('ID contact Brevo');
            $table->timestamp('brevo_synced_at')->nullable()->after('brevo_id');
            $table->text('brevo_sync_error')->nullable()->after('brevo_synced_at');
        });

        Schema::table('entreprises', function (Blueprint $table) {
            $table->unsignedBigInteger('brevo_id')->nullable()->after('hubspot_company_id')->comment('ID contact Brevo');
            $table->timestamp('brevo_synced_at')->nullable()->after('brevo_id');
            $table->text('brevo_sync_error')->nullable()->after('brevo_synced_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['brevo_id', 'brevo_synced_at', 'brevo_sync_error']);
        });

        Schema::table('entreprises', function (Blueprint $table) {
            $table->dropColumn(['brevo_id', 'brevo_synced_at', 'brevo_sync_error']);
        });
    }
};
