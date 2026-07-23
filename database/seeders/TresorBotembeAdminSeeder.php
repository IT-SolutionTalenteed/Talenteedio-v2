<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class TresorBotembeAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Crée un compte admin pour Trésor BOTEMBE.
     */
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'communication@solutiontalenteed.com'],
            [
                'name' => 'Trésor BOTEMBE',
                'first_name' => 'Trésor',
                'last_name' => 'BOTEMBE',
                'email' => 'communication@solutiontalenteed.com',
                'password' => Hash::make('AfricaSummit+5Nov!'),
                'role' => 'admin',
                'email_verified_at' => now(),
                'is_suspended' => false,
                'is_banned' => false,
            ]
        );

        if ($admin->wasRecentlyCreated) {
            $this->command->info('✓ Compte admin Trésor BOTEMBE créé avec succès !');
        } else {
            $this->command->info('ℹ Le compte admin Trésor BOTEMBE existe déjà.');
        }

        $this->command->info('');
        $this->command->info('=== INFORMATIONS DE CONNEXION ===');
        $this->command->info('Nom: Trésor BOTEMBE');
        $this->command->info('Email: communication@solutiontalenteed.com');
        $this->command->info('Mot de passe: AfricaSummit+5Nov!');
        $this->command->info('Rôle: admin');
        $this->command->info('==================================');
    }
}
