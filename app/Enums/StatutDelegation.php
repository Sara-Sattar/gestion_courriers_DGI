<?php

namespace App\Enums;

enum StatutDelegation: string
{
    case Planifiee = 'planifiee';
    case Active = 'active';
    case Terminee = 'terminee';
    case Annulee = 'annulee';

    public function label(): string
    {
        return match ($this) {
            self::Planifiee => 'Planifiée',
            self::Active => 'Active',
            self::Terminee => 'Terminée',
            self::Annulee => 'Annulée',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Planifiee => 'info',
            self::Active => 'success',
            self::Terminee => 'secondary',
            self::Annulee => 'danger',
        };
    }

    /** Statuts encore « vivants » (interdisent un chevauchement). */
    public static function incompatibles(): array
    {
        return [self::Planifiee, self::Active];
    }

    public function estModifiable(): bool
    {
        return $this === self::Planifiee;
    }

    public function estAnnulable(): bool
    {
        return in_array($this, [self::Planifiee, self::Active], true);
    }
}
