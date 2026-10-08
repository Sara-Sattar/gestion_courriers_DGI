<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Reçu de dépôt - {{ $courrier->numero }}</title>
    <style>
        @page {
            margin: 9mm 9mm;
        }

        body {
            color: #212529;
            font-family: "DejaVu Sans", sans-serif;
            font-size: 6.5pt;
            line-height: 1.18;
            margin: 0;
            padding: 0;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        /* Les deux exemplaires doivent tenir sur une seule feuille A4. */
        .exemplaire-bloc {
            border: 0.5pt solid #adb5bd;
            padding: 5pt 8pt 4pt;
        }

        .ligne-decoupe {
            border-top: 1pt dashed #343a40;
            font-size: 0;
            line-height: 0;
            margin: 7pt 0;
        }

        /* ---------- En-tête ---------- */

        .entete td {
            vertical-align: middle;
        }

        .entete-logo {
            text-align: center;
            width: 12%;
        }

        .entete-logo img {
            height: 25pt;
        }

        .entete-titre {
            padding: 0 4pt;
            text-align: center;
        }

        .titre-principal {
            font-size: 8pt;
            font-weight: bold;
            letter-spacing: 0.6pt;
            text-transform: uppercase;
        }

        .titre-secondaire {
            font-size: 6.7pt;
            font-weight: bold;
            letter-spacing: 0.3pt;
            text-transform: uppercase;
        }

        .entete-numero {
            text-align: right;
            width: 29%;
        }

        .numero-boite {
            border: 0.9pt solid #212529;
            font-size: 10pt;
            font-weight: bold;
            padding: 2.5pt 4pt;
            text-align: center;
            word-wrap: break-word;
        }

        .numero-type {
            font-size: 7pt;
            font-weight: bold;
            letter-spacing: 0.3pt;
            padding-top: 2pt;
            text-transform: uppercase;
        }

        .numero-date {
            color: #495057;
            font-size: 5.8pt;
        }

        .filet-entete {
            border-top: 1.1pt solid #212529;
            font-size: 0;
            line-height: 0;
            margin-top: 4pt;
        }

        /* ---------- Sections ---------- */

        .bandeau {
            background-color: #e9ecef;
            border-bottom: 0.4pt solid #ced4da;
            font-size: 6.2pt;
            font-weight: bold;
            letter-spacing: 0.7pt;
            margin-top: 4pt;
            padding: 2pt 6pt;
            text-transform: uppercase;
        }

        .champs td {
            border-bottom: 0.35pt solid #f1f3f5;
            font-size: 6.5pt;
            padding: 1.2pt 6pt;
            vertical-align: top;
        }

        .champs tr:last-child td {
            border-bottom: none;
        }

        .champ-libelle {
            width: 27%;
        }

        .champ-fort {
            font-weight: bold;
        }

        .jeton {
            background-color: #f1f3f5;
            border: 0.35pt solid #dee2e6;
            color: #495057;
            font-size: 6.2pt;
            margin-right: 3pt;
            padding: 0.8pt 3.5pt;
        }

        /* ---------- Pied ---------- */

        .signatures {
            margin-top: 9pt;
        }

        .signatures td {
            vertical-align: bottom;
        }

        .signature-colonne {
            width: 40%;
        }

        .signature-espace {
            width: 20%;
        }

        .signature-trait {
            border-top: 0.8pt solid #212529;
            font-size: 0;
            line-height: 0;
        }

        .signature-libelle {
            color: #495057;
            font-size: 6pt;
            padding-top: 2pt;
            text-align: center;
        }

        .mention-exemplaire {
            border-top: 0.4pt dotted #ced4da;
            color: #6c757d;
            font-size: 6.2pt;
            letter-spacing: 1.1pt;
            margin-top: 4pt;
            padding-top: 3pt;
            text-align: center;
            text-transform: uppercase;
        }
    </style>
</head>
<body>
@php
    $contribuable = $courrier->contribuable;

    $exemplaires = [
        ['mention' => 'Exemplaire à remettre au contribuable', 'administration' => false],
        ['mention' => 'Exemplaire pour l\'administration', 'administration' => true],
    ];
@endphp

@foreach ($exemplaires as $index => $exemplaire)
    @if ($index > 0)
        <div class="ligne-decoupe"></div>
    @endif

    <div class="exemplaire-bloc">
        <table class="entete">
            <tr>
                <td class="entete-logo">
                    @if ($logo)
                        <img src="{{ $logo }}" alt="">
                    @endif
                </td>
                <td class="entete-titre">
                    <div class="titre-principal">{{ config('dgi.direction_generale') }}</div>
                    <div class="titre-secondaire">{{ config('dgi.direction_regionale') }}</div>
                </td>
                <td class="entete-logo">
                    @if ($logo)
                        <img src="{{ $logo }}" alt="">
                    @endif
                </td>
                <td class="entete-numero">
                    <div class="numero-boite">N° {{ $courrier->numero }}</div>
                    <div class="numero-type">Reçu de dépôt</div>
                    <div class="numero-date">Date: {{ $courrier->date_depot->format('d/m/Y H:i') }}</div>
                </td>
            </tr>
        </table>

        <div class="filet-entete"></div>

        <div class="bandeau">Informations du contribuable</div>
        <table class="champs">
            <tr>
                <td class="champ-libelle">Nom / RS</td>
                <td class="champ-fort">{{ $contribuable->nom_raison_sociale }}</td>
            </tr>
            <tr>
                <td class="champ-libelle">IFU</td>
                <td>{{ $contribuable->ifu ?: '-' }}</td>
            </tr>
            <tr>
                <td class="champ-libelle">N° TP / TH</td>
                <td>{{ $contribuable->numero_tp ?: '-' }} / {{ $contribuable->numero_th ?: '-' }}</td>
            </tr>
            <tr>
                <td class="champ-libelle">CIN</td>
                <td>{{ $contribuable->cin ?: '-' }}</td>
            </tr>
            <tr>
                <td class="champ-libelle">Adresse</td>
                <td>{{ $contribuable->adresse ?: '-' }}</td>
            </tr>
            <tr>
                <td class="champ-libelle">Ville</td>
                <td>{{ $contribuable->ville ?: '-' }}</td>
            </tr>
            <tr>
                <td class="champ-libelle">Téléphone</td>
                <td>{{ $contribuable->telephone ?: '-' }}</td>
            </tr>
            <tr>
                <td class="champ-libelle">Email</td>
                <td>{{ $contribuable->email ?: '-' }}</td>
            </tr>
        </table>

        <div class="bandeau">Détails du courrier</div>
        <table class="champs">
            <tr>
                <td class="champ-libelle">Catégorie</td>
                <td>{{ $courrier->categorie->nom }}</td>
            </tr>
            <tr>
                <td class="champ-libelle">Type de courrier</td>
                <td>{{ $courrier->typeCourrier->nom }}</td>
            </tr>
            <tr>
                <td class="champ-libelle">Unité de destination</td>
                <td>{{ $courrier->uniteDestinataire->nom }}</td>
            </tr>
            <tr>
                <td class="champ-libelle">Objet</td>
                <td>{{ $courrier->objet ?: '—' }}</td>
            </tr>
            <tr>
                <td class="champ-libelle">Impôts concernés</td>
                <td>
                    @forelse ($impots as $impot)
                        <span class="jeton">{{ $impot }}</span>
                    @empty
                        -
                    @endforelse
                </td>
            </tr>
            <tr>
                <td class="champ-libelle">Années</td>
                <td>
                    @forelse ($annees as $annee)
                        <span class="jeton">{{ $annee }}</span>
                    @empty
                        -
                    @endforelse
                </td>
            </tr>
            <tr>
                <td class="champ-libelle">Statut</td>
                <td class="champ-fort">{{ $courrier->statut->label() }}</td>
            </tr>
            <tr>
                <td class="champ-libelle">Remarque</td>
                <td>{{ $courrier->remarque ?: '-' }}</td>
            </tr>
            @if ($exemplaire['administration'])
                <tr>
                    <td class="champ-libelle">Réceptionné par</td>
                    <td>{{ $courrier->utilisateur->name }}</td>
                </tr>
            @endif
        </table>

        <table class="signatures">
            <tr>
                <td class="signature-colonne">
                    <div class="signature-trait"></div>
                    <div class="signature-libelle">
                        {{ $exemplaire['administration'] ? 'Signature du réceptionnaire' : '' }}
                    </div>
                </td>
                <td class="signature-espace"></td>
                <td class="signature-colonne">
                    <div class="signature-trait"></div>
                    <div class="signature-libelle">Cachet de la DRI</div>
                </td>
            </tr>
        </table>

        <div class="mention-exemplaire">&#9986; {{ $exemplaire['mention'] }}</div>
    </div>
@endforeach
</body>
</html>
