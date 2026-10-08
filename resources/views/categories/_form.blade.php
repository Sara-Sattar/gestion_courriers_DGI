<x-erreurs-formulaire />

<div class="row g-3">
    <div class="col-md-6">
        <label for="nom" class="form-label">Nom de la catégorie <x-required /></label>
        <input
            type="text"
            class="form-control @error('nom') is-invalid @enderror"
            id="nom"
            name="nom"
            value="{{ old('nom', $categorie->nom ?? '') }}"
            placeholder="Attestation, Réclamation, Recours…"
            required
        >
        @error('nom')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12">
        <label for="description" class="form-label">Description</label>
        <textarea
            class="form-control @error('description') is-invalid @enderror"
            id="description"
            name="description"
            rows="3"
            placeholder="À quoi sert cette catégorie ? (facultatif)"
        >{{ old('description', $categorie->description ?? '') }}</textarea>
        @error('description')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12">
        <label class="form-label">Nécessitant une réponse <x-required /></label>
        <div class="d-flex gap-3">
            <div class="form-check">
                <input class="form-check-input" type="radio" name="necessite_reponse" id="necessite-reponse-oui" value="1"
                       @checked((string) old('necessite_reponse', (int) ($categorie->necessite_reponse ?? true)) === '1')>
                <label class="form-check-label" for="necessite-reponse-oui">Oui</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="necessite_reponse" id="necessite-reponse-non" value="0"
                       @checked((string) old('necessite_reponse', (int) ($categorie->necessite_reponse ?? true)) === '0')>
                <label class="form-check-label" for="necessite-reponse-non">Non</label>
            </div>
        </div>
        <div class="form-text">
            Appliqué automatiquement aux courriers enregistrés dans cette catégorie. Si Non, le courrier est archivé / classé dès le dépôt.
        </div>
        @error('necessite_reponse')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="actif" name="actif" value="1" @checked(old('actif', $categorie->actif ?? true))>
            <label class="form-check-label" for="actif">Catégorie active</label>
        </div>
        <div class="form-text">
            Une catégorie inactive n'est plus proposée à l'enregistrement, mais reste visible sur les courriers existants.
        </div>
    </div>
</div>
