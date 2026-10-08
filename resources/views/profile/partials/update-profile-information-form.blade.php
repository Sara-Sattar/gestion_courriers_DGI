<section>
    <p class="text-muted small mb-4">
        Modifiez votre identité et votre adresse e-mail. Le profil et l'unité sont gérés par l'administrateur.
    </p>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}">
        @csrf
        @method('patch')

        <div class="row g-3">
            <div class="col-md-4">
                <label for="ppr" class="form-label fw-medium">PPR</label>
                <input
                    id="ppr"
                    name="ppr"
                    type="text"
                    class="form-control @error('ppr') is-invalid @enderror"
                    value="{{ old('ppr', $user->ppr) }}"
                    autocomplete="off"
                >
                @error('ppr')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="name" class="form-label fw-medium">Nom <x-required /></label>
                <input
                    id="name"
                    name="name"
                    type="text"
                    class="form-control @error('name') is-invalid @enderror"
                    value="{{ old('name', $user->name) }}"
                    required
                    autofocus
                    autocomplete="family-name"
                >
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="prenom" class="form-label fw-medium">Prénom</label>
                <input
                    id="prenom"
                    name="prenom"
                    type="text"
                    class="form-control @error('prenom') is-invalid @enderror"
                    value="{{ old('prenom', $user->prenom) }}"
                    autocomplete="given-name"
                >
                @error('prenom')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="email" class="form-label fw-medium">Adresse e-mail <x-required /></label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    class="form-control @error('email') is-invalid @enderror"
                    value="{{ old('email', $user->email) }}"
                    required
                    autocomplete="username"
                >
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
            <div class="alert alert-warning mt-3 mb-0 py-2 small">
                Votre adresse e-mail n'est pas vérifiée.
                <button form="send-verification" class="btn btn-link btn-sm p-0 align-baseline">
                    Renvoyer l'e-mail de vérification
                </button>
            </div>
            @if (session('status') === 'verification-link-sent')
                <p class="text-success small mt-2 mb-0">Un nouveau lien de vérification a été envoyé.</p>
            @endif
        @endif

        <div class="d-flex align-items-center gap-3 mt-4 pt-2">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-lg me-1"></i>Enregistrer
            </button>
            @if (session('status') === 'profile-updated')
                <span class="text-success small fw-medium">
                    <i class="bi bi-check-circle me-1"></i>Modifications enregistrées
                </span>
            @endif
        </div>
    </form>
</section>
