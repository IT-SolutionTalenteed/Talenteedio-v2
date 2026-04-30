<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CommercialAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Crée un compte admin commercial pour le site
     */
    public function run(): void
    {
        // Créer l'utilisateur admin commercial s'il n'existe pas déjà
        $commercial = User::firstOrCreate(
            ['email' => 'commercial@talenteed.io'],
            [
                'name' => 'Commercial Admin',
                'first_name' => 'Commercial',
                'last_name' => 'Admin',
                'email' => 'commercial@talenteed.io',
                'password' => Hash::make('Commercial2024!'),
                'role' => 'admin',
                'email_verified_at' => now(),
                'telephone' => '+33 1 23 45 67 89',
                'is_suspended' => false,
                'is_banned' => false,
            ]
        );

        if ($commercial->wasRecentlyCreated) {
            $this->command->info('✓ Compte admin commercial créé avec succès !');
        } else {
            $this->command->info('ℹ Le compte admin commercial existe déjà.');
        }

        $this->command->info('');
        $this->command->info('=== INFORMATIONS DE CONNEXION ===');
        $this->command->info('Email: commercial@talenteed.io');
        $this->command->info('Mot de passe: Commercial2024!');
        $this->command->info('Rôle: admin');
        $this->command->info('==================================');
    }
}
