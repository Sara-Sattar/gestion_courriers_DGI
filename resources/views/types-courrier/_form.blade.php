<x-erreurs-formulaire />

<div class="row g-3">
    <div class="col-md-6">
        <label for="categorie_courrier_id" class="form-label">Catégorie <x-required /></label>
        <select class="form-select @error('categorie_courrier_id') is-invalid @enderror" id="categorie_courrier_id" name="categorie_courrier_id" required>
            <option value="">Sélectionner une catégorie</option>
            @foreach ($categories as $categorie)
                <option value="{{ $categorie->id }}" @selected((string) old('categorie_courrier_id', $type->categorie_courrier_id ?? '') === (string) $categorie->id)>
                    {{ $categorie->nom }}@unless ($categorie->actif) (inactive)@endunless
                </option>
            @endforeach
        </select>
        @error('categorie_courrier_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <div class="form-text">Un type appartient à une seule catégorie.</div>
    </div>

    <div class="col-md-6">
        <label for="nom" class="form-label">Nom du type <x-required /></label>
        <input
            type="text"
            class="form-control @error('nom') is-invalid @enderror"
            id="nom"
            name="nom"
            value="{{ old('nom', $type->nom ?? '') }}"
            placeholder="Attestation de régularité fiscale…"
            required
        >
        @error('nom')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <div class="form-text">Le nom doit être unique au sein de la catégorie.</div>
    </div>

    <div class="col-12">
        <label for="description" class="form-label">Description</label>
        <textarea
            class="form-control @error('description') is-invalid @enderror"
            id="description"
            name="description"
            rows="3"
            placeholder="Précisions sur ce type de courrier (facultatif)"
        >{{ old('description', $type->description ?? '') }}</textarea>
        @error('description')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="actif" name="actif" value="1" @checked(old('actif', $type->actif ?? true))>
            <label class="form-check-label" for="actif">Type actif</label>
        </div>
        <div class="form-text">
            Un type inactif n'est plus proposé à l'enregistrement, mais reste visible sur les courriers existants.
        </div>
    </div>
</div>
