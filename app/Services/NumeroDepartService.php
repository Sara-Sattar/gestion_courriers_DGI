<?php

namespace App\Services;

use App\Models\DepartSequence;
use App\Models\Unite;
use Illuminate\Support\Facades\DB;

class NumeroDepartService
{
    /**
     * Format : AAAA-NNN-CodeUnité (ordre sur 3 positions, unique par année).
     *
     * @return array{numero: string, sequence: int, code_unite: string, annee: int}
     */
    public function generer(Unite $unite, ?int $annee = null): array
    {
        $annee ??= (int) now()->format('Y');

        return DB::transaction(function () use ($unite, $annee) {
            $compteur = DepartSequence::query()
                ->where('annee', $annee)
                ->lockForUpdate()
                ->first();

            if (! $compteur) {
                $compteur = DepartSequence::create([
                    'annee' => $annee,
                    'dernier_numero' => 0,
                ]);
            }

            $compteur->increment('dernier_numero');
            $compteur->refresh();

            $sequence = $compteur->dernier_numero;
            $numero = sprintf('%d-%03d-%s', $annee, $sequence, $unite->code);

            return [
                'numero' => $numero,
                'sequence' => $sequence,
                'code_unite' => $unite->code,
                'annee' => $annee,
            ];
        });
    }
}
