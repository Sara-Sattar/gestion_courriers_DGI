<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TypeCourrier extends Model
{
    use HasFactory;

    protected $table = 'types_courrier';

    protected $fillable = [
        'categorie_courrier_id',
        'nom',
        'description',
        'actif',
    ];

    protected function casts(): array
    {
        return [
            'actif' => 'boolean',
        ];
    }

    public function categorie(): BelongsTo
    {
        return $this->belongsTo(CategorieCourrier::class, 'categorie_courrier_id');
    }

    public function courriers(): HasMany
    {
        return $this->hasMany(Courrier::class, 'type_courrier_id');
    }

    public function scopeActifs($query)
    {
        return $query->where('actif', true);
    }
}
