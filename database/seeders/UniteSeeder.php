<?php

namespace Database\Seeders;

use App\Models\Unite;
use Illuminate\Database\Seeder;

class UniteSeeder extends Seeder
{
    public function run(): void
    {
        $dr = Unite::updateOrCreate(
            ['code' => 'DR001'],
            [
                'nom' => 'Direction Régionale des Impôts de Marrakech',
                'type_unite' => 'DR',
                'parent_id' => null,
                'description' => 'Direction Régionale racine',
                'actif' => true,
            ]
        );

        $bureauOrdre = Unite::updateOrCreate(
            ['code' => 'BO001'],
            [
                'nom' => 'Bureau d\'ordre',
                'type_unite' => 'Bureau',
                'parent_id' => $dr->id,
                'description' => 'Bureau d\'ordre central',
                'ville' => 'Marrakech',
                'est_bureau_ordre' => true,
                'actif' => true,
            ]
        );

        $serviceIt = Unite::updateOrCreate(
            ['code' => 'SERV002'],
            [
                'nom' => 'Service IT',
                'type_unite' => 'Service',
                'parent_id' => $dr->id,
                'description' => 'Service informatique',
                'actif' => true,
            ]
        );

        Unite::updateOrCreate(
            ['code' => 'BRIG001'],
            [
                'nom' => 'Brigade de vérification',
                'type_unite' => 'Brigade',
                'parent_id' => $dr->id,
                'description' => null,
                'actif' => true,
            ]
        );

        Unite::updateOrCreate(
            ['code' => 'SECT001'],
            [
                'nom' => 'Secteur fiscal Guéliz',
                'type_unite' => 'Secteur',
                'parent_id' => $serviceIt->id,
                'description' => null,
                'actif' => true,
            ]
        );

        Unite::updateOrCreate(
            ['code' => 'BUR003'],
            [
                'nom' => 'Bureau de recouvrement',
                'type_unite' => 'Bureau',
                'parent_id' => $bureauOrdre->id,
                'description' => null,
                'actif' => true,
            ]
        );
    }
}
