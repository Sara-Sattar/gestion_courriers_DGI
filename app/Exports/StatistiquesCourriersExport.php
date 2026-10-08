<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class StatistiquesCourriersExport implements WithMultipleSheets
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        private readonly array $payload,
    ) {}

    /**
     * @return list<StatistiquesCourriersSheet>
     */
    public function sheets(): array
    {
        return [
            new StatistiquesCourriersSheet('Par type', $this->payload['par_type'] ?? collect()),
            new StatistiquesCourriersSheet('Par BO', $this->payload['par_bo'] ?? collect()),
            new StatistiquesCourriersSheet('Par US', $this->payload['par_us'] ?? collect()),
            new StatistiquesCourriersSheet('Par mois', $this->payload['par_mois'] ?? collect()),
            new StatistiquesCourriersSheet('Par categorie', $this->payload['par_categorie'] ?? collect()),
            new StatistiquesCourriersSheet('Par statut', $this->payload['par_statut'] ?? collect()),
            new StatistiquesCourriersSheet('Par priorite', $this->payload['par_priorite'] ?? collect()),
            new StatistiquesCourriersSheet('Reponse', $this->payload['par_reponse'] ?? collect()),
            new StatistiquesCourriersSheet('Delivrance', $this->payload['par_livraison'] ?? collect()),
        ];
    }
}
