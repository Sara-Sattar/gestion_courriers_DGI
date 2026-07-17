<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CategorieCourrier extends Model
{
    use HasFactory;

    protected $table = 'categories_courrier';

    protected $fillable = [
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

    public function typesCourrier(): HasMany
    {
        return $this->hasMany(TypeCourrier::class, 'categorie_courrier_id');
    }

    public function courriers(): HasMany
    {
        return $this->hasMany(Courrier::class, 'categorie_courrier_id');
    }

    public function scopeActives($query)
    {
        return $query->where('actif', true);
    }
}
