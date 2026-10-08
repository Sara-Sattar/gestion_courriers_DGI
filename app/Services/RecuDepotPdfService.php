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
            'logo' => $this->logoEncode(),
            'impots' => $courrier->impots->pluck('impot')->map->value->all(),
            'annees' => $courrier->annees->sortByDesc('annee')->pluck('annee')->all(),
        ])
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true);
    }

    /**
     * DomPDF ne suit pas les URL de l'application : le logo est embarqué
     * directement dans le document.
     */
    private function logoEncode(): ?string
    {
        $chemin = public_path(config('dgi.logo_navbar'));

        if (! is_file($chemin)) {
            return null;
        }

        return 'data:image/png;base64,'.base64_encode((string) file_get_contents($chemin));
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
