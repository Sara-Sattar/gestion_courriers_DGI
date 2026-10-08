<?php

namespace App\Enums;

enum ModeLivraison: string
{
    case Interesse = 'interesse';
    case Mandataire = 'mandataire';
    case EnvoiPostal = 'envoi_postal';

    public function label(): string
    {
        return match ($this) {
            self::Interesse => 'Livré à l\'intéressé',
            self::Mandataire => 'Mandataire',
            self::EnvoiPostal => 'Envoi postal',
        };
    }
}
