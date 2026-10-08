@php
    $utilisateur = $user ?? null;
    $estModification = $utilisateur !== null;
    $parcoursEtapes = $parcoursEtapes ?? false;

    $roleSelectionne = old('role_id', $utilisateur?->role_id);
    $uniteSelectionnee = old('unite_id', $utilisateur?->unite_id);
@endphp

<x-erreurs-formulaire />

<div
    class="user-form-etape"
    data-etape-panel="1"
    @if ($parcoursEtapes) data-parcours="1" @endif
>
    <div class="formulaire-section">
        <div class="formulaire-section-titre">Identité</div>

        <div class="row g-3">
            <div class="col-md-4">
                <label for="ppr" class="form-label">PPR</label>
                <input
                    type="text"
                    id="ppr"
                    name="ppr"
                    class="form-control @error('ppr') is-invalid @enderror"
                    value="{{ old('ppr', $utilisateur?->ppr) }}"
                    placeholder="Ex. 123456"
                    data-etape1-required
                >
                @error('ppr')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-4">
                <label for="name" class="form-label">Nom <x-required /></label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    class="form-control @error('name') is-invalid @enderror"
                    value="{{ old('name', $utilisateur?->name) }}"
                    placeholder="Nom de famille"
                    autocomplete="family-name"
                    required
                    data-etape1-required
                >
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-4">
                <label for="prenom" class="form-label">Prénom</label>
                <input
                    type="text"
                    id="prenom"
                    name="prenom"
                    class="form-control @error('prenom') is-invalid @enderror"
                    value="{{ old('prenom', $utilisateur?->prenom) }}"
                    placeholder="Prénom"
                    autocomplete="given-name"
                >
                @error('prenom')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="email" class="form-label">Adresse e-mail <x-required /></label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    class="form-control @error('email') is-invalid @enderror"
                    value="{{ old('email', $utilisateur?->email) }}"
                    placeholder="prenom.nom@dgi.ma"
                    autocomplete="email"
                    required
                    data-etape1-required
                >
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="form-text">Sert d'identifiant de connexion.</div>
            </div>
        </div>
    </div>

    <div class="formulaire-section">
        <div class="formulaire-section-titre">
            Mot de passe
            <button type="button" class="btn btn-sm btn-outline-secondary" id="generer-mot-de-passe">
                <i class="bi bi-shuffle me-1"></i>Générer
            </button>
        </div>

        <div class="row g-3">
            <div class="col-md-6">
                @if ($estModification)
                    <label for="password" class="form-label">Nouveau mot de passe</label>
                @else
                    <label for="password" class="form-label">Mot de passe <x-required /></label>
                @endif
                <div class="input-group">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-control @error('password') is-invalid @enderror"
                        autocomplete="new-password"
                        @unless ($estModification) required data-etape1-required @endunless
                    >
                    <button
                        type="button"
                        class="btn btn-outline-secondary basculer-mot-de-passe"
                        data-cible="password"
                        aria-label="Afficher le mot de passe"
                    >
                        <i class="bi bi-eye" aria-hidden="true"></i>
                    </button>
                </div>
                @error('password')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
                @if ($estModification)
                    <div class="form-text">Laissez vide pour conserver le mot de passe actuel.</div>
                @endif
            </div>

            <div class="col-md-6">
                @if ($estModification)
                    <label for="password_confirmation" class="form-label">Confirmer le mot de passe</label>
                @else
                    <label for="password_confirmation" class="form-label">Confirmer le mot de passe <x-required /></label>
                @endif
                <div class="input-group">
                    <input
                        type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        class="form-control"
                        autocomplete="new-password"
                        @unless ($estModification) required data-etape1-required @endunless
                    >
                    <button
                        type="button"
                        class="btn btn-outline-secondary basculer-mot-de-passe"
                        data-cible="password_confirmation"
                        aria-label="Afficher la confirmation du mot de passe"
                    >
                        <i class="bi bi-eye" aria-hidden="true"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="formulaire-section">
        <div class="formulaire-section-titre">Affectation</div>

        <div class="row g-3">
            <div class="col-md-6">
                <label for="role_id" class="form-label">Profil principal <x-required /></label>
                <select
                    id="role_id"
                    name="role_id"
                    class="form-select @error('role_id') is-invalid @enderror"
                    required
                    data-etape1-required
                    data-url-permissions="{{ url('/roles') }}"
                >
                    <option value="">Sélectionner un profil</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->id }}" @selected((string) $roleSelectionne === (string) $role->id)>
                            {{ $role->name }}
                        </option>
                    @endforeach
                </select>
                @error('role_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="form-text">Profil CDC principal (Admin, Accueil, Traitement, Consultation…).</div>
            </div>

            <div class="col-md-6">
                <label for="role_ids" class="form-label">Profils additionnels</label>
                @php
                    $rolesAdditionnels = collect(old('role_ids', $utilisateur?->roles?->pluck('id')->all() ?? []))
                        ->map(fn ($id) => (int) $id)
                        ->reject(fn ($id) => (string) $id === (string) $roleSelectionne)
                        ->all();
                @endphp
                <select
                    id="role_ids"
                    name="role_ids[]"
                    class="form-select @error('role_ids') is-invalid @enderror"
                    multiple
                    data-placeholder="Rechercher ou sélectionner un profil"
                >
                    @foreach ($roles as $role)
                        <option value="{{ $role->id }}" @selected(in_array((int) $role->id, $rolesAdditionnels, true))>
                            {{ $role->name }}
                        </option>
                    @endforeach
                </select>
                @error('role_ids')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
                <div class="form-text">Cumulez plusieurs profils CDC (hors profil principal).</div>
            </div>

            <div class="col-md-6">
                <label for="unite_id" class="form-label">Unité de rattachement <x-required /></label>
                <select
                    id="unite_id"
                    name="unite_id"
                    class="form-select @error('unite_id') is-invalid @enderror"
                    data-unite-selectionnee="{{ $uniteSelectionnee }}"
                    data-url-unites="{{ url('/unites/role') }}"
                    required
                    data-etape1-required
                    disabled
                >
                    <option value="">Sélectionnez d'abord un rôle</option>
                </select>
                @error('unite_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-6">
                <label for="bureau_ordre_id" class="form-label">Bureau d'ordre</label>
                <select
                    id="bureau_ordre_id"
                    name="bureau_ordre_id"
                    class="form-select @error('bureau_ordre_id') is-invalid @enderror"
                >
                    <option value="">— Aucun —</option>
                    @foreach (($bureauxOrdre ?? []) as $bo)
                        <option value="{{ $bo->id }}" @selected((string) old('bureau_ordre_id', $utilisateur?->bureau_ordre_id) === (string) $bo->id)>
                            {{ $bo->code }} — {{ $bo->libelle }}@if($bo->ville) ({{ $bo->ville }})@endif
                        </option>
                    @endforeach
                </select>
                @error('bureau_ordre_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="form-text">Rattachement au B.O. (cahier des charges).</div>
            </div>
        </div>
    </div>
</div>

<div
    class="user-form-etape"
    data-etape-panel="2"
    @if ($parcoursEtapes) hidden @endif
>
    <div class="formulaire-section @if ($parcoursEtapes) formulaire-section--sans-separateur @endif" id="section-permissions">
        <div class="formulaire-section-titre">Droits d'accès</div>
        <p class="text-muted small mb-3">
            Choisissez les menus visibles et les actions autorisées pour cet utilisateur.
        </p>
        @error('permissions')
            <div class="alert alert-danger py-2">{{ $message }}</div>
        @enderror
        @include('permissions._checklist', [
            'permissions' => $permissions,
            'groupes' => $groupes,
            'permissionIdsActives' => $permissionIdsActives,
            'verrouAdmin' => false,
            'prefixId' => 'user-perm',
        ])
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.6.2/dist/css/tom-select.bootstrap5.min.css">
<link rel="stylesheet" href="{{ asset('css/formulaires.css') }}">
<link rel="stylesheet" href="{{ asset('css/permissions.css') }}">
<link rel="stylesheet" href="{{ asset('css/users-admin.css') }}">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.6.2/dist/js/tom-select.complete.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('select[multiple][data-placeholder]').forEach((select) => {
        new TomSelect(select, {
            plugins: ['remove_button', 'clear_button'],
            placeholder: select.dataset.placeholder,
            hidePlaceholder: true,
            maxOptions: null,
            hideSelected: true,
            render: {
                no_results: () => '<div class="no-results">Aucun résultat</div>',
            },
        });
    });

    document.querySelectorAll('.basculer-mot-de-passe').forEach((bouton) => {
        const champ = document.getElementById(bouton.dataset.cible);
        if (!champ) return;

        const libelleAfficher = bouton.getAttribute('aria-label');
        const libelleMasquer = libelleAfficher.replace('Afficher', 'Masquer');

        bouton.addEventListener('click', () => {
            const afficher = champ.type === 'password';
            champ.type = afficher ? 'text' : 'password';
            bouton.querySelector('.bi').className = afficher ? 'bi bi-eye-slash' : 'bi bi-eye';
            bouton.setAttribute('aria-label', afficher ? libelleMasquer : libelleAfficher);
            champ.focus();
        });
    });

    const boutonGenerer = document.getElementById('generer-mot-de-passe');
    if (boutonGenerer) {
        const jeux = [
            'ABCDEFGHJKLMNPQRSTUVWXYZ',
            'abcdefghijkmnopqrstuvwxyz',
            '23456789',
            '!@#$%&*?-+',
        ];
        const entierAleatoire = (max) => {
            const tirage = new Uint32Array(1);
            crypto.getRandomValues(tirage);
            return tirage[0] % max;
        };

        boutonGenerer.addEventListener('click', () => {
            const caracteres = jeux.map((jeu) => jeu[entierAleatoire(jeu.length)]);
            while (caracteres.length < 14) {
                const jeu = jeux[entierAleatoire(jeux.length)];
                caracteres.push(jeu[entierAleatoire(jeu.length)]);
            }
            for (let i = caracteres.length - 1; i > 0; i--) {
                const j = entierAleatoire(i + 1);
                [caracteres[i], caracteres[j]] = [caracteres[j], caracteres[i]];
            }
            const motDePasse = caracteres.join('');
            ['password', 'password_confirmation'].forEach((id) => {
                const champ = document.getElementById(id);
                champ.value = motDePasse;
                champ.type = 'text';
            });
            document.querySelectorAll('.basculer-mot-de-passe').forEach((bouton) => {
                bouton.querySelector('.bi').className = 'bi bi-eye-slash';
            });
            document.getElementById('password')?.dispatchEvent(new Event('input', { bubbles: true }));
        });
    }

    const champRole = document.getElementById('role_id');
    const champUnite = document.getElementById('unite_id');
    const urlUnites = champUnite.dataset.urlUnites;
    const urlPermissionsBase = champRole.dataset.urlPermissions;
    let uniteAPreselectionner = champUnite.dataset.uniteSelectionnee || null;
    const estCreation = @json(! $estModification);
    let ignorerProchainChargementDroits = !estCreation && !@json((bool) old('role_id'));

    const remplacerOptions = (texte) => {
        champUnite.innerHTML = '';
        champUnite.appendChild(new Option(texte, ''));
    };

    const appliquerPermissions = (ids) => {
        const selection = new Set((ids || []).map(String));
        document.querySelectorAll('#section-permissions input[name="permissions[]"]').forEach((caseACocher) => {
            if (caseACocher.disabled) return;
            caseACocher.checked = selection.has(String(caseACocher.value));
        });
    };

    const chargerDroitsProfil = (roleId) => {
        if (!roleId || !urlPermissionsBase) return Promise.resolve();

        return fetch(`${urlPermissionsBase}/${roleId}/permissions`, { headers: { Accept: 'application/json' } })
            .then((reponse) => {
                if (!reponse.ok) throw new Error(reponse.status);
                return reponse.json();
            })
            .then((donnees) => appliquerPermissions(donnees.permission_ids))
            .catch(() => {});
    };

    const chargerUnites = (roleId) => {
        if (!roleId) {
            champUnite.disabled = true;
            remplacerOptions("Sélectionnez d'abord un rôle");
            return;
        }

        champUnite.disabled = true;
        remplacerOptions('Chargement des unités…');

        fetch(`${urlUnites}/${roleId}`, { headers: { Accept: 'application/json' } })
            .then((reponse) => {
                if (!reponse.ok) throw new Error(reponse.status);
                return reponse.json();
            })
            .then((unites) => {
                if (!unites.length) {
                    remplacerOptions('Aucune unité disponible pour ce rôle');
                    return;
                }
                remplacerOptions('Sélectionner une unité');
                unites.forEach((unite) => {
                    const option = new Option(`${unite.nom} (${unite.code})`, unite.id);
                    option.selected = String(uniteAPreselectionner) === String(unite.id);
                    champUnite.appendChild(option);
                });
                champUnite.disabled = false;
                champUnite.dispatchEvent(new Event('change', { bubbles: true }));
            })
            .catch(() => remplacerOptions('Impossible de charger les unités'));
    };

    champRole.addEventListener('change', () => {
        uniteAPreselectionner = null;
        chargerUnites(champRole.value);
        if (!ignorerProchainChargementDroits) {
            chargerDroitsProfil(champRole.value);
        }
        ignorerProchainChargementDroits = false;
    });

    document.querySelectorAll('[data-toggle-groupe]').forEach((bouton) => {
        bouton.addEventListener('click', () => {
            const groupe = bouton.dataset.toggleGroupe;
            const cases = [...document.querySelectorAll(`#section-permissions input[data-groupe="${groupe}"]:not(:disabled)`)];
            if (!cases.length) return;
            const toutCocher = cases.some((c) => !c.checked);
            cases.forEach((c) => { c.checked = toutCocher; });
        });
    });

    chargerUnites(champRole.value);

    if (estCreation && champRole.value && !@json((bool) old('permissions'))) {
        chargerDroitsProfil(champRole.value);
    }
});
</script>
@endpush
