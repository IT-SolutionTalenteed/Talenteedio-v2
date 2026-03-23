<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer l'utilisateur admin s'il n'existe pas déjà
        User::firstOrCreate(
            ['email' => 'solofonirina35@gmail.com'],
            [
                'name' => 'Admin User',
                'email' => 'solofonirina35@gmail.com',
                'password' => Hash::make('STDlux06@'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Utilisateur admin créé avec succès !');
        $this->command->info('Email: solofonirina35@gmail.com');
        $this->command->info('Mot de passe: STDlux06@');
        $this->command->info('Rôle: admin');
    }
}