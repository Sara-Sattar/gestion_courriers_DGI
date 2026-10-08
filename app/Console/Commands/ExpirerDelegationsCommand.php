<?php

namespace App\Console\Commands;

use App\Services\DelegationService;
use Illuminate\Console\Command;

class ExpirerDelegationsCommand extends Command
{
    protected $signature = 'delegations:expirer';

    protected $description = 'Active les délégations planifiées dues et termine celles expirées';

    public function handle(DelegationService $service): int
    {
        $resultat = $service->synchroniserStatuts();
        $this->info("{$resultat['activees']} délégation(s) activée(s), {$resultat['expirees']} expirée(s).");

        return self::SUCCESS;
    }
}
