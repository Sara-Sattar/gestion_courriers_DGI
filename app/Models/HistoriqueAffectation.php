<?php

namespace App\Models;

use App\Enums\ActionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistoriqueAffectation extends Model
{
    use HasFactory;

    protected $fillable = [
        'courrier_id',
        'user_id',
        'unite_source_id',
        'unite_destination_id',
        'ancien_responsable_id',
        'nouveau_responsable_id',
        'commentaire',
        'action_type',
        'motif',
    ];

    protected $casts = [
        'action_type' => ActionType::class,
    ];

    public function courrier(): BelongsTo
    {
        return $this->belongsTo(Courrier::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function uniteSource(): BelongsTo
    {
        return $this->belongsTo(Unite::class, 'unite_source_id');
    }

    public function uniteDestination(): BelongsTo
    {
        return $this->belongsTo(Unite::class, 'unite_destination_id');
    }

    public function ancienResponsable(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ancien_responsable_id');
    }

    public function nouveauResponsable(): BelongsTo
    {
        return $this->belongsTo(User::class, 'nouveau_responsable_id');
    }
}
