<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'ppr',
        'name',
        'prenom',
        'email',
        'password',
        'role_id',
        'unite_id',
        'bureau_ordre_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /** Profils CDC additionnels (un utilisateur peut en cumuler plusieurs). */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }

    public function unite(): BelongsTo
    {
        return $this->belongsTo(Unite::class);
    }

    public function bureauOrdre(): BelongsTo
    {
        return $this->belongsTo(BureauOrdre::class, 'bureau_ordre_id');
    }

    /** Nom d'affichage CDC : Prénom + Nom. */
    public function nomComplet(): string
    {
        return trim(($this->prenom ? $this->prenom.' ' : '').$this->name);
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class)->withTimestamps();
    }

    public function courriersEnregistres(): HasMany
    {
        return $this->hasMany(Courrier::class, 'user_id');
    }

    public function historiquesCourrier(): HasMany
    {
        return $this->hasMany(CourrierHistorique::class, 'user_id');
    }

    public function delegationsCommeAbsent(): HasMany
    {
        return $this->hasMany(Delegation::class, 'agent_absent_id');
    }

    public function delegationsCommeRemplacant(): HasMany
    {
        return $this->hasMany(Delegation::class, 'agent_remplacant_id');
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('Admin');
    }

    public function hasRole(string $roleName): bool
    {
        if ($this->role?->name === $roleName) {
            return true;
        }

        if (! $this->relationLoaded('roles')) {
            $this->load('roles');
        }

        return $this->roles->contains('name', $roleName);
    }

    /**
     * Droits effectifs : permissions personnelles si définies, sinon union des profils.
     */
    public function hasPermission(string $code): bool
    {
        if (! $this->relationLoaded('permissions')) {
            $this->load('permissions');
        }

        if ($this->permissions->isNotEmpty()) {
            return $this->permissions->contains('code', $code);
        }

        if (! $this->relationLoaded('roles')) {
            $this->load('roles.permissions');
        }

        foreach ($this->roles as $role) {
            if ($role->hasPermission($code)) {
                return true;
            }
        }

        $role = $this->role;

        if (! $role) {
            return false;
        }

        if (! $role->relationLoaded('permissions')) {
            $role->load('permissions');
        }

        return $role->hasPermission($code);
    }

    /**
     * IDs des permissions à afficher dans le formulaire (personnelles ou héritées des profils).
     *
     * @return list<int>
     */
    public function permissionIdsEffectives(): array
    {
        if (! $this->relationLoaded('permissions')) {
            $this->load('permissions');
        }

        if ($this->permissions->isNotEmpty()) {
            return $this->permissions->pluck('id')->map(fn ($id) => (int) $id)->all();
        }

        if (! $this->relationLoaded('roles')) {
            $this->load('roles.permissions');
        }

        $ids = $this->roles
            ->flatMap(fn (Role $role) => $role->permissions->pluck('id'))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        if ($ids !== []) {
            return $ids;
        }

        if (! $this->role) {
            return [];
        }

        if (! $this->role->relationLoaded('permissions')) {
            $this->role->load('permissions');
        }

        return $this->role->permissions->pluck('id')->map(fn ($id) => (int) $id)->all();
    }
}
