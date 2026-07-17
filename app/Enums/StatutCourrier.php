<?php

namespace App\Enums;

enum StatutCourrier: string
{
    case Depose = 'depose';
    case Receptionne = 'receptionne';
    case Reaffecte = 'reaffecte';
    case Traite = 'traite';

    public function label(): string
    {
        return match ($this) {
            self::Depose => 'Déposé',
            self::Receptionne => 'Réceptionné',
            self::Reaffecte => 'Réaffecté',
            self::Traite => 'Traité',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Depose => 'secondary',
            self::Receptionne => 'primary',
            self::Reaffecte => 'warning',
            self::Traite => 'success',
        };
    }
}
