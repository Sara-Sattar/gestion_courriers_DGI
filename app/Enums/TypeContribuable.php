<?php

namespace App\Enums;

enum TypeContribuable: string
{
    case PhysiqueProfessionnel = 'physique_professionnel';
    case PhysiqueParticulier = 'physique_particulier';
    case PersonneMorale = 'personne_morale';
    case Autre = 'autre';

    public function label(): string
    {
        return match ($this) {
            self::PhysiqueProfessionnel => 'Physique professionnel',
            self::PhysiqueParticulier => 'Physique particulier',
            self::PersonneMorale => 'Personne morale',
            self::Autre => 'Autre',
        };
    }
}
