<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Unite extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'code',
        'type_unite',
        'parent_id',
        'description',
        'actif',
    ];

    protected function casts(): array
    {
        return [
            'actif' => 'boolean',
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
