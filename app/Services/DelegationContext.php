<?php

namespace App\Services;

use App\Models\Delegation;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

/**
 * Contexte d'espace de travail : identité réelle (acteur) vs agent effectif (sujet).
 * Sans délégation choisie, sujet === acteur → comportement inchangé.
 */
class DelegationContext
{
    public const SESSION_ESPACE = 'espace_travail';

    public const SESSION_PROMPT = 'delegation_prompt_needed';

    /** Valeur session pour « Mon espace ». */
    public const ESPACE_PERSONNEL = 'self';

    public function acteur(): ?User
    {
        return Auth::guard('web')->user();
    }

    /**
     * Agent dont on consulte / traite les dossiers.
     */
    public function sujet(): ?User
    {
        $acteur = $this->acteur();

        if (! $acteur) {
            return null;
        }

        $delegation = $this->delegationActive();

        if (! $delegation) {
            return $acteur;
        }

        return $delegation->agentAbsent ?? $acteur;
    }

    public function enRemplacement(): bool
    {
        return $this->delegationActive() !== null;
    }

    public function pourCompteDeId(): ?int
    {
        if (! $this->enRemplacement()) {
            return null;
        }

        return $this->sujet()?->id;
    }

    public function delegationActive(): ?Delegation
    {
        $acteur = $this->acteur();
        $espace = Session::get(self::SESSION_ESPACE, self::ESPACE_PERSONNEL);

        if (! $acteur || $espace === self::ESPACE_PERSONNEL || $espace === null) {
            return null;
        }

        $delegation = Delegation::query()
            ->with('agentAbsent')
            ->where('id', (int) $espace)
            ->where('agent_remplacant_id', $acteur->id)
            ->enCours()
            ->first();

        if (! $delegation) {
            Session::put(self::SESSION_ESPACE, self::ESPACE_PERSONNEL);

            return null;
        }

        return $delegation;
    }

    /**
     * @return Collection<int, Delegation>
     */
    public function delegationsActivesPourRemplacant(?User $user = null): Collection
    {
        $user ??= $this->acteur();

        if (! $user) {
            return collect();
        }

        return Delegation::query()
            ->with(['agentAbsent', 'unite'])
            ->where('agent_remplacant_id', $user->id)
            ->enCours()
            ->orderBy('date_debut')
            ->get();
    }

    public function doitChoisirEspace(): bool
    {
        if (! Session::get(self::SESSION_PROMPT)) {
            return false;
        }

        return $this->delegationsActivesPourRemplacant()->isNotEmpty();
    }

    public function marquerPromptApresConnexion(User $user): void
    {
        Session::forget(self::SESSION_ESPACE);

        if ($this->delegationsActivesPourRemplacant($user)->isNotEmpty()) {
            Session::put(self::SESSION_PROMPT, true);
            Session::put(self::SESSION_ESPACE, self::ESPACE_PERSONNEL);
        } else {
            Session::forget(self::SESSION_PROMPT);
            Session::put(self::SESSION_ESPACE, self::ESPACE_PERSONNEL);
        }
    }

    /**
     * @param  int|string|null  $delegationId  null|'self' = mon espace, sinon id de délégation
     */
    public function choisirEspace(int|string|null $delegationId): void
    {
        $acteur = $this->acteur();

        if (! $acteur) {
            return;
        }

        if ($delegationId === null || $delegationId === '' || $delegationId === self::ESPACE_PERSONNEL) {
            Session::put(self::SESSION_ESPACE, self::ESPACE_PERSONNEL);
            Session::forget(self::SESSION_PROMPT);

            Log::info('Changement d\'espace de travail', [
                'acteur_id' => $acteur->id,
                'espace' => 'personnel',
            ]);

            return;
        }

        $delegation = Delegation::query()
            ->where('id', (int) $delegationId)
            ->where('agent_remplacant_id', $acteur->id)
            ->enCours()
            ->firstOrFail();

        Session::put(self::SESSION_ESPACE, $delegation->id);
        Session::forget(self::SESSION_PROMPT);

        Log::info('Changement d\'espace de travail', [
            'acteur_id' => $acteur->id,
            'espace' => 'remplacement',
            'delegation_id' => $delegation->id,
            'agent_absent_id' => $delegation->agent_absent_id,
        ]);
    }

    public function libelleEspaceActif(): string
    {
        $delegation = $this->delegationActive();

        if ($delegation) {
            return $delegation->libelleRemplacement();
        }

        return $this->acteur()?->name ?? 'Mon espace';
    }
}
