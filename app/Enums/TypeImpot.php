<?php

namespace App\Enums;

enum TypeImpot: string
{
    case IR = 'IR';
    case IS = 'IS';
    case TVA = 'TVA';
    case TP = 'TP';
    case TSC = 'TSC';
    case TH = 'TH';
    case DE = 'DE';
    case DT = 'DT';
    case TSAVA = 'TSAVA';
    case CSS = 'CSS';
    case TL = 'TL';

    public function label(): string
    {
        return $this->value;
    }

    public function libelle(): string
    {
        return match ($this) {
            self::IR => 'Impôt sur le revenu',
            self::IS => 'Impôt sur les sociétés',
            self::TVA => 'Taxe sur la valeur ajoutée',
            self::TP => 'Taxe professionnelle',
            self::TSC => 'Taxe de services communaux',
            self::TH => 'Taxe d\'habitation',
            self::DE => 'Droits d\'enregistrement',
            self::DT => 'Droits de timbre',
            self::TSAVA => 'Taxe spéciale annuelle sur les véhicules',
            self::CSS => 'Contribution sociale de solidarité',
            self::TL => 'Taxe de logement',
        };
    }

    /**
     * @return list<self>
     */
    public static function liste(): array
    {
        return self::cases();
    }
}
