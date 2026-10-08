<?php

namespace App\Services;

use App\Enums\MotifDelegation;
use App\Enums\StatutDelegation;
use App\Models\Delegation;
use App\Models\Unite;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class DelegationService
{
    /**
     * Seul le responsable opérationnel de son unité (chef) gère les délégations.
     * Un simple agent rattaché à la même unité / au même type de profil est exclu.
     */
    public function estResponsableHierarchique(User $utilisateur): bool
    {
        $unite = $utilisateur->unite;

        if (! $unite) {
            return false;
        }

        $responsable = $unite->responsable();

        return $responsable !== null && (int) $responsable->id === (int) $utilisateur->id;
    }

    /**
     * Unités du périmètre hiérarchique du responsable (descendance incluse).
     * L'Admin System n'a PAS de périmètre métier automatique.
     *
     * @return Collection<int, Unite>
     */
    public function unitesDansPerimetre(User $utilisateur): Collection
    {
        if (! $this->estResponsableHierarchique($utilisateur)) {
            return collect();
        }

        $hierarchyIds = $utilisateur->unite?->hierarchy_ids ?? [];

        if ($hierarchyIds === []) {
            return collect();
        }

        return Unite::query()
            ->actives()
            ->whereIn('id', $hierarchyIds)
            ->orderBy('nom')
            ->get();
    }

    /**
     * Agents du périmètre (hors Admin), pour délégant / délégué.
     *
     * @return Collection<int, User>
     */
    public function agentsDuPerimetre(User $responsable, ?int $uniteId = null): Collection
    {
        $uniteIds = $this->unitesDansPerimetre($responsable)->pluck('id');

        if ($uniteIds->isEmpty()) {
            return collect();
        }

        if ($uniteId !== null && ! $uniteIds->contains($uniteId)) {
            return collect();
        }

        return User::query()
            ->with(['role', 'unite'])
            ->whereIn('unite_id', $uniteId ? [$uniteId] : $uniteIds->all())
            ->where(function ($q) {
                $q->whereNull('role_id')
                    ->orWhereHas('role', fn ($r) => $r->where('name', '!=', 'Admin'));
            })
            ->orderBy('name')
            ->get();
    }

    public function peutGererDelegation(User $utilisateur, Delegation $delegation): bool
    {
        if (! $utilisateur->can('delegations.voir') && ! $utilisateur->can('delegations.creer')) {
            return false;
        }

        return $this->unitesDansPerimetre($utilisateur)->contains('id', $delegation->unite_id);
    }

    /**
     * @param  array{
     *     agent_absent_id: int,
     *     agent_remplacant_id: int,
     *     unite_id: int,
     *     date_debut: string,
     *     date_fin: string,
     *     motif: string
     * }  $donnees
     */
    public function creer(array $donnees, User $createur): Delegation
    {
        $this->validerReglesMetier($donnees, $createur);

        $delegation = Delegation::create([
            ...$donnees,
            'motif' => MotifDelegation::from($donnees['motif']),
            'statut' => $this->statutSelonDates($donnees['date_debut'], $donnees['date_fin']),
            'created_by' => $createur->id,
        ]);

        Log::info('Délégation créée', [
            'delegation_id' => $delegation->id,
            'acteur_id' => $createur->id,
            'agent_absent_id' => $delegation->agent_absent_id,
            'agent_remplacant_id' => $delegation->agent_remplacant_id,
            'statut' => $delegation->statut->value,
        ]);

        return $delegation;
    }

    /**
     * @param  array{
     *     agent_absent_id: int,
     *     agent_remplacant_id: int,
     *     unite_id: int,
     *     date_debut: string,
     *     date_fin: string,
     *     motif: string
     * }  $donnees
     */
    public function mettreAJour(Delegation $delegation, array $donnees, User $acteur): Delegation
    {
        if (! $this->peutGererDelegation($acteur, $delegation)) {
            throw new InvalidArgumentException('Cette délégation est hors de votre périmètre.');
        }

        if (! $delegation->statut->estModifiable()) {
            throw new InvalidArgumentException(
                'Seule une délégation planifiée peut être modifiée.'
            );
        }

        $this->validerReglesMetier($donnees, $acteur, $delegation->id);

        $delegation->update([
            ...$donnees,
            'motif' => MotifDelegation::from($donnees['motif']),
            'statut' => $this->statutSelonDates($donnees['date_debut'], $donnees['date_fin']),
        ]);

        Log::info('Délégation modifiée', [
            'delegation_id' => $delegation->id,
            'acteur_id' => $acteur->id,
            'statut' => $delegation->fresh()->statut->value,
        ]);

        return $delegation->fresh();
    }

    public function annuler(Delegation $delegation, User $acteur): Delegation
    {
        if (! $this->peutGererDelegation($acteur, $delegation)) {
            throw new InvalidArgumentException('Cette délégation est hors de votre périmètre.');
        }

        if (! $delegation->statut->estAnnulable()) {
            throw new InvalidArgumentException(
                'Cette délégation ne peut plus être annulée.'
            );
        }

        $delegation->update(['statut' => StatutDelegation::Annulee]);

        Log::info('Délégation annulée', [
            'delegation_id' => $delegation->id,
            'acteur_id' => $acteur->id,
        ]);

        return $delegation->fresh();
    }

    /**
     * Active les planifiées dont la période a commencé, et termine celles dépassées.
     *
     * @return array{activees: int, expirees: int}
     */
    public function synchroniserStatuts(): array
    {
        $aujourdhui = now()->toDateString();

        $activees = Delegation::query()
            ->where('statut', StatutDelegation::Planifiee)
            ->whereDate('date_debut', '<=', $aujourdhui)
            ->whereDate('date_fin', '>=', $aujourdhui)
            ->update(['statut' => StatutDelegation::Active]);

        $expirees = Delegation::query()
            ->whereIn('statut', [StatutDelegation::Active, StatutDelegation::Planifiee])
            ->whereDate('date_fin', '<', $aujourdhui)
            ->update(['statut' => StatutDelegation::Terminee]);

        if ($activees > 0 || $expirees > 0) {
            Log::info('Délégations synchronisées', [
                'activees' => $activees,
                'expirees' => $expirees,
            ]);
        }

        return ['activees' => $activees, 'expirees' => $expirees];
    }

    public function expirerDepassees(): int
    {
        return $this->synchroniserStatuts()['expirees'];
    }

    /**
     * @param  array<string, mixed>  $donnees
     */
    private function validerReglesMetier(array $donnees, User $acteur, ?int $ignorerId = null): void
    {
        $uniteId = (int) $donnees['unite_id'];
        $absentId = (int) $donnees['agent_absent_id'];
        $remplacantId = (int) $donnees['agent_remplacant_id'];
        $perimetreIds = $this->unitesDansPerimetre($acteur)->pluck('id');

        if (! $perimetreIds->contains($uniteId)) {
            throw ValidationException::withMessages([
                'unite_id' => 'Vous ne pouvez pas créer de délégation pour cette unité (hors périmètre hiérarchique).',
            ]);
        }

        if ($absentId === $remplacantId) {
            throw ValidationException::withMessages([
                'agent_remplacant_id' => 'Un utilisateur ne peut pas se remplacer lui-même.',
            ]);
        }

        $absent = User::query()->findOrFail($absentId);
        $remplacant = User::query()->findOrFail($remplacantId);

        if (! $perimetreIds->contains((int) $absent->unite_id)) {
            throw ValidationException::withMessages([
                'agent_absent_id' => 'Le délégant n\'appartient pas à votre périmètre hiérarchique.',
            ]);
        }

        if (! $perimetreIds->contains((int) $remplacant->unite_id)) {
            throw ValidationException::withMessages([
                'agent_remplacant_id' => 'Le délégué n\'appartient pas à votre périmètre hiérarchique.',
            ]);
        }

        if ((int) $absent->unite_id !== $uniteId) {
            throw ValidationException::withMessages([
                'unite_id' => 'L\'unité doit correspondre à celle du délégant.',
            ]);
        }

        $debut = $donnees['date_debut'];
        $fin = $donnees['date_fin'];

        if ($fin < $debut) {
            throw ValidationException::withMessages([
                'date_fin' => 'La date de fin doit être postérieure ou égale à la date de début.',
            ]);
        }

        $chevauchement = Delegation::query()
            ->where('agent_absent_id', $absentId)
            ->whereIn('statut', array_map(fn (StatutDelegation $s) => $s->value, StatutDelegation::incompatibles()))
            ->when($ignorerId, fn ($q) => $q->where('id', '!=', $ignorerId))
            ->whereDate('date_debut', '<=', $fin)
            ->whereDate('date_fin', '>=', $debut)
            ->exists();

        if ($chevauchement) {
            throw ValidationException::withMessages([
                'date_debut' => 'Une délégation planifiée ou active existe déjà pour cet utilisateur sur cette période.',
            ]);
        }
    }

    private function statutSelonDates(string $debut, string $fin): StatutDelegation
    {
        $aujourdhui = now()->toDateString();

        if ($fin < $aujourdhui) {
            return StatutDelegation::Terminee;
        }

        if ($debut > $aujourdhui) {
            return StatutDelegation::Planifiee;
        }

        return StatutDelegation::Active;
    }
}
