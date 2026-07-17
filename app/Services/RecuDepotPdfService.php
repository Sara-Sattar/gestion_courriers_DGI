<?php

namespace App\Services;

use App\Models\Courrier;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\Response;

class RecuDepotPdfService
{
    public function generer(Courrier $courrier): \Barryvdh\DomPDF\PDF
    {
        $courrier->load([
            'contribuable',
            'categorie',
            'typeCourrier',
            'uniteDestinataire',
            'impots',
            'annees',
            'utilisateur',
        ]);

        return Pdf::loadView('courriers.pdf.recu-depot', [
            'courrier' => $courrier,
            'impots' => $courrier->impots->pluck('impot')->map->value->implode(' '),
            'annees' => $courrier->annees->sortByDesc('annee')->pluck('annee')->implode(' '),
        ])
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true);
    }

    public function telecharger(Courrier $courrier): Response
    {
        $pdf = $this->generer($courrier);

        return $pdf->download('recu-depot-'.$courrier->numero.'.pdf');
    }

    public function afficher(Courrier $courrier): Response
    {
        $pdf = $this->generer($courrier);

        return $pdf->stream('recu-depot-'.$courrier->numero.'.pdf');
    }
}
