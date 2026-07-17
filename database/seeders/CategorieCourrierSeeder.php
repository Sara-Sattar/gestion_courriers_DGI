<?php

namespace Database\Seeders;

use App\Models\CategorieCourrier;
use App\Models\TypeCourrier;
use Illuminate\Database\Seeder;

class CategorieCourrierSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Attestation' => [
                'Attestation d\'inscription à la TP',
                'Attestation de régularité fiscale',
            ],
            'Réclamation' => [
                'Réclamation sur avis d\'imposition',
                'Réclamation sur pénalités',
            ],
            'Demande' => [
                'Demande de remise gracieuse',
                'Demande de dégrèvement',
            ],
            'Recours' => [
                'Recours gracieux',
                'Recours hiérarchique',
            ],
        ];

        foreach ($categories as $nomCategorie => $types) {
            $categorie = CategorieCourrier::updateOrCreate(
                ['nom' => $nomCategorie],
                ['actif' => true]
            );

            foreach ($types as $nomType) {
                TypeCourrier::updateOrCreate(
                    [
                        'categorie_courrier_id' => $categorie->id,
                        'nom' => $nomType,
                    ],
                    ['actif' => true]
                );
            }
        }
    }
}
