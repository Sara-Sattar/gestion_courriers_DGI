{{--
  Checklist de permissions réutilisable (profil ou utilisateur).
  Variables : $permissions (Collection groupée), $groupes, $permissionIdsActives,
  $verrouAdmin (bool, optionnel), $prefixId (string, optionnel)
--}}
@php
    $verrouAdmin = $verrouAdmin ?? false;
    $prefixId = $prefixId ?? 'perm';
@endphp

@foreach ($permissions as $groupe => $liste)
    @php
        $meta = $groupes[$groupe] ?? ['label' => ucfirst($groupe), 'description' => ''];
    @endphp
    <div class="permissions-groupe mb-4">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <div>
                <h6 class="mb-0">{{ $meta['label'] }}</h6>
                @if (! empty($meta['description']))
                    <p class="text-muted small mb-0">{{ $meta['description'] }}</p>
                @endif
            </div>
            <button
                type="button"
                class="btn btn-sm btn-outline-secondary"
                data-toggle-groupe="{{ $groupe }}"
            >
                Tout cocher / décocher
            </button>
        </div>
        <div class="row g-2">
            @foreach ($liste as $permission)
                @php
                    $verrouille = $verrouAdmin && in_array($permission->code, ['roles.gerer', 'users.gerer'], true);
                    $coche = in_array($permission->id, $permissionIdsActives, true)
                        || in_array((string) $permission->id, old('permissions', []), true);
                @endphp
                <div class="col-md-6 col-xl-4">
                    <label class="permission-item" for="{{ $prefixId }}-{{ $permission->id }}">
                        <input
                            type="checkbox"
                            id="{{ $prefixId }}-{{ $permission->id }}"
                            name="permissions[]"
                            value="{{ $permission->id }}"
                            data-groupe="{{ $groupe }}"
                            data-permission-code="{{ $permission->code }}"
                            @checked($coche)
                            @disabled($verrouille)
                        >
                        <span>
                            <strong>{{ $permission->label }}</strong>
                            <small class="d-block text-muted">{{ $permission->code }}</small>
                        </span>
                    </label>
                    @if ($verrouille)
                        <input type="hidden" name="permissions[]" value="{{ $permission->id }}">
                    @endif
                </div>
            @endforeach
        </div>
    </div>
@endforeach
