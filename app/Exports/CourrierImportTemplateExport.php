<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * Modèle Excel à remplir pour l'import de courriers.
 */
class CourrierImportTemplateExport implements WithMultipleSheets
{
    /**
     * @return list<object>
     */
    public function sheets(): array
    {
        return [
            new CourrierImportModeleSheet(),
            new CourrierImportReferencesSheet(),
        ];
    }
}
