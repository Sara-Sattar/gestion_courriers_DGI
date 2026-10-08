<?php

namespace App\Services;

use App\Models\Courrier;
use App\Models\CourrierSequence;
use App\Models\Unite;
use Illuminate\Support\Facades\DB;

class CourrierNumeroService
{
    /**
     * Format : AAAA-NNNNNN-CODE (ex. 2026-000007-BO001), CODE étant l'unité destinataire.
     * La séquence est propre à chaque unité et repart à 1 chaque année.
     *
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

            // On repart du plus grand numéro déjà attribué à l'unité pour éviter tout doublon
            // avec des courriers enregistrés sous l'ancien format (séquence annuelle globale).
            $dernierAttribue = (int) Courrier::query()
                ->where('annee_enregistrement', $annee)
                ->where('code_unite', $unite->code)
                ->max('sequence');

            $sequence = max($compteur->dernier_numero, $dernierAttribue) + 1;
            $compteur->update(['dernier_numero' => $sequence]);

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
