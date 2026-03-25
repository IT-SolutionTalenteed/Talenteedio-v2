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
            // H-02 — Profil talent étendu (champs XLS)
            $table->string('civilite')->nullable()->after('name');
            $table->string('titre_poste')->nullable()->after('civilite');
            $table->string('telephone')->nullable()->after('titre_poste');
            $table->date('date_naissance')->nullable()->after('telephone');
            $table->string('nationalite')->nullable()->after('date_naissance');
            $table->string('ville')->nullable()->after('nationalite');
            $table->string('pays')->nullable()->after('ville');
            $table->string('disponibilite')->nullable()->after('pays');
            $table->string('mobilite')->nullable()->after('disponibilite');
            $table->string('source_provenance')->nullable()->after('mobilite'); // LinkedIn, ADEM, Cooptation, Jobboard, France Travail, Salon, Talenteed, Autre
            $table->string('ref_ancien_crm')->nullable()->after('source_provenance');

            // H-03 — Statut CRM talent
            $table->string('statut_crm')->nullable()->after('is_banned'); // a_traiter, en_cours_qualif, vivier, top_profil, converti_ressource, recrute_client, ne_plus_contacter
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'civilite', 'titre_poste', 'telephone', 'date_naissance',
                'nationalite', 'ville', 'pays', 'disponibilite', 'mobilite',
                'source_provenance', 'ref_ancien_crm', 'statut_crm',
            ]);
        });
    }
};
