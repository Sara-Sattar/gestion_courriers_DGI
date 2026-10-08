<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Unite extends Model
{
    use HasFactory;

    /**
     * Niveau hiérarchique de chaque type d'unité : plus le nombre est petit,
     * plus l'unité est haute dans l'organisation.
     */
    public const NIVEAUX = [
        'DR' => 1,
        'Direction' => 1,
        'Service' => 2,
        'Brigade' => 2,
        'Subdivision' => 3,
        'RAF' => 3,
        'BET' => 3,
        'Bureau' => 3,
        'Centre' => 3,
        'Section' => 4,
        'Secteur' => 4,
    ];

    /** Préfixe du code attribué automatiquement, par type d'unité. */
    public const PREFIXES_CODE = [
        'DR' => 'DR',
        'Direction' => 'DIR',
        'Service' => 'SERV',
        'Brigade' => 'BRIG',
        'Subdivision' => 'SUB',
        'RAF' => 'RAF',
        'BET' => 'BET',
        'Bureau' => 'BUR',
        'Centre' => 'CTR',
        'Section' => 'SEC',
        'Secteur' => 'SECT',
    ];

    protected $fillable = [
        'nom',
        'code',
        'type_unite',
        'parent_id',
        'description',
        'ville',
        'actif',
        'est_bureau_ordre',
    ];

    protected function casts(): array
    {
        return [
            'actif' => 'boolean',
            'est_bureau_ordre' => 'boolean',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function enfants(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function courriersDestines(): HasMany
    {
        return $this->hasMany(Courrier::class, 'unite_destinataire_id');
    }

    public function courriersActuels(): HasMany
    {
        return $this->hasMany(Courrier::class, 'unite_actuelle_id');
    }

    public function sequences(): HasMany
    {
        return $this->hasMany(CourrierSequence::class, 'unite_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Responsable opérationnel de l'unité (chef de service, bureau…).
     * Exclut le rôle Admin lorsque plusieurs utilisateurs partagent la même unité.
     */
    public function responsable(): ?User
    {
        $roleName = match ($this->type_unite) {
            'Service' => 'Service',
            'Brigade' => 'Brigade',
            'Bureau' => 'Bureau',
            'Section' => 'Section',
            'Secteur' => 'Secteur',
            default => null,
        };

        if ($roleName) {
            $responsable = User::query()
                ->where('unite_id', $this->id)
                ->whereHas('role', fn ($query) => $query->where('name', $roleName))
                ->first();

            if ($responsable) {
                return $responsable;
            }
        }

        return User::query()
            ->where('unite_id', $this->id)
            ->whereHas('role', fn ($query) => $query->where('name', '!=', 'Admin'))
            ->orderBy('id')
            ->first();
    }

    /**
     * Types proposés au choix : la liste de référence, complétée par les types
     * déjà présents en base pour ne pas bloquer la modification de l'existant.
     *
     * @return list<string>
     */
    public static function typesDisponibles(): array
    {
        return self::query()
            ->distinct()
            ->pluck('type_unite')
            ->merge(array_keys(self::NIVEAUX))
            ->filter()
            ->unique()
            ->sortBy(fn (string $type) => [self::NIVEAUX[$type] ?? 99, $type])
            ->values()
            ->all();
    }

    public static function prefixeCode(string $typeUnite): string
    {
        if (isset(self::PREFIXES_CODE[$typeUnite])) {
            return self::PREFIXES_CODE[$typeUnite];
        }

        $lettres = preg_replace('/[^A-Z]/', '', Str::upper(Str::ascii($typeUnite)));

        return Str::substr($lettres !== '' ? $lettres : 'UNITE', 0, 4);
    }

    /**
     * Prochain code libre pour ce type, sous la forme PREFIXE + numéro sur
     * trois chiffres (BRIG001, BRIG002…).
     */
    public static function prochainCode(string $typeUnite): string
    {
        $prefixe = self::prefixeCode($typeUnite);

        $dernierNumero = self::query()
            ->where('code', 'like', $prefixe.'%')
            ->pluck('code')
            ->map(function (string $code) use ($prefixe) {
                preg_match('/^'.preg_quote($prefixe, '/').'(\d+)$/', $code, $correspondance);

                return isset($correspondance[1]) ? (int) $correspondance[1] : null;
            })
            ->filter()
            ->max() ?? 0;

        return $prefixe.str_pad((string) ($dernierNumero + 1), 3, '0', STR_PAD_LEFT);
    }

    /**
     * Types auxquels une unité du type donné peut être rattachée : ceux d'un
     * niveau strictement supérieur, plus le sien (un bureau peut dépendre d'un
     * autre bureau, mais un service ne dépend pas d'une brigade).
     *
     * @return list<string>
     */
    public static function typesParentsAutorises(string $typeUnite): array
    {
        $niveau = self::NIVEAUX[$typeUnite] ?? null;

        if ($niveau === null) {
            return array_keys(self::NIVEAUX);
        }

        $types = array_keys(array_filter(self::NIVEAUX, fn (int $autre) => $autre < $niveau));
        $types[] = $typeUnite;

        return array_values(array_unique($types));
    }

    /**
     * Unités pouvant servir de parent à une unité du type donné, hors elle-même
     * et sa propre descendance.
     *
     * @return Collection<int, self>
     */
    public static function parentsPossibles(?string $typeUnite, ?self $unite = null): Collection
    {
        // Sans type connu, aucun rattachement ne peut être proposé.
        if ($typeUnite === null || $typeUnite === '') {
            return new Collection;
        }

        $exclusions = $unite ? $unite->hierarchy_ids : [];

        return self::query()
            ->whereIn('type_unite', self::typesParentsAutorises($typeUnite))
            ->when($exclusions !== [], fn ($requete) => $requete->whereNotIn('id', $exclusions))
            ->orderBy('type_unite')
            ->orderBy('nom')
            ->get();
    }

    public function scopeActives($query)
    {
        return $query->where('actif', true);
    }

    public function scopeParType($query, string $typeUnite)
    {
        return $query->where('type_unite', $typeUnite);
    }

    public function scopeRacines($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Get all descendants (children, grandchildren, etc.)
     */
    public function getDescendantsAttribute(): Collection
    {
        $descendants = collect();
        $this->enfants->each(function ($enfant) use (&$descendants) {
            $descendants->push($enfant);
            $descendants = $descendants->merge($enfant->descendants);
        });
        return $descendants;
    }

    /**
     * Get all ancestors (parent, grandparent, etc.)
     */
    public function getAncestorsAttribute(): Collection
    {
        $ancestors = collect();
        $current = $this->parent;
        while ($current) {
            $ancestors->push($current);
            $current = $current->parent;
        }
        return $ancestors;
    }

    /**
     * Check if this unit is an ancestor of another unit
     */
    public function isAncestorOf(Unite $unite): bool
    {
        return $this->descendants->contains($unite);
    }

    /**
     * Check if this unit is a descendant of another unit
     */
    public function isDescendantOf(Unite $unite): bool
    {
        return $unite->descendants->contains($this);
    }

    /**
     * Get all unit IDs including this one and all descendants
     */
    public function getHierarchyIdsAttribute(): array
    {
        $ids = [$this->id];
        $this->descendants->each(function ($descendant) use (&$ids) {
            $ids[] = $descendant->id;
        });
        return $ids;
    }
}
