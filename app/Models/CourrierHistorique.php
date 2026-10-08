<?php

namespace App\Models;

use App\Enums\ActionHistorique;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourrierHistorique extends Model
{
    use HasFactory;

    protected $fillable = [
        'courrier_id',
        'user_id',
        'pour_compte_de_id',
        'action',
        'commentaire',
    ];

    protected function casts(): array
    {
        return [
            'action' => ActionHistorique::class,
        ];
    }

    public function courrier(): BelongsTo
    {
        return $this->belongsTo(Courrier::class);
    }

    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function pourCompteDe(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pour_compte_de_id');
    }
}
