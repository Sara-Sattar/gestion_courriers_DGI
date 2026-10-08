<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title') - {{ config('app.name', 'Gestion DGI') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Bootstrap 5 & Icons -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
        <link rel="stylesheet" href="{{ asset('css/navbar-dgi.css') }}">
        <link rel="stylesheet" href="{{ asset('css/delegations.css') }}">

        @stack('styles')

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-light font-sans antialiased">
        <div class="min-h-screen">
            @include('layouts.navigation')

            @if (! empty($espaceEnRemplacement))
                <div class="bandeau-remplacement" role="status">
                    <i class="bi bi-info-circle me-1"></i>
                    Mode <strong>Remplacement</strong> —
                    vous travaillez pour le compte de
                    <strong>{{ $delegationContext->sujet()?->name }}</strong>
                    (connecté en tant que {{ Auth::user()->name }}).
                </div>
            @endif

            <!-- Page Content -->
            <main class="py-3">
                <div class="container-fluid px-5">
                    <!-- En-tête de page -->
                    @hasSection('page-header')
                        <div class="row mb-4 align-items-center">
                            @yield('page-header')
                        </div>
                    @else
                        @if (View::hasSection('breadcrumb') || View::hasSection('title'))
                            <div class="row mb-4 align-items-center">
                                <div class="col">
                                    @hasSection('breadcrumb')
                                        <nav aria-label="breadcrumb">
                                            <ol class="breadcrumb mb-1">
                                                @yield('breadcrumb')
                                            </ol>
                                        </nav>
                                    @endif
                                    @hasSection('title')
                                        <h2 class="h3 mb-0 fw-bold">@yield('title')</h2>
                                    @endif
                                </div>
                            </div>
                        @endif
                    @endif

                    <!-- Flash messages -->
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @yield('content')
                </div>
            </main>
        </div>

        @include('delegations._modal-espace')

        <!-- Bootstrap Bundle with Popper -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        @stack('scripts')
    </body>
</html>
