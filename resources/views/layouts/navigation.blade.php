<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom navbar-dgi">
    <div class="container-fluid px-4">
        <a class="navbar-brand py-0" href="{{ route('dashboard') }}">
            <x-application-logo />
        </a>

        <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#navMenu"
            aria-controls="navMenu"
            aria-expanded="false"
            aria-label="Menu"
        >
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav me-auto">
                @can('menu.dashboard')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                            <i class="bi bi-house-door"></i>
                            <span>Accueil</span>
                        </a>
                    </li>
                @endcan

                @can('menu.reception')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('courriers.reception.index') ? 'active' : '' }}" href="{{ route('courriers.reception.index') }}">
                            <i class="bi bi-inbox"></i>
                            <span>Réception</span>
                        </a>
                    </li>
                @endcan

                @can('menu.traitement')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('courriers.traitement.*') ? 'active' : '' }}" href="{{ route('courriers.traitement.index') }}">
                            <i class="bi bi-gear"></i>
                            <span>Traitement</span>
                        </a>
                    </li>
                @endcan

                @can('menu.delivrance')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('courriers.delivrance.*') ? 'active' : '' }}" href="{{ route('courriers.delivrance.index') }}">
                            <i class="bi bi-box-seam"></i>
                            <span>Délivrance</span>
                        </a>
                    </li>
                @endcan

                @can('menu.consultation')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('consultation.*') ? 'active' : '' }}" href="{{ route('consultation.index') }}">
                            <i class="bi bi-bar-chart"></i>
                            <span>Statistiques</span>
                        </a>
                    </li>
                @endcan

                @can('menu.utilisateurs')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">
                            <i class="bi bi-people"></i>
                            <span>Gestion des utilisateurs</span>
                        </a>
                    </li>
                @endcan

                @can('delegations.voir')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('delegations.*') ? 'active' : '' }}" href="{{ route('delegations.index') }}">
                            <i class="bi bi-person-bounding-box"></i>
                            <span>Gestion · Délégations</span>
                        </a>
                    </li>
                @endcan

                @can('menu.referentiels')
                    @php
                        $referentielsActifs = request()->routeIs('categories.*')
                            || request()->routeIs('types-courrier.*')
                            || request()->routeIs('unites.*');
                    @endphp
                    <li class="nav-item dropdown">
                        <a
                            class="nav-link dropdown-toggle {{ $referentielsActifs ? 'active' : '' }}"
                            href="#"
                            role="button"
                            data-bs-toggle="dropdown"
                            aria-expanded="false"
                        >
                            <i class="bi bi-sliders"></i>
                            <span>Référentiels</span>
                        </a>
                        <ul class="dropdown-menu">
                            @can('categories.gerer')
                                <li>
                                    <a class="dropdown-item" href="{{ route('categories.index') }}">
                                        <i class="bi bi-tags me-2"></i>Catégories de courrier
                                    </a>
                                </li>
                            @endcan
                            @can('types_courrier.gerer')
                                <li>
                                    <a class="dropdown-item" href="{{ route('types-courrier.index') }}">
                                        <i class="bi bi-list-ul me-2"></i>Types de courrier
                                    </a>
                                </li>
                            @endcan
                            @can('unites.gerer')
                                <li>
                                    <a class="dropdown-item" href="{{ route('unites.index') }}">
                                        <i class="bi bi-diagram-3 me-2"></i>Unités destinataires
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endcan
            </ul>

            <ul class="navbar-nav ms-lg-auto align-items-lg-center gap-lg-1">
                @if (($delegationsActivesRemplacant ?? collect())->isNotEmpty())
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-briefcase"></i>
                            <span class="d-none d-xl-inline nav-espace-actif">
                                Espace actif :
                                <span class="espace-nom">{{ $libelleEspaceActif ?? Auth::user()->name }}</span>
                            </span>
                            <span class="d-xl-none">Espace</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li class="dropdown-header">Changer d'espace</li>
                            <li>
                                <form method="POST" action="{{ route('espace-travail.choisir') }}">
                                    @csrf
                                    <input type="hidden" name="espace" value="self">
                                    <button type="submit" class="dropdown-item {{ empty($espaceEnRemplacement) ? 'active' : '' }}">
                                        <i class="bi bi-person me-2"></i>Mon espace
                                    </button>
                                </form>
                            </li>
                            @foreach ($delegationsActivesRemplacant as $delegation)
                                <li>
                                    <form method="POST" action="{{ route('espace-travail.choisir') }}">
                                        @csrf
                                        <input type="hidden" name="espace" value="{{ $delegation->id }}">
                                        <button type="submit" class="dropdown-item {{ ($delegationContext->delegationActive()?->id ?? null) === $delegation->id ? 'active' : '' }}">
                                            <i class="bi bi-person-check me-2"></i>{{ $delegation->libelleRemplacement() }}
                                        </button>
                                    </form>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                @endif

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle"></i>
                        <span>{{ Auth::user()->name }}</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-person-gear me-2"></i>Profil</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item">
                                    <i class="bi bi-box-arrow-right me-2"></i>Déconnexion
                                </button>
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
