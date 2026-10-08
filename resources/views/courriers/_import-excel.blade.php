@php
    $resultatImport = session('import_resultat');
@endphp

@if ($resultatImport)
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="bi bi-clipboard-check me-2 text-primary"></i>Résultat du dernier import</h5>
        </div>
        <div class="card-body">
            @if (! empty($resultatImport['succes']))
                <h6 class="fw-semibold text-success mb-2">
                    <i class="bi bi-check-circle me-1"></i>{{ count($resultatImport['succes']) }} courrier(s) enregistré(s)
                </h6>
                <div class="table-responsive mb-4">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" style="width: 8rem;">Ligne</th>
                                <th scope="col">Numéro attribué</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($resultatImport['succes'] as $succes)
                                <tr>
                                    <td>{{ $succes['ligne'] }}</td>
                                    <td class="fw-semibold">{{ $succes['numero'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            @if (! empty($resultatImport['erreurs']))
                <h6 class="fw-semibold text-danger mb-2">
                    <i class="bi bi-exclamation-triangle me-1"></i>{{ count($resultatImport['erreurs']) }} ligne(s) non importée(s)
                </h6>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" style="width: 8rem;">Ligne</th>
                                <th scope="col">Motif du refus</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($resultatImport['erreurs'] as $erreur)
                                <tr>
                                    <td>{{ $erreur['ligne'] ?: '—' }}</td>
                                    <td>{{ $erreur['message'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p class="form-text mt-3 mb-0">
                    Corrigez ces lignes dans votre fichier, puis importez-le à nouveau.
                    Les courriers déjà enregistrés ne seront pas dupliqués si vous ne réimportez que les lignes corrigées.
                </p>
            @endif
        </div>
    </div>
@endif

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-1"><i class="bi bi-file-earmark-spreadsheet me-2 text-primary"></i>Importer un fichier Excel</h5>
        <p class="text-muted mb-0 small">Enregistrez plusieurs courriers en une seule opération à partir d'un tableau.</p>
    </div>
    <div class="card-body">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 p-3 mb-4 rounded border bg-light">
            <div>
                <p class="fw-semibold mb-1">Étape 1 — Téléchargez le modèle</p>
                <p class="mb-0 small text-muted">
                    Il contient les colonnes attendues, une ligne d'exemple, et une feuille
                    « Références » listant les catégories, types, codes d'unité et impôts acceptés.
                </p>
            </div>
            <a href="{{ route('courriers.import.modele') }}" class="btn btn-outline-primary flex-shrink-0">
                <i class="bi bi-download me-1"></i>Télécharger le modèle
            </a>
        </div>

        <p class="fw-semibold mb-1">Étape 2 — Déposez votre fichier rempli</p>
        <p class="small text-muted mb-3">
            Une ligne du tableau correspond à un courrier. Chaque ligne valide reçoit automatiquement
            son numéro d'enregistrement. Les lignes incorrectes sont signalées sans bloquer les autres.
        </p>

        <form method="POST" action="{{ route('courriers.import') }}" enctype="multipart/form-data" id="formulaire-import">
            @csrf

            <div
                class="depot-fichiers @error('fichier') is-invalid @enderror"
                id="depot-import"
                tabindex="0"
                role="button"
                aria-label="Sélectionner ou déposer le fichier Excel à importer"
            >
                <input
                    type="file"
                    class="visually-hidden"
                    id="fichier"
                    name="fichier"
                    accept=".xlsx,.xls,.csv"
                >
                <i class="bi bi-file-earmark-arrow-up depot-fichiers-icone" aria-hidden="true"></i>
                <p class="depot-fichiers-titre mb-1" id="depot-import-titre">Cliquez ou déposez votre fichier ici</p>
                <p class="depot-fichiers-aide mb-0">Excel (XLSX, XLS) ou CSV — 10 Mo maximum</p>
            </div>

            @error('fichier')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror

            <div class="d-flex justify-content-end gap-2 mt-4">
                <button type="submit" class="btn btn-primary" id="bouton-import" disabled>
                    <i class="bi bi-upload me-1"></i>Lancer l'import
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const zone = document.getElementById('depot-import');
    const input = document.getElementById('fichier');
    const titre = document.getElementById('depot-import-titre');
    const bouton = document.getElementById('bouton-import');

    if (!zone || !input) {
        return;
    }

    const titreParDefaut = titre.textContent;

    const majAffichage = () => {
        const fichier = input.files[0];
        titre.textContent = fichier ? fichier.name : titreParDefaut;
        bouton.disabled = !fichier;
    };

    zone.addEventListener('click', () => input.click());

    zone.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            input.click();
        }
    });

    input.addEventListener('change', majAffichage);

    ['dragenter', 'dragover'].forEach((evenement) => {
        zone.addEventListener(evenement, (event) => {
            event.preventDefault();
            zone.classList.add('depot-fichiers--survol');
        });
    });

    ['dragleave', 'drop'].forEach((evenement) => {
        zone.addEventListener(evenement, (event) => {
            event.preventDefault();
            zone.classList.remove('depot-fichiers--survol');
        });
    });

    zone.addEventListener('drop', (event) => {
        if (event.dataTransfer?.files?.length) {
            const transfert = new DataTransfer();
            transfert.items.add(event.dataTransfer.files[0]);
            input.files = transfert.files;
            majAffichage();
        }
    });

    majAffichage();
});
</script>
@endpush
