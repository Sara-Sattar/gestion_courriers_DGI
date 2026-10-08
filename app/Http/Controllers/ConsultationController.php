<?php

namespace App\Http\Controllers;

use App\Enums\ModeLivraison;
use App\Enums\StatutCourrier;
use App\Exports\StatistiquesCourriersExport;
use App\Models\BureauOrdre;
use App\Models\CategorieCourrier;
use App\Models\Courrier;
use App\Models\TypeCourrier;
use App\Models\Unite;
use App\Services\DelegationContext;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ConsultationController extends Controller
{
    /** @var list<string> */
    private const AXES = [
        'synthese',
        'type', 'bo', 'us', 'mois',
        'categorie', 'statut', 'priorite', 'reponse', 'livraison',
    ];

    public function __construct(
        private readonly DelegationContext $delegationContext,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('menu.consultation');

        $periode = $request->input('periode', 'mois');
        $axe = $request->input('axe', 'synthese');
        if (! in_array($axe, self::AXES, true)) {
            $axe = 'synthese';
        }

        [$debut, $fin, $libellePeriode] = $this->bornePeriode($periode, $request);
        $filtrePeriode = $this->requeteFiltrePeriode($debut, $fin);

        $totalPeriode = (clone $filtrePeriode)->count();
        $statistiques = $this->statistiquesCompletes($filtrePeriode, $totalPeriode);
        $indicateurs = $this->indicateursPerformance($filtrePeriode, $totalPeriode);
        $comparaison = $this->comparaisonPeriodePrecedente($periode, $debut, $fin, $totalPeriode);
        $croisement = $this->croisementTypeStatut($filtrePeriode);

        $titresAxe = $this->titresAxes();
        $lignesAxe = $axe === 'synthese'
            ? collect()
            : ($statistiques[$this->cleStat($axe)] ?? collect());

        return view('consultation.index', [
            'periode' => $periode,
            'libellePeriode' => $libellePeriode,
            'debut' => $debut,
            'fin' => $fin,
            'axe' => $axe,
            'titreAxe' => $titresAxe[$axe],
            'totalPeriode' => $totalPeriode,
            'lignesAxe' => $lignesAxe,
            'indicateurs' => $indicateurs,
            'comparaison' => $comparaison,
            'statistiques' => $statistiques,
            'croisement' => $croisement,
            'maxAxe' => max(1, (int) $lignesAxe->max('total')),
            'maxMois' => max(1, (int) $statistiques['par_mois']->max('total')),
        ]);
    }

    public function exporter(Request $request): StreamedResponse|Response|BinaryFileResponse|JsonResponse
    {
        $this->authorize('stats.exporter');

        $format = $request->input('format', 'csv');
        $periode = $request->input('periode', 'mois');
        [$debut, $fin, $libellePeriode] = $this->bornePeriode($periode, $request);
        $filtrePeriode = $this->requeteFiltrePeriode($debut, $fin);
        $totalPeriode = (clone $filtrePeriode)->count();
        $statistiques = $this->statistiquesCompletes($filtrePeriode, $totalPeriode);
        $indicateurs = $this->indicateursPerformance($filtrePeriode, $totalPeriode);
        $comparaison = $this->comparaisonPeriodePrecedente($periode, $debut, $fin, $totalPeriode);
        $croisement = $this->croisementTypeStatut($filtrePeriode);

        $payload = array_merge([
            'periode' => $periode,
            'libelle_periode' => $libellePeriode,
            'debut' => $debut?->format('Y-m-d'),
            'fin' => $fin?->format('Y-m-d'),
            'total' => $totalPeriode,
            'indicateurs' => $indicateurs,
            'comparaison' => $comparaison,
            'croisement_type_statut' => $croisement,
        ], $statistiques);

        $stamp = now()->format('Ymd-His');

        return match ($format) {
            'json' => response()->json($payload),
            'xlsx' => Excel::download(
                new StatistiquesCourriersExport($payload),
                "statistiques-courriers-{$stamp}.xlsx"
            ),
            'pdf' => Pdf::loadView('consultation.pdf-statistiques', $payload)
                ->download("statistiques-courriers-{$stamp}.pdf"),
            default => $this->exporterCsv($payload, $stamp),
        };
    }

    /**
     * @return array<string, string>
     */
    private function titresAxes(): array
    {
        return [
            'synthese' => 'Synthèse',
            'type' => 'Type de courrier',
            'bo' => 'Bureau d’ordre (BO)',
            'us' => 'Unité de service (US)',
            'mois' => 'Mois',
            'categorie' => 'Catégorie',
            'statut' => 'Statut',
            'priorite' => 'Priorité',
            'reponse' => 'Nécessite une réponse',
            'livraison' => 'Mode de délivrance',
        ];
    }

    private function cleStat(string $axe): string
    {
        return match ($axe) {
            'bo' => 'par_bo',
            'us' => 'par_us',
            'mois' => 'par_mois',
            'categorie' => 'par_categorie',
            'statut' => 'par_statut',
            'priorite' => 'par_priorite',
            'reponse' => 'par_reponse',
            'livraison' => 'par_livraison',
            default => 'par_type',
        };
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function exporterCsv(array $payload, string $stamp): StreamedResponse
    {
        return response()->streamDownload(function () use ($payload) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, ['Statistiques DGI', $payload['libelle_periode']], ';');
            fputcsv($handle, ['Total courriers', $payload['total']], ';');
            fputcsv($handle, [], ';');

            fputcsv($handle, ['Indicateurs de performance'], ';');
            foreach ($payload['indicateurs'] as $cle => $valeur) {
                fputcsv($handle, [$cle, is_array($valeur) ? json_encode($valeur) : $valeur], ';');
            }
            fputcsv($handle, [], ';');

            foreach ($this->sectionsExport($payload) as $titre => $lignes) {
                fputcsv($handle, [$titre], ';');
                fputcsv($handle, ['Libellé', 'Code', 'Nombre', 'Pourcentage'], ';');
                foreach ($lignes as $ligne) {
                    fputcsv($handle, [
                        $ligne['libelle'],
                        $ligne['code'] ?? '',
                        $ligne['total'],
                        $ligne['pourcentage'].' %',
                    ], ';');
                }
                fputcsv($handle, [], ';');
            }

            fclose($handle);
        }, "statistiques-courriers-{$stamp}.csv", [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, Collection>
     */
    private function sectionsExport(array $payload): array
    {
        return [
            'Par type de courrier' => $payload['par_type'],
            'Par bureau d’ordre (BO)' => $payload['par_bo'],
            'Par unité de service (US)' => $payload['par_us'],
            'Par mois' => $payload['par_mois'],
            'Par catégorie' => $payload['par_categorie'],
            'Par statut' => $payload['par_statut'],
            'Par priorité' => $payload['par_priorite'],
            'Nécessite une réponse' => $payload['par_reponse'],
            'Par mode de délivrance' => $payload['par_livraison'],
        ];
    }

    /**
     * @return array<string, Collection>
     */
    private function statistiquesCompletes(Builder $filtrePeriode, int $totalPeriode): array
    {
        return [
            'par_type' => $this->repartitionTypes($filtrePeriode, $totalPeriode),
            'par_bo' => $this->repartitionBo($filtrePeriode, $totalPeriode),
            'par_us' => $this->repartitionUs($filtrePeriode, $totalPeriode),
            'par_mois' => $this->repartitionMois($filtrePeriode, $totalPeriode),
            'par_categorie' => $this->repartitionCategories($filtrePeriode, $totalPeriode),
            'par_statut' => $this->repartitionStatuts($filtrePeriode, $totalPeriode),
            'par_priorite' => $this->repartitionPriorites($filtrePeriode, $totalPeriode),
            'par_reponse' => $this->repartitionReponse($filtrePeriode, $totalPeriode),
            'par_livraison' => $this->repartitionLivraison($filtrePeriode, $totalPeriode),
        ];
    }

    /**
     * @return array<string, int|float|null>
     */
    private function indicateursPerformance(Builder $filtrePeriode, int $totalPeriode): array
    {
        $statutsEnCours = [
            StatutCourrier::Depose->value,
            StatutCourrier::Receptionne->value,
            StatutCourrier::Reaffecte->value,
            StatutCourrier::Suspendu->value,
            StatutCourrier::TraiteFavorable->value,
            StatutCourrier::Traite->value,
        ];

        $statutsTermines = [
            StatutCourrier::Cloture->value,
            StatutCourrier::Archive->value,
        ];

        $enCours = (clone $filtrePeriode)->whereIn('statut', $statutsEnCours)->count();
        $termines = (clone $filtrePeriode)->whereIn('statut', $statutsTermines)->count();
        $rejetes = (clone $filtrePeriode)->where('statut', StatutCourrier::Rejete)->count();
        $annules = (clone $filtrePeriode)->where('statut', StatutCourrier::Annule)->count();
        $necessiteReponse = (clone $filtrePeriode)->where('necessite_reponse', true)->count();

        return [
            'total' => $totalPeriode,
            'en_cours' => $enCours,
            'termines' => $termines,
            'rejetes' => $rejetes,
            'annules' => $annules,
            'necessite_reponse' => $necessiteReponse,
            'taux_cloture' => $totalPeriode > 0 ? round(($termines / $totalPeriode) * 100, 1) : 0.0,
            'delai_reception_jours' => $this->delaiMoyenJours($filtrePeriode, 'date_depot', 'date_reception'),
            'delai_traitement_jours' => $this->delaiMoyenJours($filtrePeriode, 'date_reception', 'date_traitement'),
            'delai_delivrance_jours' => $this->delaiMoyenJours($filtrePeriode, 'date_traitement', 'date_delivrance'),
        ];
    }

    /**
     * @return array{disponible: bool, total_precedent: ?int, variation_pct: ?float, libelle: ?string}
     */
    private function comparaisonPeriodePrecedente(
        string $periode,
        ?Carbon $debut,
        ?Carbon $fin,
        int $totalPeriode,
    ): array {
        if ($periode === 'tout' || ! $debut || ! $fin) {
            return [
                'disponible' => false,
                'total_precedent' => null,
                'variation_pct' => null,
                'libelle' => null,
            ];
        }

        $dureeSecondes = max(1, (int) $debut->diffInSeconds($fin));
        $finPrec = $debut->copy()->subSecond();
        $debutPrec = $finPrec->copy()->subSeconds($dureeSecondes);

        $totalPrecedent = $this->requeteFiltrePeriode($debutPrec, $finPrec)->count();
        $variation = $totalPrecedent > 0
            ? round((($totalPeriode - $totalPrecedent) / $totalPrecedent) * 100, 1)
            : ($totalPeriode > 0 ? 100.0 : 0.0);

        return [
            'disponible' => true,
            'total_precedent' => $totalPrecedent,
            'variation_pct' => $variation,
            'libelle' => $debutPrec->format('d/m/Y').' → '.$finPrec->format('d/m/Y'),
        ];
    }

    /**
     * Matrice type (top 5) × principaux statuts.
     *
     * @return array{types: list<string>, statuts: list<array{value: string, label: string}>, cellules: array<string, array<string, int>>}
     */
    private function croisementTypeStatut(Builder $filtrePeriode): array
    {
        $topTypes = (clone $filtrePeriode)
            ->select(DB::raw('type_courrier_id as cle'), DB::raw('count(*) as total'))
            ->whereNotNull('type_courrier_id')
            ->groupBy('type_courrier_id')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $typeIds = $topTypes->pluck('cle')->filter()->all();
        $types = TypeCourrier::query()->whereIn('id', $typeIds)->get()->keyBy('id');

        $statutsAffiches = [
            StatutCourrier::Depose,
            StatutCourrier::Receptionne,
            StatutCourrier::TraiteFavorable,
            StatutCourrier::Rejete,
            StatutCourrier::Cloture,
        ];

        $cellules = [];
        $nomsTypes = [];

        foreach ($typeIds as $typeId) {
            $nom = $types->get($typeId)?->nom ?? 'Type #'.$typeId;
            $nomsTypes[] = $nom;
            $cellules[$nom] = [];

            foreach ($statutsAffiches as $statut) {
                $cellules[$nom][$statut->value] = (clone $filtrePeriode)
                    ->where('type_courrier_id', $typeId)
                    ->where('statut', $statut)
                    ->count();
            }
        }

        return [
            'types' => $nomsTypes,
            'statuts' => collect($statutsAffiches)->map(fn (StatutCourrier $s) => [
                'value' => $s->value,
                'label' => $s->label(),
            ])->all(),
            'cellules' => $cellules,
        ];
    }

    private function delaiMoyenJours(Builder $filtrePeriode, string $debutCol, string $finCol): ?float
    {
        $driver = DB::connection()->getDriverName();

        $expression = $driver === 'sqlite'
            ? "AVG(julianday(date({$finCol})) - julianday(date({$debutCol})))"
            : "AVG(DATEDIFF({$finCol}, DATE({$debutCol})))";

        $moyenne = (clone $filtrePeriode)
            ->whereNotNull($debutCol)
            ->whereNotNull($finCol)
            ->select(DB::raw("{$expression} as moyenne"))
            ->value('moyenne');

        return $moyenne !== null ? round((float) $moyenne, 1) : null;
    }

    /**
     * @return Collection<int, array{libelle: string, code: ?string, total: int, pourcentage: float}>
     */
    private function repartitionTypes(Builder $filtrePeriode, int $totalPeriode): Collection
    {
        $lignes = (clone $filtrePeriode)
            ->select(DB::raw('type_courrier_id as cle'), DB::raw('count(*) as total'))
            ->groupBy('type_courrier_id')
            ->orderByDesc('total')
            ->get();

        $types = TypeCourrier::query()
            ->whereIn('id', $lignes->pluck('cle')->filter())
            ->get()
            ->keyBy('id');

        return $lignes->map(function ($ligne) use ($types, $totalPeriode) {
            $type = $ligne->cle ? $types->get($ligne->cle) : null;

            return $this->ligneStat($type?->nom ?? 'Non renseigné', null, (int) $ligne->total, $totalPeriode);
        })->values();
    }

    /**
     * @return Collection<int, array{libelle: string, code: ?string, total: int, pourcentage: float}>
     */
    private function repartitionBo(Builder $filtrePeriode, int $totalPeriode): Collection
    {
        $lignes = (clone $filtrePeriode)
            ->leftJoin('users', 'courriers.user_id', '=', 'users.id')
            ->leftJoin('bureaux_ordre', 'users.bureau_ordre_id', '=', 'bureaux_ordre.id')
            ->select(DB::raw('bureaux_ordre.id as cle'), DB::raw('count(courriers.id) as total'))
            ->groupBy('bureaux_ordre.id')
            ->orderByDesc('total')
            ->get();

        $bureaux = BureauOrdre::query()
            ->whereIn('id', $lignes->pluck('cle')->filter())
            ->get()
            ->keyBy('id');

        return $lignes->map(function ($ligne) use ($bureaux, $totalPeriode) {
            $bo = $ligne->cle ? $bureaux->get($ligne->cle) : null;

            return $this->ligneStat(
                $bo?->libelle ?? 'Non renseigné',
                $bo?->code,
                (int) $ligne->total,
                $totalPeriode
            );
        })->values();
    }

    /**
     * @return Collection<int, array{libelle: string, code: ?string, total: int, pourcentage: float}>
     */
    private function repartitionUs(Builder $filtrePeriode, int $totalPeriode): Collection
    {
        $lignes = (clone $filtrePeriode)
            ->select(DB::raw('unite_actuelle_id as cle'), DB::raw('count(*) as total'))
            ->groupBy('unite_actuelle_id')
            ->orderByDesc('total')
            ->get();

        $unites = Unite::query()
            ->whereIn('id', $lignes->pluck('cle')->filter())
            ->get()
            ->keyBy('id');

        return $lignes->map(function ($ligne) use ($unites, $totalPeriode) {
            $unite = $ligne->cle ? $unites->get($ligne->cle) : null;

            return $this->ligneStat(
                $unite?->nom ?? 'Non renseigné',
                $unite?->code,
                (int) $ligne->total,
                $totalPeriode
            );
        })->values();
    }

    /**
     * @return Collection<int, array{libelle: string, code: ?string, total: int, pourcentage: float}>
     */
    private function repartitionMois(Builder $filtrePeriode, int $totalPeriode): Collection
    {
        $expression = $this->expressionMois('courriers.date_depot');

        $lignes = (clone $filtrePeriode)
            ->whereNotNull('courriers.date_depot')
            ->select(DB::raw("{$expression} as cle"), DB::raw('count(*) as total'))
            ->groupByRaw($expression)
            ->orderBy('cle')
            ->get();

        return $lignes->map(function ($ligne) use ($totalPeriode) {
            $cle = (string) $ligne->cle;
            $libelle = $cle;
            try {
                $libelle = ucfirst(
                    Carbon::createFromFormat('Y-m', $cle)->locale('fr')->translatedFormat('F Y')
                );
            } catch (\Throwable) {
                // garder la clé brute
            }

            return $this->ligneStat($libelle, $cle, (int) $ligne->total, $totalPeriode);
        })->values();
    }

    /**
     * @return Collection<int, array{libelle: string, code: ?string, total: int, pourcentage: float}>
     */
    private function repartitionCategories(Builder $filtrePeriode, int $totalPeriode): Collection
    {
        $lignes = (clone $filtrePeriode)
            ->select(DB::raw('categorie_courrier_id as cle'), DB::raw('count(*) as total'))
            ->groupBy('categorie_courrier_id')
            ->orderByDesc('total')
            ->get();

        $categories = CategorieCourrier::query()
            ->whereIn('id', $lignes->pluck('cle')->filter())
            ->get()
            ->keyBy('id');

        return $lignes->map(function ($ligne) use ($categories, $totalPeriode) {
            $cat = $ligne->cle ? $categories->get($ligne->cle) : null;

            return $this->ligneStat($cat?->nom ?? 'Non renseigné', null, (int) $ligne->total, $totalPeriode);
        })->values();
    }

    /**
     * @return Collection<int, array{libelle: string, code: ?string, total: int, pourcentage: float}>
     */
    private function repartitionStatuts(Builder $filtrePeriode, int $totalPeriode): Collection
    {
        $lignes = (clone $filtrePeriode)
            ->select(DB::raw('statut as cle'), DB::raw('count(*) as total'))
            ->groupBy('statut')
            ->orderByDesc('total')
            ->get();

        return $lignes->map(function ($ligne) use ($totalPeriode) {
            $statut = StatutCourrier::tryFrom((string) $ligne->cle);
            $ligneStat = $this->ligneStat(
                $statut?->label() ?? (string) $ligne->cle,
                (string) $ligne->cle,
                (int) $ligne->total,
                $totalPeriode
            );
            $ligneStat['lien'] = route('dashboard', ['statut' => (string) $ligne->cle]);

            return $ligneStat;
        })->values();
    }

    /**
     * @return Collection<int, array{libelle: string, code: ?string, total: int, pourcentage: float}>
     */
    private function repartitionPriorites(Builder $filtrePeriode, int $totalPeriode): Collection
    {
        $lignes = (clone $filtrePeriode)
            ->select(DB::raw('priorite as cle'), DB::raw('count(*) as total'))
            ->groupBy('priorite')
            ->orderByDesc('total')
            ->get();

        return $lignes->map(function ($ligne) use ($totalPeriode) {
            $cle = $ligne->cle;
            $libelle = $cle ? ucfirst((string) $cle) : 'Non définie';

            return $this->ligneStat($libelle, $cle ? (string) $cle : null, (int) $ligne->total, $totalPeriode);
        })->values();
    }

    /**
     * @return Collection<int, array{libelle: string, code: ?string, total: int, pourcentage: float}>
     */
    private function repartitionReponse(Builder $filtrePeriode, int $totalPeriode): Collection
    {
        $lignes = (clone $filtrePeriode)
            ->select(DB::raw('necessite_reponse as cle'), DB::raw('count(*) as total'))
            ->groupBy('necessite_reponse')
            ->orderByDesc('total')
            ->get();

        return $lignes->map(function ($ligne) use ($totalPeriode) {
            $oui = (bool) $ligne->cle;

            return $this->ligneStat(
                $oui ? 'Nécessite une réponse' : 'Sans réponse attendue',
                $oui ? 'oui' : 'non',
                (int) $ligne->total,
                $totalPeriode
            );
        })->values();
    }

    /**
     * @return Collection<int, array{libelle: string, code: ?string, total: int, pourcentage: float}>
     */
    private function repartitionLivraison(Builder $filtrePeriode, int $totalPeriode): Collection
    {
        $baseDelivres = (clone $filtrePeriode)->whereNotNull('mode_livraison');
        $totalDelivres = (clone $baseDelivres)->count();

        $lignes = $baseDelivres
            ->select(DB::raw('mode_livraison as cle'), DB::raw('count(*) as total'))
            ->groupBy('mode_livraison')
            ->orderByDesc('total')
            ->get();

        return $lignes->map(function ($ligne) use ($totalDelivres) {
            $mode = ModeLivraison::tryFrom((string) $ligne->cle);

            return $this->ligneStat(
                $mode?->label() ?? (string) $ligne->cle,
                (string) $ligne->cle,
                (int) $ligne->total,
                $totalDelivres > 0 ? $totalDelivres : 1
            );
        })->values();
    }

    /**
     * @return array{libelle: string, code: ?string, total: int, pourcentage: float}
     */
    private function ligneStat(string $libelle, ?string $code, int $total, int $totalPeriode): array
    {
        return [
            'libelle' => $libelle,
            'code' => $code,
            'total' => $total,
            'pourcentage' => $totalPeriode > 0 ? round(($total / $totalPeriode) * 100, 1) : 0.0,
        ];
    }

    private function expressionMois(string $colonne): string
    {
        return DB::connection()->getDriverName() === 'sqlite'
            ? "strftime('%Y-%m', {$colonne})"
            : "DATE_FORMAT({$colonne}, '%Y-%m')";
    }

    private function requeteFiltrePeriode(?Carbon $debut, ?Carbon $fin): Builder
    {
        return $this->requetePerimetre()->when(
            $debut && $fin,
            fn (Builder $q) => $q->whereBetween('courriers.date_depot', [
                $debut->copy()->startOfDay(),
                $fin->copy()->endOfDay(),
            ])
        );
    }

    private function requetePerimetre(): Builder
    {
        $user = $this->delegationContext->sujet();
        $hierarchyIds = $user?->unite?->hierarchy_ids ?? [];

        $query = Courrier::query();

        if (! empty($hierarchyIds)) {
            $query->whereIn('courriers.unite_actuelle_id', $hierarchyIds);
        }

        return $query;
    }

    /**
     * @return array{0: ?Carbon, 1: ?Carbon, 2: string}
     */
    private function bornePeriode(string $periode, Request $request): array
    {
        return match ($periode) {
            'semaine' => [
                now()->startOfWeek(),
                now()->endOfWeek(),
                'Cette semaine',
            ],
            'annee' => [
                now()->startOfYear(),
                now()->endOfYear(),
                'Année '.now()->year,
            ],
            'tout' => [
                null,
                null,
                'Tout le parc',
            ],
            'personnalise' => [
                $request->filled('debut') ? Carbon::parse($request->input('debut'))->startOfDay() : now()->startOfMonth(),
                $request->filled('fin') ? Carbon::parse($request->input('fin'))->endOfDay() : now()->endOfDay(),
                'Période personnalisée',
            ],
            default => [
                now()->startOfMonth(),
                now()->endOfMonth(),
                ucfirst(now()->locale('fr')->translatedFormat('F Y')),
            ],
        };
    }
}
