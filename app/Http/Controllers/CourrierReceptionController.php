<?php

namespace App\Http\Controllers;

use App\Enums\ActionHistorique;
use App\Enums\ActionType;
use App\Enums\StatutCourrier;
use App\Http\Requests\AnnulerReceptionRequest;
use App\Http\Requests\ReaffecterCourrierRequest;
use App\Http\Requests\ReceptionnerCourrierRequest;
use App\Models\Courrier;
use App\Models\CourrierHistorique;
use App\Models\HistoriqueAffectation;
use App\Models\Role;
use App\Models\Unite;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CourrierReceptionController extends Controller
{
    public function index()
    {
        $today = now()->toDateString();
        $user = auth()->user();
        $unite = $user->unite;
        $hierarchyIds = $unite ? $unite->hierarchy_ids : [];

        $baseQuery = Courrier::with(['contribuable', 'utilisateur']);
        if (! empty($hierarchyIds)) {
            $baseQuery->whereIn('unite_actuelle_id', $hierarchyIds);
        } else {
            $baseQuery->where('responsable_actuel_id', $user->id);
        }

        $courriersAujourdhui = (clone $baseQuery)
            ->whereDate('date_depot', $today)
            ->where('statut', StatutCourrier::Depose)
            ->orderBy('date_depot', 'desc')
            ->get();

        // Les courriers en instance ne doivent concerner que l'utilisateur connecté.
        $courriersEnInstance = Courrier::with(['contribuable', 'utilisateur'])
            ->where('unite_actuelle_id', $user->unite_id)
            ->where('statut', StatutCourrier::Depose)
            ->whereDate('date_depot', '<', $today)
            ->orderBy('date_depot', 'asc')
            ->get();

        $historiquesReaffectations = HistoriqueAffectation::with(['courrier', 'user', 'ancienResponsable', 'nouveauResponsable', 'uniteSource', 'uniteDestination'])
            ->where('action_type', ActionType::Reaffectation)
            ->where(function ($query) {
                $query->whereNotNull('unite_source_id')
                    ->orWhereNotNull('ancien_responsable_id');
            })
            ->whereHas('courrier', function ($query) use ($hierarchyIds, $user) {
                if (! empty($hierarchyIds)) {
                    $query->whereIn('unite_actuelle_id', $hierarchyIds);
                } else {
                    $query->where('responsable_actuel_id', $user->id);
                }
            })
            ->orderByDesc('created_at')
            ->paginate(10, ['*'], 'reaffectations_page');

        $allowedUsers = collect();
        $allowedUnites = collect();
        $currentUnite = $user->unite;

        if ($currentUnite) {
            $directChildUniteIds = $currentUnite->enfants->pluck('id')->toArray();
            $allowedUsers = User::with('unite')
                ->whereIn('unite_id', $directChildUniteIds)
                ->get();
            $allowedUnites = $currentUnite->enfants;
        }

        $directorRole = Role::where('name', 'Direction Régionale')->first();
        if ($directorRole) {
            $directors = User::with('unite')
                ->where('role_id', $directorRole->id)
                ->get();
            $allowedUsers = $allowedUsers->merge($directors);
            $directorUnites = Unite::whereIn('id', $directors->pluck('unite_id'))->get();
            $allowedUnites = $allowedUnites->merge($directorUnites);
        }

        $allowedUsers = $allowedUsers
            ->where('id', '!==', $user->id)
            ->unique('id')
            ->values();

        $allowedUnites = $allowedUnites
            ->where('id', '!==', $user->unite_id)
            ->unique('id')
            ->values();

        return view('courriers.reception', compact(
            'courriersAujourdhui',
            'courriersEnInstance',
            'historiquesReaffectations',
            'allowedUsers',
            'allowedUnites'
        ));
    }

    public function receptionner(ReceptionnerCourrierRequest $request)
    {
        $validated = $request->validated();
        $user = auth()->user();
        $now = now();

        DB::beginTransaction();

        try {
            foreach ($validated['courrier_ids'] as $courrierId) {
                $courrier = Courrier::findOrFail($courrierId);
                $this->authorize('update', $courrier);

                if ($courrier->statut !== StatutCourrier::Depose) {
                    continue;
                }

                $courrier->update([
                    'statut' => StatutCourrier::Receptionne,
                    'date_reception' => $now->toDateString(),
                    'heure_reception' => $now->toTimeString(),
                    'responsable_reception_id' => $user->id,
                    'responsable_actuel_id' => $user->id,
                    'unite_actuelle_id' => $user->unite_id,
                ]);

                CourrierHistorique::create([
                    'courrier_id' => $courrier->id,
                    'user_id' => $user->id,
                    'action' => ActionHistorique::Reception,
                    'commentaire' => 'Réception du courrier',
                ]);
            }

            DB::commit();

            return back()->with('success', 'Les courriers sélectionnés ont été réceptionnés avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Une erreur est survenue lors de la réception des courriers.');
        }
    }

    public function annulerReception(Courrier $courrier, AnnulerReceptionRequest $request)
    {
        $user = auth()->user();
        $this->authorize('update', $courrier);

        if ($courrier->statut === StatutCourrier::Traite) {
            return back()->with('error', 'Impossible d\'annuler la réception d\'un courrier déjà traité.');
        }

        if ($courrier->statut !== StatutCourrier::Receptionne) {
            return back()->with('error', 'Seul un courrier déjà réceptionné peut être annulé.');
        }

        DB::beginTransaction();

        try {
            $courrier->update([
                'statut' => StatutCourrier::Depose,
                'date_reception' => null,
                'heure_reception' => null,
                'responsable_reception_id' => null,
            ]);

            CourrierHistorique::create([
                'courrier_id' => $courrier->id,
                'user_id' => $user->id,
                'action' => ActionHistorique::AnnulationReception,
                'commentaire' => 'Annulation de la réception du courrier',
            ]);

            DB::commit();

            return back()->with('success', 'La réception du courrier a été annulée avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Une erreur est survenue lors de l\'annulation de la réception.');
        }
    }

    public function reaffecter(Courrier $courrier, ReaffecterCourrierRequest $request)
    {
        $validated = $request->validated();
        $user = auth()->user();
        $this->authorize('update', $courrier);

        if ($courrier->statut === StatutCourrier::Traite) {
            return back()->with('error', 'Impossible de réaffecter un courrier déjà traité.');
        }

        if (! in_array($courrier->statut, [StatutCourrier::Receptionne, StatutCourrier::Reaffecte], true)) {
            return back()->with('error', 'Le courrier doit d\'abord être réceptionné avant toute réaffectation.');
        }

        $currentUnite = $user->unite;
        $allowed = false;
        $directChildUniteIds = $currentUnite ? $currentUnite->enfants->pluck('id')->toArray() : [];
        $directorRole = Role::where('name', 'Direction Régionale')->first();
        $directorUserIds = $directorRole ? User::where('role_id', $directorRole->id)->pluck('id')->toArray() : [];
        $directorUniteIds = $directorRole ? Unite::whereIn('id', User::where('role_id', $directorRole->id)->pluck('unite_id'))->pluck('id')->toArray() : [];

        if ($validated['type_destinataire'] === 'utilisateur') {
            $destUser = User::findOrFail($validated['destinataire_id']);
            if (
                (in_array($destUser->unite_id, $directChildUniteIds)) ||
                (in_array($destUser->id, $directorUserIds))
            ) {
                $allowed = true;
            }
            if ($destUser->id === $user->id) {
                $allowed = false;
            }
        } else {
            $destUnite = Unite::findOrFail($validated['destinataire_id']);
            if (
                (in_array($destUnite->id, $directChildUniteIds)) ||
                (in_array($destUnite->id, $directorUniteIds))
            ) {
                $allowed = true;
            }

            if ($destUnite->id === $user->unite_id) {
                $allowed = false;
            }
        }

        if (! $allowed) {
            return back()->with('error', 'Réaffectation vers cette destination non autorisée.');
        }

        DB::beginTransaction();

        try {
            $ancienResponsableId = $courrier->responsable_actuel_id;
            $ancienneUniteId = $courrier->unite_actuelle_id;

            if ($validated['type_destinataire'] === 'utilisateur') {
                $nouveauUser = User::findOrFail($validated['destinataire_id']);
                $nouveauUniteId = $nouveauUser->unite_id;
                $nouveauResponsableId = $nouveauUser->id;
            } else {
                $nouvelleUnite = Unite::findOrFail($validated['destinataire_id']);
                $nouveauUniteId = $nouvelleUnite->id;
                $nouveauResponsableId = null;
            }

            $courrier->update([
                'statut' => StatutCourrier::Reaffecte,
                'unite_actuelle_id' => $nouveauUniteId,
                'responsable_actuel_id' => $nouveauResponsableId,
            ]);

            HistoriqueAffectation::create([
                'courrier_id' => $courrier->id,
                'user_id' => $user->id,
                'unite_source_id' => $ancienneUniteId,
                'unite_destination_id' => $nouveauUniteId,
                'ancien_responsable_id' => $ancienResponsableId,
                'nouveau_responsable_id' => $nouveauResponsableId,
                'action_type' => ActionType::Reaffectation,
                'motif' => $validated['motif'],
                'commentaire' => $validated['motif'],
            ]);

            DB::commit();

            return back()->with('success', 'Le courrier a été réaffecté avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Une erreur est survenue lors de la réaffectation.');
        }
    }
}
