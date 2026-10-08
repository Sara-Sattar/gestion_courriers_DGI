<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Statistiques courriers</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #222; }
        h1 { font-size: 16px; margin: 0 0 4px; }
        h2 { font-size: 13px; margin: 18px 0 8px; }
        .meta { color: #555; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        th, td { border: 1px solid #ccc; padding: 5px 7px; text-align: left; }
        th { background: #f1f3f5; }
        td.num { text-align: right; }
        .ind { margin: 8px 0 14px; }
        .ind td { border: 0; padding: 2px 8px 2px 0; }
    </style>
</head>
<body>
    <h1>Statistiques des courriers</h1>
    <div class="meta">
        Période : {{ $libelle_periode }}
        @if ($debut && $fin)
            ({{ \Carbon\Carbon::parse($debut)->format('d/m/Y') }} → {{ \Carbon\Carbon::parse($fin)->format('d/m/Y') }})
        @endif
        — Total : {{ number_format($total, 0, ',', ' ') }}
    </div>

    @if (! empty($indicateurs))
        <h2>Indicateurs de performance</h2>
        <table class="ind">
            <tr><td>En cours</td><td class="num">{{ $indicateurs['en_cours'] }}</td></tr>
            <tr><td>Terminés</td><td class="num">{{ $indicateurs['termines'] }} ({{ $indicateurs['taux_cloture'] }} %)</td></tr>
            <tr><td>Nécessite réponse</td><td class="num">{{ $indicateurs['necessite_reponse'] }}</td></tr>
            <tr><td>Délai moyen réception</td><td class="num">{{ $indicateurs['delai_reception_jours'] ?? '—' }} j</td></tr>
            <tr><td>Délai moyen traitement</td><td class="num">{{ $indicateurs['delai_traitement_jours'] ?? '—' }} j</td></tr>
            <tr><td>Délai moyen délivrance</td><td class="num">{{ $indicateurs['delai_delivrance_jours'] ?? '—' }} j</td></tr>
            <tr><td>Rejetés</td><td class="num">{{ $indicateurs['rejetes'] }}</td></tr>
            <tr><td>Annulés</td><td class="num">{{ $indicateurs['annules'] }}</td></tr>
        </table>
    @endif

    @foreach ([
        'Par type de courrier' => $par_type,
        'Par bureau d’ordre (BO)' => $par_bo,
        'Par unité de service (US)' => $par_us,
        'Par mois' => $par_mois,
        'Par catégorie' => $par_categorie,
        'Par statut' => $par_statut,
        'Par priorité' => $par_priorite,
        'Nécessite une réponse' => $par_reponse,
        'Par mode de délivrance' => $par_livraison,
    ] as $titre => $lignes)
        <h2>{{ $titre }}</h2>
        <table>
            <thead>
                <tr>
                    <th>Libellé</th>
                    <th>Code</th>
                    <th>Nombre</th>
                    <th>%</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($lignes as $ligne)
                    <tr>
                        <td>{{ $ligne['libelle'] }}</td>
                        <td>{{ $ligne['code'] ?? '—' }}</td>
                        <td class="num">{{ $ligne['total'] }}</td>
                        <td class="num">{{ $ligne['pourcentage'] }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4">Aucune donnée</td></tr>
                @endforelse
            </tbody>
        </table>
    @endforeach
</body>
</html>
