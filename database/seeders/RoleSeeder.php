<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'Admin', 'description' => 'Gère les utilisateurs, rôles, catégories, types de courrier, unités administratives et paramètres'],
            ['name' => 'Direction Régionale', 'description' => 'Gère une direction régionale'],
            ['name' => 'Service', 'description' => 'Gère un service'],
            ['name' => 'Brigade', 'description' => 'Gère une brigade'],
            ['name' => 'Bureau', 'description' => 'Gère un bureau'],
            ['name' => 'Section', 'description' => 'Gère une section'],
            ['name' => 'Secteur', 'description' => 'Gère un secteur'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate($role);
        }
    }
}
