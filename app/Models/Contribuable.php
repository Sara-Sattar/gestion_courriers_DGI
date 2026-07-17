<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contribuable extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom_raison_sociale',
        'ifu',
        'cin',
        'ice',
        'numero_tp',
        'numero_th',
        'adresse',
        'ville',
        'telephone',
        'email',
    ];

    public function courriers(): HasMany
    {
        return $this->hasMany(Courrier::class);
    }

    public function scopeRecherche($query, ?string $terme)
    {
        if (blank($terme)) {
            return $query;
        }

        $terme = trim($terme);

        return $query->where(function ($q) use ($terme) {
            $q->where('ifu', $terme)
                ->orWhere('cin', $terme)
                ->orWhere('ice', $terme)
                ->orWhere('nom_raison_sociale', 'like', "%{$terme}%");
        });
    }
}
