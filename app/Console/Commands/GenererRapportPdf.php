<?php

namespace App\Console\Commands;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GenererRapportPdf extends Command
{
    protected $signature = 'rapport:pdf
                            {--output=docs/Rapport_Final_Gestion_DGI2_Partie1.pdf : Chemin de sortie relatif au projet}';

    protected $description = 'Génère le rapport PDF (page de garde → chapitre 1)';

    public function handle(): int
    {
        $relatif = (string) $this->option('output');
        $chemin = base_path($relatif);

        File::ensureDirectoryExists(dirname($chemin));

        $logoPath = public_path('images/dgi/logo-dgi.png');

        $pdf = Pdf::loadView('rapports.memoire-partie1', [
            'logoPath' => $logoPath,
        ])->setPaper('a4', 'portrait');

        $pdf->save($chemin);

        $this->info('Rapport généré : '.$chemin);

        return self::SUCCESS;
    }
}
