<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Connexion — {{ config('app.name', 'Gestion DGI') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>
<body class="login-page">
    <div class="login-layout">
        <aside class="login-brand">
            <div class="login-brand-content">
                <div class="login-logo-box login-logo-box--brand">
                    <img
                        src="{{ asset(config('dgi.logo_navbar')) }}"
                        alt="Direction Générale des Impôts"
                        class="login-logo-img"
                    >
                </div>

                <div class="login-brand-body">
                    <p class="login-brand-subtitle">{{ config('dgi.direction_regionale') }}</p>
                    <div class="login-brand-divider"></div>
                    <p class="login-brand-app">Gestion des courriers</p>
                </div>
            </div>
            <p class="login-brand-footer">&copy; {{ date('Y') }} — DGI Marrakech</p>
        </aside>

        <main class="login-main">
            <div class="login-main-inner">
                <div class="login-mobile-header d-lg-none">
                    <div class="login-logo-box login-logo-box--mobile">
                        <img
                            src="{{ asset(config('dgi.logo_navbar')) }}"
                            alt="Direction Générale des Impôts"
                            class="login-logo-img"
                        >
                    </div>
                </div>

                <div class="login-card">
                    {{ $slot }}
                </div>

                <p class="login-page-footer">
                    Accès réservé au personnel autorisé.
                    En cas de problème, contactez l'administrateur système.
                </p>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
