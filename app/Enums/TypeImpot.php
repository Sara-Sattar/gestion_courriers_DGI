<?php

namespace App\Enums;

enum TypeImpot: string
{
    case IR = 'IR';
    case IS = 'IS';
    case TVA = 'TVA';
    case TP = 'TP';

    public function label(): string
    {
        return $this->value;
    }
}
