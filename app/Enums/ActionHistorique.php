<?php

namespace App\Enums;

enum ActionHistorique: string
{
    case Depot = 'depot';
    case Affectation = 'affectation';
    case Reaffectation = 'reaffectation';
    case Reception = 'reception';
    case AnnulationReception = 'annulation_reception';
    case RefusReception = 'refus_reception';
    case RefusReaffectation = 'refus_reaffectation';
    case AnnulationDepot = 'annulation_depot';
    case DebutTraitement = 'debut_traitement';
    case Suspension = 'suspension';
    case Reprise = 'reprise';
    case TraitementFavorable = 'traitement_favorable';
    case TraitementRejete = 'traitement_rejete';
    case Archivage = 'archivage';
    case RetourAccueil = 'retour_accueil';
    case Delivrance = 'delivrance';
    case Cloture = 'cloture';
    /** @deprecated Conservé pour les historiques déjà enregistrés. */
    case Traitement = 'traitement';

    public function label(): string
    {
        return match ($this) {
            self::Depot => 'Dépôt',
            self::Affectation => 'Affectation',
            self::Reaffectation => 'Réaffectation',
            self::Reception => 'Réception',
            self::AnnulationReception => 'Annulation de réception',
            self::RefusReception => 'Refus de réception',
            self::RefusReaffectation => 'Refus de réaffectation',
            self::AnnulationDepot => 'Annulation de dépôt',
            self::DebutTraitement => 'Début traitement',
            self::Suspension => 'Suspension',
            self::Reprise => 'Reprise',
            self::TraitementFavorable => 'Traitement favorable',
            self::TraitementRejete => 'Traitement rejeté',
            self::Archivage => 'Archivage / classement',
            self::RetourAccueil => 'Retour à l\'Accueil',
            self::Delivrance => 'Délivrance',
            self::Cloture => 'Clôture',
            self::Traitement => 'Traitement',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Depot => 'bi-inbox',
            self::Affectation => 'bi-diagram-3',
            self::Reaffectation => 'bi-arrow-left-right',
            self::Reception => 'bi-check-circle',
            self::AnnulationReception => 'bi-x-circle',
            self::RefusReception => 'bi-slash-circle',
            self::RefusReaffectation => 'bi-arrow-return-left',
            self::AnnulationDepot => 'bi-trash',
            self::DebutTraitement => 'bi-play-circle',
            self::Suspension => 'bi-pause-circle',
            self::Reprise => 'bi-arrow-clockwise',
            self::TraitementFavorable, self::Traitement => 'bi-patch-check',
            self::TraitementRejete => 'bi-x-octagon',
            self::Archivage => 'bi-archive',
            self::RetourAccueil => 'bi-house-door',
            self::Delivrance => 'bi-box-seam',
            self::Cloture => 'bi-lock',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Depot => 'secondary',
            self::Affectation => 'secondary',
            self::Reaffectation => 'info',
            self::Reception => 'primary',
            self::AnnulationReception => 'warning',
            self::RefusReception => 'danger',
            self::RefusReaffectation => 'warning',
            self::AnnulationDepot => 'danger',
            self::DebutTraitement => 'primary',
            self::Suspension => 'warning',
            self::Reprise => 'info',
            self::TraitementFavorable, self::Traitement => 'success',
            self::TraitementRejete => 'danger',
            self::Archivage => 'secondary',
            self::RetourAccueil => 'primary',
            self::Delivrance => 'success',
            self::Cloture => 'dark',
        };
    }
}
