<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BureauOrdre extends Model
{
    protected $table = 'bureaux_ordre';

    protected $fillable = [
        'code',
        'libelle',
        'ville',
        'unite_id',
        'actif',
    ];

    protected function casts(): array
    {
        return [
            'actif' => 'boolean',
        ];
    }

    public function unite(): BelongsTo
    {
        return $this->belongsTo(Unite::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'bureau_ordre_id');
    }

    public function scopeActifs($query)
    {
        return $query->where('actif', true);
    }
}
