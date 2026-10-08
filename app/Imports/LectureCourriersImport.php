<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * Lit uniquement la première feuille du fichier importé et en restitue
 * les lignes brutes. L'interprétation des colonnes est faite par
 * App\Services\CourrierImportService.
 */
class LectureCourriersImport implements ToArray, WithMultipleSheets
{
    /** @var list<array<int, mixed>> */
    public array $lignes = [];

    /**
     * @return array<int, object>
     */
    public function sheets(): array
    {
        return [0 => $this];
    }

    /**
     * @param  array<int, array<int, mixed>>  $lignes
     */
    public function array(array $lignes): void
    {
        $this->lignes = $lignes;
    }
}
