<?php

namespace App\Console\Commands;

use App\Models\Courrier;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\URL;
use Symfony\Component\Process\Process;

class GenererRapportInterfaces extends Command
{
    protected $signature = 'rapport:interfaces
                            {--base-url=http://127.0.0.1:8010 : URL locale de l\'application}
                            {--output=docs/Rapport_Interfaces_Et_Base_DGI2.pdf : Chemin de sortie}
                            {--skip-captures : Réutiliser les captures déjà présentes}';

    protected $description = 'Génère le PDF 4.2.4 Base de données + 4.3 Interfaces (avec captures)';

    private string $baseUrl = 'http://127.0.0.1:8010';

    private string $chrome = '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';

    public function handle(): int
    {
        $this->baseUrl = rtrim((string) $this->option('base-url'), '/');
        $capturesDir = base_path('docs/captures');
        File::ensureDirectoryExists($capturesDir);

        if (! $this->option('skip-captures')) {
            if (! $this->serveurJoignable()) {
                $this->error('Impossible de joindre '.$this->baseUrl.' — démarrez php artisan serve.');

                return self::FAILURE;
            }
            $this->capturerEcrans($capturesDir);
        }

        $captures = [];
        foreach (File::glob($capturesDir.'/*.png') ?: [] as $fichier) {
            $captures[basename($fichier, '.png')] = $fichier;
        }

        $relatif = (string) $this->option('output');
        $chemin = base_path($relatif);
        File::ensureDirectoryExists(dirname($chemin));

        Pdf::loadView('rapports.interfaces-et-base', [
            'logoPath' => public_path('images/dgi/logo-dgi.png'),
            'captures' => $captures,
        ])->setPaper('a4', 'portrait')->save($chemin);

        $this->info('PDF généré : '.$chemin);

        return self::SUCCESS;
    }

    private function serveurJoignable(): bool
    {
        $process = new Process(['curl', '-s', '-o', '/dev/null', '-w', '%{http_code}', $this->baseUrl.'/login']);
        $process->setTimeout(8);
        $process->run();

        $code = trim($process->getOutput());

        return in_array($code, ['200', '302'], true);
    }

    private function capturerEcrans(string $dir): void
    {
        $courrier = Courrier::query()->orderByDesc('id')->first();
        $ficheDelivrance = $courrier ? '/courriers/'.$courrier->id.'/delivrance' : null;
        $ficheTraitement = $courrier ? '/courriers/'.$courrier->id.'/traitement' : null;
        $ficheCourrier = $courrier ? '/courriers/'.$courrier->id : null;

        $this->screenshot($dir.'/01-login.png', $this->baseUrl.'/login');

        $accueil = 'accueil@dgi.ma';
        $this->screenshot($dir.'/02-dashboard-accueil.png', $this->urlAuth($accueil, '/dashboard'));
        $this->screenshot($dir.'/03-enregistrement.png', $this->urlAuth($accueil, '/courriers/create'));
        $this->screenshot($dir.'/04-delivrance.png', $this->urlAuth($accueil, '/delivrance'));
        if ($ficheDelivrance) {
            $this->screenshot($dir.'/05-delivrance-fiche.png', $this->urlAuth($accueil, $ficheDelivrance));
        }
        $this->screenshot($dir.'/06-statistiques.png', $this->urlAuth($accueil, '/consultation'));
        $this->screenshot($dir.'/07-profil.png', $this->urlAuth($accueil, '/profile'));
        if ($ficheCourrier) {
            $this->screenshot($dir.'/09-fiche-courrier.png', $this->urlAuth($accueil, $ficheCourrier));
        }

        $agent = 'agent.bureau1@dgi.ma';
        $this->screenshot($dir.'/10-reception.png', $this->urlAuth($agent, '/reception'));
        $this->screenshot($dir.'/11-traitement.png', $this->urlAuth($agent, '/traitement'));
        if ($ficheTraitement) {
            $this->screenshot($dir.'/12-traitement-fiche.png', $this->urlAuth($agent, $ficheTraitement));
        }

        $chef = 'bureau@dgi.ma';
        $this->screenshot($dir.'/13-delegations.png', $this->urlAuth($chef, '/delegations'));
        $this->screenshot($dir.'/14-delegations-create.png', $this->urlAuth($chef, '/delegations/create'));

        $admin = 'admin@dgi.ma';
        $this->screenshot($dir.'/15-users.png', $this->urlAuth($admin, '/users'));
        $this->screenshot($dir.'/16-categories.png', $this->urlAuth($admin, '/categories'));
        $this->screenshot($dir.'/17-types-courrier.png', $this->urlAuth($admin, '/types-courrier'));
        $this->screenshot($dir.'/18-unites.png', $this->urlAuth($admin, '/unites'));
    }

    private function urlAuth(string $email, string $next): string
    {
        URL::forceRootUrl($this->baseUrl);
        URL::forceScheme('http');

        return URL::temporarySignedRoute('rapport.entrer', now()->addMinutes(30), [
            'email' => $email,
            'next' => $next,
        ]);
    }

    private function screenshot(string $destination, string $url): void
    {
        $this->line('Capture : '.basename($destination));

        $userData = sys_get_temp_dir().'/dgi-chrome-'.uniqid('', true);
        File::ensureDirectoryExists($userData);

        $process = new Process([
            $this->chrome,
            '--headless',
            '--disable-gpu',
            '--no-sandbox',
            '--disable-dev-shm-usage',
            '--hide-scrollbars',
            '--no-first-run',
            '--no-default-browser-check',
            '--disable-extensions',
            '--default-background-color=ffffffff',
            '--force-device-scale-factor=1',
            '--window-size=1400,900',
            '--user-data-dir='.$userData,
            '--screenshot='.$destination,
            $url,
        ]);
        $process->setTimeout(60);
        try {
            $process->run();
        } catch (\Throwable) {
            // Chrome peut terminer par SIGABRT après avoir écrit la capture.
        }

        if (! is_file($destination) || filesize($destination) < 2000) {
            $this->warn('  échec');
        } else {
            $this->info('  OK ('.round(filesize($destination) / 1024).' Ko)');
        }

        File::deleteDirectory($userData);
    }
}
