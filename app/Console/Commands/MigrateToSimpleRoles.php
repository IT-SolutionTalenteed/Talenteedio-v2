<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

#[Signature('migrate:simple-roles')]
#[Description('Migrer les rôles Spatie vers le système simple')]
class MigrateToSimpleRoles extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Migration des rôles Spatie vers le système simple...');

        try {
            // Vérifier si les tables Spatie existent encore
            if (!DB::getSchemaBuilder()->hasTable('model_has_roles')) {
                $this->error('Les tables Spatie n\'existent plus. Migration impossible.');
                return 1;
            }

            // Récupérer tous les utilisateurs avec leurs rôles Spatie
            $usersWithRoles = DB::table('model_has_roles')
                ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                ->join('users', 'model_has_roles.model_id', '=', 'users.id')
                ->where('model_has_roles.model_type', User::class)
                ->select('users.id as user_id', 'roles.name as role_name')
                ->get();

            $this->info('Trouvé ' . $usersWithRoles->count() . ' utilisateurs avec des rôles.');

            // Mettre à jour chaque utilisateur
            foreach ($usersWithRoles as $userRole) {
                User::where('id', $userRole->user_id)
                    ->update(['role' => $userRole->role_name]);
                
                $this->line("Utilisateur {$userRole->user_id} -> rôle: {$userRole->role_name}");
            }

            $this->success('Migration terminée avec succès !');
            return 0;

        } catch (\Exception $e) {
            $this->error('Erreur lors de la migration: ' . $e->getMessage());
            return 1;
        }
    }
}
