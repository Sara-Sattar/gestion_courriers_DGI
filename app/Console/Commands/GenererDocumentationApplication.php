<?php

namespace App\Console\Commands;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GenererDocumentationApplication extends Command
{
    protected $signature = 'doc:application
                            {--output=docs/Documentation_Complete_Application_DGI2.pdf : Chemin de sortie relatif au projet}';

    protected $description = 'Génère le PDF de documentation complète (pages, comptes, fonctionnalités)';

    public function handle(): int
    {
        $relatif = (string) $this->option('output');
        $chemin = base_path($relatif);

        File::ensureDirectoryExists(dirname($chemin));

        $logoPath = public_path('images/dgi/logo-dgi.png');

        $pdf = Pdf::loadView('rapports.documentation-complete', [
            'logoPath' => $logoPath,
        ])->setPaper('a4', 'portrait');

        $pdf->save($chemin);

        $this->info('Documentation générée : '.$chemin);

        return self::SUCCESS;
    }
}
