<?php

namespace App\Enums;

enum ActionType: string
{
    case Reception = 'reception';
    case AnnulationReception = 'annulation_reception';
    case Reaffectation = 'reaffectation';

    public function label(): string
    {
        return match ($this) {
            self::Reception => 'Réception',
            self::AnnulationReception => 'Annulation',
            self::Reaffectation => 'Réaffectation',
        };
    }
}
