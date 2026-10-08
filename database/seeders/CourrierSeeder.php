<?php

namespace Database\Seeders;

use App\Enums\ActionHistorique;
use App\Enums\StatutCourrier;
use App\Models\CategorieCourrier;
use App\Models\Contribuable;
use App\Models\Courrier;
use App\Models\CourrierAnnee;
use App\Models\CourrierHistorique;
use App\Models\CourrierImpot;
use App\Models\CourrierSequence;
use App\Models\HistoriqueAffectation;
use App\Models\TypeCourrier;
use App\Models\Unite;
use App\Models\User;
use Illuminate\Database\Seeder;

class CourrierSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->where('email', 'admin@dgi.ma')->firstOrFail();

        $unites = Unite::query()->whereIn('code', [
            'DR001', 'SERV002', 'BRIG001', 'BO001', 'SECT001', 'BUR003',
        ])->get()->keyBy('code');

        $contribuables = Contribuable::query()->orderBy('id')->get();

        if ($contribuables->count() < 10) {
            $this->command?->warn('Moins de 10 contribuables en base — exécutez d’abord ContribuableSeeder.');

            return;
        }

        $type = fn (string $categorie, string $nom) => TypeCourrier::query()
            ->whereHas('categorie', fn ($q) => $q->where('nom', $categorie))
            ->where('nom', $nom)
            ->firstOrFail();

        $courriers = [
            ['num' => '2026-000001-DR001', 'code' => 'DR001', 'seq' => 1, 'c' => 0, 'cat' => 'Attestation', 'type' => 'Attestation de régularité fiscale', 'objet' => 'Demande attestation de régularité fiscale', 'statut' => StatutCourrier::Depose, 'depot' => '2026-07-01 09:15:00', 'remarque' => 'Dossier complet', 'impots' => ['IR'], 'annees' => [2024, 2025]],
            ['num' => '2026-000002-DR001', 'code' => 'DR001', 'seq' => 2, 'c' => 1, 'cat' => 'Réclamation', 'type' => 'Réclamation sur avis d\'imposition', 'objet' => 'Réclamation sur avis IR 2024', 'statut' => StatutCourrier::Receptionne, 'depot' => '2026-07-03 10:30:00', 'reception' => ['2026-07-04', '08:45:00'], 'impots' => ['IR', 'TVA'], 'annees' => [2024]],
            ['num' => '2026-000003-DR001', 'code' => 'DR001', 'seq' => 3, 'c' => 2, 'cat' => 'Demande', 'type' => 'Demande de remise gracieuse', 'objet' => 'Demande remise gracieuse pénalités', 'statut' => StatutCourrier::Traite, 'depot' => '2026-07-05 11:00:00', 'remarque' => 'Traitée favorablement', 'reception' => ['2026-07-06', '09:00:00'], 'impots' => ['IR'], 'annees' => [2023, 2024]],
            ['num' => '2026-000001-SERV002', 'code' => 'SERV002', 'seq' => 1, 'c' => 3, 'cat' => 'Attestation', 'type' => 'Attestation d\'inscription à la TP', 'objet' => 'Attestation inscription TP', 'statut' => StatutCourrier::Depose, 'depot' => '2026-07-07 08:20:00', 'impots' => ['TP'], 'annees' => [2025]],
            ['num' => '2026-000002-SERV002', 'code' => 'SERV002', 'seq' => 2, 'c' => 4, 'cat' => 'Recours', 'type' => 'Recours gracieux', 'objet' => 'Recours gracieux IR', 'statut' => StatutCourrier::Reaffecte, 'depot' => '2026-07-08 14:00:00', 'remarque' => 'Réaffecté depuis bureau d\'ordre', 'reception' => ['2026-07-09', '10:30:00'], 'impots' => ['IR'], 'annees' => [2024]],
            ['num' => '2026-000003-SERV002', 'code' => 'SERV002', 'seq' => 3, 'c' => 5, 'cat' => 'Demande', 'type' => 'Demande de dégrèvement', 'objet' => 'Demande dégrèvement IS', 'statut' => StatutCourrier::Receptionne, 'depot' => '2026-07-10 09:45:00', 'reception' => ['2026-07-11', '11:00:00'], 'impots' => ['IS'], 'annees' => [2024, 2025]],
            ['num' => '2026-000001-BRIG001', 'code' => 'BRIG001', 'seq' => 1, 'c' => 6, 'cat' => 'Réclamation', 'type' => 'Réclamation sur pénalités', 'objet' => 'Réclamation pénalités retard déclaration', 'statut' => StatutCourrier::Depose, 'depot' => '2026-07-12 10:00:00', 'impots' => ['TVA'], 'annees' => [2025]],
            ['num' => '2026-000002-BRIG001', 'code' => 'BRIG001', 'seq' => 2, 'c' => 7, 'cat' => 'Recours', 'type' => 'Recours hiérarchique', 'objet' => 'Recours hiérarchique contrôle fiscal', 'statut' => StatutCourrier::Traite, 'depot' => '2026-07-13 11:30:00', 'remarque' => 'Clôturé', 'reception' => ['2026-07-14', '09:15:00'], 'impots' => ['IR', 'IS'], 'annees' => [2023]],
            ['num' => '2026-000003-BRIG001', 'code' => 'BRIG001', 'seq' => 3, 'c' => 8, 'cat' => 'Attestation', 'type' => 'Attestation de régularité fiscale', 'objet' => 'Attestation régularité avant marché public', 'statut' => StatutCourrier::Depose, 'depot' => '2026-07-15 08:00:00', 'impots' => ['IR'], 'annees' => [2025]],
            ['num' => '2026-000001-BO001', 'code' => 'BO001', 'seq' => 1, 'c' => 9, 'cat' => 'Demande', 'type' => 'Demande de remise gracieuse', 'objet' => 'Demande remise gracieuse TVA', 'statut' => StatutCourrier::Receptionne, 'depot' => '2026-07-16 09:00:00', 'reception' => ['2026-07-16', '14:00:00'], 'impots' => ['TVA'], 'annees' => [2024]],
            ['num' => '2026-000002-BO001', 'code' => 'BO001', 'seq' => 2, 'c' => 0, 'cat' => 'Réclamation', 'type' => 'Réclamation sur avis d\'imposition', 'objet' => 'Réclamation avis taxe professionnelle', 'statut' => StatutCourrier::Depose, 'depot' => '2026-07-17 10:15:00', 'impots' => ['TP'], 'annees' => [2025]],
            ['num' => '2026-000003-BO001', 'code' => 'BO001', 'seq' => 3, 'c' => 1, 'cat' => 'Attestation', 'type' => 'Attestation d\'inscription à la TP', 'objet' => 'Attestation TP renouvellement', 'statut' => StatutCourrier::Traite, 'depot' => '2026-07-18 11:00:00', 'remarque' => 'Dossier traité', 'reception' => ['2026-07-19', '08:30:00'], 'impots' => ['TP'], 'annees' => [2025]],
            ['num' => '2026-000001-SECT001', 'code' => 'SECT001', 'seq' => 1, 'c' => 2, 'cat' => 'Demande', 'type' => 'Demande de dégrèvement', 'objet' => 'Dégrèvement taxe d\'habitation Guéliz', 'statut' => StatutCourrier::Depose, 'depot' => '2026-07-20 09:30:00', 'impots' => ['IR'], 'annees' => [2024]],
            ['num' => '2026-000002-SECT001', 'code' => 'SECT001', 'seq' => 2, 'c' => 3, 'cat' => 'Recours', 'type' => 'Recours gracieux', 'objet' => 'Recours gracieux secteur Guéliz', 'statut' => StatutCourrier::Depose, 'depot' => '2026-07-21 10:00:00', 'impots' => ['IR'], 'annees' => [2025]],
            ['num' => '2026-000001-BUR003', 'code' => 'BUR003', 'seq' => 1, 'c' => 4, 'cat' => 'Recours', 'type' => 'Recours hiérarchique', 'objet' => 'Recours recouvrement amiable', 'statut' => StatutCourrier::Depose, 'depot' => '2026-07-22 08:45:00', 'impots' => ['IS'], 'annees' => [2024]],
            ['num' => '2026-000002-BUR003', 'code' => 'BUR003', 'seq' => 2, 'c' => 5, 'cat' => 'Réclamation', 'type' => 'Réclamation sur pénalités', 'objet' => 'Réclamation pénalités recouvrement', 'statut' => StatutCourrier::Receptionne, 'depot' => '2026-07-23 09:00:00', 'reception' => ['2026-07-24', '10:00:00'], 'impots' => ['TVA'], 'annees' => [2025]],
        ];

        foreach ($courriers as $data) {
            $unite = $unites[$data['code']];
            $responsable = $unite->responsable();
            $typeCourrier = $type($data['cat'], $data['type']);
            $categorie = CategorieCourrier::query()->where('nom', $data['cat'])->firstOrFail();

            $courrier = Courrier::query()->updateOrCreate(
                ['numero' => $data['num']],
                [
                    'contribuable_id' => $contribuables[$data['c']]->id,
                    'categorie_courrier_id' => $categorie->id,
                    'type_courrier_id' => $typeCourrier->id,
                    'objet' => $data['objet'],
                    'unite_destinataire_id' => $unite->id,
                    'unite_actuelle_id' => $unite->id,
                    'responsable_actuel_id' => $data['statut'] === StatutCourrier::Depose
                        ? null
                        : $responsable?->id,
                    'code_unite' => $unite->code,
                    'annee_enregistrement' => 2026,
                    'sequence' => $data['seq'],
                    'remarque' => $data['remarque'] ?? null,
                    'statut' => $data['statut'],
                    'date_depot' => $data['depot'],
                    'priorite' => 'Moyenne',
                    'date_reception' => $data['reception'][0] ?? null,
                    'heure_reception' => $data['reception'][1] ?? null,
                    'responsable_reception_id' => isset($data['reception']) ? $responsable?->id : null,
                    'user_id' => $admin->id,
                ]
            );

            foreach ($data['impots'] as $impot) {
                CourrierImpot::query()->updateOrCreate(
                    ['courrier_id' => $courrier->id, 'impot' => $impot],
                    ['impot' => $impot]
                );
            }

            foreach ($data['annees'] as $annee) {
                CourrierAnnee::query()->updateOrCreate(
                    ['courrier_id' => $courrier->id, 'annee' => $annee],
                    ['annee' => $annee]
                );
            }

            CourrierHistorique::query()->firstOrCreate(
                ['courrier_id' => $courrier->id, 'action' => ActionHistorique::Depot],
                ['user_id' => $admin->id, 'action' => ActionHistorique::Depot]
            );

            HistoriqueAffectation::query()->firstOrCreate(
                ['courrier_id' => $courrier->id, 'commentaire' => 'Dépôt initial du courrier'],
                [
                    'user_id' => $admin->id,
                    'unite_source_id' => null,
                    'unite_destination_id' => $unite->id,
                    'ancien_responsable_id' => null,
                    'nouveau_responsable_id' => null,
                    'action_type' => null,
                ]
            );

            CourrierSequence::query()->updateOrCreate(
                ['annee' => 2026, 'unite_id' => $unite->id],
                ['dernier_numero' => max(
                    CourrierSequence::query()->where('annee', 2026)->where('unite_id', $unite->id)->value('dernier_numero') ?? 0,
                    $data['seq']
                )]
            );
        }
    }
}
