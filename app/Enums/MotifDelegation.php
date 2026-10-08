<?php

namespace App\Enums;

enum MotifDelegation: string
{
    case Conge = 'conge';
    case Maladie = 'maladie';
    case Mission = 'mission';
    case Formation = 'formation';
    case Autre = 'autre';

    public function label(): string
    {
        return match ($this) {
            self::Conge => 'Congé',
            self::Maladie => 'Maladie',
            self::Mission => 'Mission',
            self::Formation => 'Formation',
            self::Autre => 'Autre',
        };
    }
}
