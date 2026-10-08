<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class StatistiquesCourriersSheet implements FromArray, ShouldAutoSize, WithHeadings, WithTitle
{
    /**
     * @param  Collection<int, array{libelle: string, code: ?string, total: int, pourcentage: float}>  $lignes
     */
    public function __construct(
        private readonly string $titre,
        private readonly Collection $lignes,
    ) {}

    public function title(): string
    {
        return $this->titre;
    }

    /**
     * @return list<string>
     */
    public function headings(): array
    {
        return ['Libellé', 'Code', 'Nombre', 'Pourcentage (%)'];
    }

    /**
     * @return list<list<string|int|float|null>>
     */
    public function array(): array
    {
        return $this->lignes->map(fn (array $ligne) => [
            $ligne['libelle'],
            $ligne['code'],
            $ligne['total'],
            $ligne['pourcentage'],
        ])->all();
    }
}
