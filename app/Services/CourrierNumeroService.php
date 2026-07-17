<?php

namespace App\Services;

use App\Models\CourrierSequence;
use App\Models\Unite;
use Illuminate\Support\Facades\DB;

class CourrierNumeroService
{
    /**
     * @return array{numero: string, sequence: int, code_unite: string, annee: int}
     */
    public function generer(Unite $unite, ?int $annee = null): array
    {
        $annee ??= (int) now()->format('Y');

        return DB::transaction(function () use ($unite, $annee) {
            $compteur = CourrierSequence::query()
                ->where('annee', $annee)
                ->where('unite_id', $unite->id)
                ->lockForUpdate()
                ->first();

            if (! $compteur) {
                $compteur = CourrierSequence::create([
                    'annee' => $annee,
                    'unite_id' => $unite->id,
                    'dernier_numero' => 0,
                ]);
            }

            $compteur->increment('dernier_numero');
            $compteur->refresh();

            $sequence = $compteur->dernier_numero;
            $numero = sprintf('%d-%06d-%s', $annee, $sequence, $unite->code);

            return [
                'numero' => $numero,
                'sequence' => $sequence,
                'code_unite' => $unite->code,
                'annee' => $annee,
            ];
        });
    }
}
