<nav class="navbar navbar-expand-md navbar-light bg-white border-bottom shadow-sm">
    <div class="container-fluid px-4">
        <a class="navbar-brand" href="{{ route('dashboard') }}">
            <x-application-logo style="height: 36px;" />
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('dashboard') ? 'active fw-semibold' : '' }}" href="{{ route('dashboard') }}">
                        <i class="bi bi-speedometer2 me-1"></i>Tableau de bord
                    </a>
                </li>
                
                @if (Auth::user()->role && Auth::user()->role->name === 'Direction Régionale')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('courriers.*') ? 'active fw-semibold' : '' }}" href="{{ route('courriers.create') }}">
                            <i class="bi bi-envelope-plus me-1"></i>Enregistrer un courrier
                        </a>
                    </li>
                @endif

                @if (!Auth::user()->isAdmin())
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('courriers.reception.index') ? 'active fw-semibold' : '' }}" href="{{ route('courriers.reception.index') }}">
                            <i class="bi bi-inbox me-1"></i>Réception
                        </a>
                    </li>
                @endif
                
                @if (!Auth::user()->isAdmin())
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('consultation.index') ? 'active fw-semibold' : '' }}" href="{{ route('consultation.index') }}">
                            <i class="bi bi-search me-1"></i>Consultation
                        </a>
                    </li>
                @endif

                @if (Auth::user()->isAdmin())
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('users.*') ? 'active fw-semibold' : '' }}" href="{{ route('users.index') }}">
                            <i class="bi bi-people me-1"></i>Gestion des utilisateurs
                        </a>
                    </li>
                @endif
            </ul>

            <ul class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle me-1"></i>{{ Auth::user()->name }}
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
