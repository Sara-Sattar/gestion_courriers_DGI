<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Ancien profil « Direction Régionale » → « Accueil » (guichet d'enregistrement).
        Role::query()
            ->where('name', 'Direction Régionale')
            ->update([
                'name' => 'Accueil',
                'description' => 'Guichet d\'accueil — enregistrement et orientation des courriers',
            ]);

        $roles = [
            ['name' => 'Admin', 'description' => 'Gère les utilisateurs, rôles, catégories, types de courrier, unités administratives et paramètres'],
            ['name' => 'Accueil', 'description' => 'Guichet d\'accueil — enregistrement et orientation des courriers'],
            ['name' => 'Traitement', 'description' => 'Profil CDC — traitement des courriers'],
            ['name' => 'Consultation', 'description' => 'Profil CDC — consultation et statistiques'],
            ['name' => 'Service', 'description' => 'Gère un service'],
            ['name' => 'Brigade', 'description' => 'Gère une brigade'],
            ['name' => 'Bureau', 'description' => 'Gère un bureau'],
            ['name' => 'Section', 'description' => 'Gère une section'],
            ['name' => 'Secteur', 'description' => 'Gère un secteur'],
        ];

        foreach ($roles as $role) {
            Role::query()->updateOrCreate(
                ['name' => $role['name']],
                ['description' => $role['description']]
            );
        }
    }
}
