<section>
    <p class="text-muted small mb-4">
        Utilisez un mot de passe long et sécurisé pour protéger votre compte.
    </p>

    <form method="post" action="{{ route('password.update') }}">
        @csrf
        @method('put')

        <div class="row g-3">
            <div class="col-12">
                <label for="update_password_current_password" class="form-label fw-medium">Mot de passe actuel <x-required /></label>
                <div class="profile-password-wrap">
                    <input
                        id="update_password_current_password"
                        name="current_password"
                        type="password"
                        class="form-control profile-password-input @error('current_password', 'updatePassword') is-invalid @enderror"
                        autocomplete="current-password"
                    >
                    <button
                        type="button"
                        class="profile-password-toggle"
                        data-target="update_password_current_password"
                        aria-label="Afficher le mot de passe actuel"
                    >
                        <i class="bi bi-eye" aria-hidden="true"></i>
                    </button>
                </div>
                @error('current_password', 'updatePassword')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="update_password_password" class="form-label fw-medium">Nouveau mot de passe <x-required /></label>
                <div class="profile-password-wrap">
                    <input
                        id="update_password_password"
                        name="password"
                        type="password"
                        class="form-control profile-password-input @error('password', 'updatePassword') is-invalid @enderror"
                        autocomplete="new-password"
                    >
                    <button
                        type="button"
                        class="profile-password-toggle"
                        data-target="update_password_password"
                        aria-label="Afficher le nouveau mot de passe"
                    >
                        <i class="bi bi-eye" aria-hidden="true"></i>
                    </button>
                </div>
                @error('password', 'updatePassword')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="update_password_password_confirmation" class="form-label fw-medium">Confirmer le mot de passe <x-required /></label>
                <div class="profile-password-wrap">
                    <input
                        id="update_password_password_confirmation"
                        name="password_confirmation"
                        type="password"
                        class="form-control profile-password-input @error('password_confirmation', 'updatePassword') is-invalid @enderror"
                        autocomplete="new-password"
                    >
                    <button
                        type="button"
                        class="profile-password-toggle"
                        data-target="update_password_password_confirmation"
                        aria-label="Afficher la confirmation du mot de passe"
                    >
                        <i class="bi bi-eye" aria-hidden="true"></i>
                    </button>
                </div>
                @error('password_confirmation', 'updatePassword')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="d-flex align-items-center gap-3 mt-4 pt-2">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-shield-lock me-1"></i>Mettre à jour le mot de passe
            </button>
            @if (session('status') === 'password-updated')
                <span class="text-success small fw-medium">
                    <i class="bi bi-check-circle me-1"></i>Mot de passe mis à jour
                </span>
            @endif
        </div>
    </form>
</section>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    function bindPasswordToggle(inputId, btnSelector) {
        var input = document.getElementById(inputId);
        var btn = document.querySelector(btnSelector);
        if (!input || !btn) {
            return;
        }
        var icon = btn.querySelector('.bi');
        var showLabel = btn.getAttribute('aria-label');
        var hideLabel = showLabel.replace('Afficher', 'Masquer');

        btn.addEventListener('click', function (e) {
            e.preventDefault();
            var show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            if (icon) {
                icon.className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
            }
            btn.setAttribute('aria-label', show ? hideLabel : showLabel);
            input.focus();
        });
    }

    bindPasswordToggle('update_password_current_password', '[data-target="update_password_current_password"]');
    bindPasswordToggle('update_password_password', '[data-target="update_password_password"]');
    bindPasswordToggle('update_password_password_confirmation', '[data-target="update_password_password_confirmation"]');
});
</script>
@endpush
