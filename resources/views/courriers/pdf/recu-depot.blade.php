<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Reçu de dépôt - {{ $courrier->numero }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #1f2937;
            font-size: 12px;
            line-height: 1.5;
        }

        .copy {
            border: 1px solid #cbd5e1;
            padding: 18px;
            margin-bottom: 22px;
        }

        .header {
            text-align: center;
            margin-bottom: 18px;
        }

        .header h1,
        .header h2,
        .header p {
            margin: 0;
        }

        .header h1 {
            font-size: 16px;
            text-transform: uppercase;
        }

        .header h2 {
            font-size: 14px;
            margin-top: 4px;
        }

        .subtitle {
            margin-top: 8px;
            font-size: 13px;
            font-weight: bold;
        }

        .meta {
            width: 100%;
            margin-bottom: 14px;
        }

        .meta td {
            padding: 4px 0;
            vertical-align: top;
        }

        .section-title {
            background: #e5e7eb;
            padding: 6px 8px;
            font-weight: bold;
            margin-bottom: 8px;
            text-transform: uppercase;
        }

        .details {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }

        .details td {
            border: 1px solid #e5e7eb;
            padding: 7px 8px;
            vertical-align: top;
        }

        .label {
            width: 28%;
            font-weight: bold;
            background: #f8fafc;
        }

        .footer {
            margin-top: 22px;
        }

        .signature {
            width: 100%;
            margin-top: 28px;
        }

        .signature td {
            width: 50%;
            vertical-align: top;
        }

        .signature-box {
            margin-top: 40px;
            border-top: 1px solid #94a3b8;
            width: 80%;
            padding-top: 6px;
        }
    </style>
</head>
<body>
    @foreach (['Exemplaire contribuable', 'Exemplaire administration'] as $exemplaire)
        <div class="copy">
            <div class="header">
                <h1>{{ config('dgi.direction_generale') }}</h1>
                <h2>{{ config('dgi.direction_regionale') }}</h2>
                <p class="subtitle">Reçu de dépôt - {{ $exemplaire }}</p>
            </div>

            <table class="meta">
                <tr>
                    <td><strong>Numéro du courrier :</strong> {{ $courrier->numero }}</td>
                    <td style="text-align: right;"><strong>Date :</strong> {{ $courrier->date_depot->format('d/m/Y H:i') }}</td>
                </tr>
                <tr>
                    <td><strong>Statut :</strong> {{ $courrier->statut->label() }}</td>
                    <td style="text-align: right;"><strong>Unité :</strong> {{ $courrier->uniteDestinataire->nom }} ({{ $courrier->code_unite }})</td>
                </tr>
            </table>

            <div class="section-title">Informations du contribuable</div>
            <table class="details">
                <tr>
                    <td class="label">Nom / Raison sociale</td>
                    <td>{{ $courrier->contribuable->nom_raison_sociale }}</td>
                </tr>
                <tr>
                    <td class="label">IFU / CIN / ICE</td>
                    <td>
                        IFU : {{ $courrier->contribuable->ifu ?: '-' }} |
                        CIN : {{ $courrier->contribuable->cin ?: '-' }} |
                        ICE : {{ $courrier->contribuable->ice ?: '-' }}
                    </td>
                </tr>
                <tr>
                    <td class="label">Adresse</td>
                    <td>{{ $courrier->contribuable->adresse ?: '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Ville / Téléphone / Email</td>
                    <td>
                        Ville : {{ $courrier->contribuable->ville ?: '-' }} |
                        Tél : {{ $courrier->contribuable->telephone ?: '-' }} |
                        Email : {{ $courrier->contribuable->email ?: '-' }}
                    </td>
                </tr>
            </table>

            <div class="section-title">Informations du courrier</div>
            <table class="details">
                <tr>
                    <td class="label">Catégorie</td>
                    <td>{{ $courrier->categorie->nom }}</td>
                </tr>
                <tr>
                    <td class="label">Type de courrier</td>
                    <td>{{ $courrier->typeCourrier->nom }}</td>
                </tr>
                <tr>
                    <td class="label">Objet</td>
                    <td>{{ $courrier->objet }}</td>
                </tr>
                <tr>
                    <td class="label">Destination</td>
                    <td>{{ $courrier->uniteDestinataire->type_unite }} - {{ $courrier->uniteDestinataire->nom }}</td>
                </tr>
                <tr>
                    <td class="label">Impôts concernés</td>
                    <td>{{ $impots ?: '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Années concernées</td>
                    <td>{{ $annees ?: '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Remarque</td>
                    <td>{{ $courrier->remarque ?: 'Aucune remarque.' }}</td>
                </tr>
            </table>

            <div class="footer">
                Le présent reçu atteste le dépôt du courrier susmentionné auprès des services de la DGI.
            </div>

            <table class="signature">
                <tr>
                    <td>
                        <div><strong>Signature de l'administration</strong></div>
                        <div class="signature-box">Nom et cachet</div>
                    </td>
                    <td style="text-align: right;">
                        <div><strong>Signature du contribuable</strong></div>
                        <div class="signature-box" style="margin-left: auto;">Nom et signature</div>
                    </td>
                </tr>
            </table>
        </div>
    @endforeach
</body>
</html>
