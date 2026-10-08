<?php

namespace App\Enums;

enum StatutCourrier: string
{
    case Depose = 'depose';
    case Receptionne = 'receptionne';
    case Reaffecte = 'reaffecte';
    /** @deprecated Conservé pour les données historiques ; préférer TraiteFavorable. */
    case Traite = 'traite';
    case TraiteFavorable = 'traite_favorable';
    case Rejete = 'rejete';
    case Suspendu = 'suspendu';
    case Cloture = 'cloture';
    case Archive = 'archive';
    case Annule = 'annule';

    public function label(): string
    {
        return match ($this) {
            self::Depose => 'Déposé',
            self::Receptionne => 'Réceptionné',
            self::Reaffecte => 'Réaffecté',
            self::Traite => 'Traité',
            self::TraiteFavorable => 'Traité favorable',
            self::Rejete => 'Rejeté',
            self::Suspendu => 'Suspendu (En cours de traitement)',
            self::Cloture => 'Clôturé',
            self::Archive => 'Archivé / classé',
            self::Annule => 'Annulé',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Depose => 'secondary',
            self::Receptionne => 'primary',
            self::Reaffecte => 'warning',
            self::Traite, self::TraiteFavorable => 'success',
            self::Rejete => 'danger',
            self::Suspendu => 'info',
            self::Cloture => 'dark',
            self::Archive => 'secondary',
            self::Annule => 'danger',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Depose => 'bi-inbox-fill',
            self::Receptionne => 'bi-check-circle-fill',
            self::Reaffecte => 'bi-arrow-left-right',
            self::Traite, self::TraiteFavorable => 'bi-patch-check-fill',
            self::Rejete => 'bi-x-octagon-fill',
            self::Suspendu => 'bi-pause-circle-fill',
            self::Cloture => 'bi-lock-fill',
            self::Archive => 'bi-archive-fill',
            self::Annule => 'bi-x-circle-fill',
        };
    }

    /** Courriers visibles dans la file de traitement (y compris suspendus à reprendre). */
    public static function pourTraitement(): array
    {
        return [self::Receptionne, self::Suspendu];
    }

    /**
     * Statuts d'un courrier déjà traité par un agent (historique personnel).
     * Exclut Receptionne après reprise : le dossier revient alors dans la file active.
     */
    public static function pourHistoriqueTraitement(): array
    {
        return [
            self::TraiteFavorable,
            self::Traite,
            self::Rejete,
            self::Suspendu,
            self::Cloture,
            self::Archive,
        ];
    }

    /** Statuts pour lesquels le formulaire de résultat (favorable/rejet/suspendu) est disponible. */
    public static function traitablesActivement(): array
    {
        return [self::Receptionne];
    }

    /** Statuts pour lesquels réception / réaffectation sont bloquées. */
    public function bloqueReceptionOuReaffectation(): bool
    {
        return in_array($this, [
            self::Traite,
            self::TraiteFavorable,
            self::Rejete,
            self::Suspendu,
            self::Cloture,
            self::Archive,
            self::Annule,
        ], true);
    }

    public function enAttenteDelivrance(): bool
    {
        return $this === self::TraiteFavorable;
    }
}
