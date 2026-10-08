<?php

namespace App\Enums;

enum ResultatTraitement: string
{
    case Favorable = 'favorable';
    case Rejet = 'rejet';
    case Suspendu = 'suspendu';

    public function label(): string
    {
        return match ($this) {
            self::Favorable => 'Favorable',
            self::Rejet => 'Rejet',
            self::Suspendu => 'Suspendu (En cours de traitement)',
        };
    }

    public function statutCible(): StatutCourrier
    {
        return match ($this) {
            self::Favorable => StatutCourrier::TraiteFavorable,
            self::Rejet => StatutCourrier::Rejete,
            self::Suspendu => StatutCourrier::Suspendu,
        };
    }
}
