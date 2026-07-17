<?php

namespace App\Models;

use App\Enums\TypeImpot;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourrierImpot extends Model
{
    use HasFactory;

    protected $fillable = [
        'courrier_id',
        'impot',
    ];

    protected function casts(): array
    {
        return [
            'impot' => TypeImpot::class,
        ];
    }

    public function courrier(): BelongsTo
    {
        return $this->belongsTo(Courrier::class);
    }
}
