<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourrierAnnee extends Model
{
    use HasFactory;

    protected $fillable = [
        'courrier_id',
        'annee',
    ];

    protected function casts(): array
    {
        return [
            'annee' => 'integer',
        ];
    }

    public function courrier(): BelongsTo
    {
        return $this->belongsTo(Courrier::class);
    }
}
