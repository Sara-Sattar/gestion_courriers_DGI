<?php

namespace App\Enums;

enum ActionHistorique: string
{
    case Depot = 'depot';
    case Reception = 'reception';
    case AnnulationReception = 'annulation_reception';

    public function label(): string
    {
        return match ($this) {
            self::Depot => 'Dépôt',
            self::Reception => 'Réception',
            self::AnnulationReception => 'Annulation de réception',
        };
    }
}
