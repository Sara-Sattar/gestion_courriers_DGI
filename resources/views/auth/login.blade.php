<x-guest-layout>
    <header class="login-header">
        <h1 class="login-title">Connexion</h1>
        <p class="login-lead">Accédez à la gestion des courriers.</p>
    </header>

    <x-auth-session-status class="mb-3" :status="session('status')" />

    @error('email')
        <div class="login-alert login-alert--error mb-3" role="alert">
            <i class="bi bi-exclamation-circle me-2"></i>{{ $message }}
        </div>
    @enderror

    <form method="POST" action="{{ route('login') }}" class="login-form" id="login-form">
        @csrf

        <div class="login-field mb-3">
            <label for="email" class="form-label">Adresse e-mail</label>
            <div class="login-input-wrap">
                <i class="bi bi-envelope login-input-icon" aria-hidden="true"></i>
                <input
                    id="email"
                    type="email"
                    name="email"
                    class="form-control login-input @error('email') is-invalid @enderror"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="prenom.nom@dgi.ma"
                >
            </div>
        </div>

        <div class="login-field mb-3">
            <label for="password" class="form-label">Mot de passe</label>
            <div class="login-input-wrap">
                <i class="bi bi-lock login-input-icon" aria-hidden="true"></i>
                <input
                    id="password"
                    type="password"
                    name="password"
                    class="form-control login-input login-input--password @error('password') is-invalid @enderror"
                    required
                    autocomplete="current-password"
                    placeholder="Votre mot de passe"
                >
                <button
                    type="button"
                    class="login-password-toggle"
                    id="toggle-password"
                    aria-label="Afficher le mot de passe"
                >
                    <i class="bi bi-eye" id="toggle-password-icon" aria-hidden="true"></i>
                </button>
            </div>
            @error('password')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-4">
            <div class="form-check mb-0">
                <input id="remember_me" type="checkbox" class="form-check-input" name="remember">
                <label for="remember_me" class="form-check-label">Rester connecté</label>
            </div>
        </div>

        <button type="submit" class="btn login-submit w-100" id="login-submit">
            <span class="login-submit-text">Se connecter</span>
            <span class="login-submit-loading d-none">
                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                Connexion…
            </span>
        </button>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var passwordInput = document.getElementById('password');
            var toggleBtn = document.getElementById('toggle-password');
            var toggleIcon = document.getElementById('toggle-password-icon');
            var form = document.getElementById('login-form');
            var submitBtn = document.getElementById('login-submit');

            if (toggleBtn && passwordInput && toggleIcon) {
                toggleBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    var show = passwordInput.type === 'password';
                    passwordInput.type = show ? 'text' : 'password';
                    toggleIcon.className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
                    toggleBtn.setAttribute('aria-label', show ? 'Masquer le mot de passe' : 'Afficher le mot de passe');
                    passwordInput.focus();
                });
            }

            if (form && submitBtn) {
                form.addEventListener('submit', function () {
                    submitBtn.disabled = true;
                    submitBtn.querySelector('.login-submit-text').classList.add('d-none');
                    submitBtn.querySelector('.login-submit-loading').classList.remove('d-none');
                });
            }
        });
    </script>
</x-guest-layout>
