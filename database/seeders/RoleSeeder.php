<?php

namespace Database\Seeders;

use App\Models\SimpleRole;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'admin', 'display_name' => 'Administrateur'],
            ['name' => 'talent', 'display_name' => 'Talent'],
            ['name' => 'entreprise', 'display_name' => 'Entreprise'],
        ];

        foreach ($roles as $role) {
            SimpleRole::firstOrCreate(
                ['name' => $role['name']],
                ['display_name' => $role['display_name']]
            );
        }
    }
}