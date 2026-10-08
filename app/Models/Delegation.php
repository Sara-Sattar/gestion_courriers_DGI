<?php

namespace App\Models;

use App\Enums\MotifDelegation;
use App\Enums\StatutDelegation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Delegation extends Model
{
    use HasFactory;

    protected $fillable = [
        'agent_absent_id',
        'agent_remplacant_id',
        'unite_id',
        'date_debut',
        'date_fin',
        'motif',
        'statut',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'date_debut' => 'date',
            'date_fin' => 'date',
            'motif' => MotifDelegation::class,
            'statut' => StatutDelegation::class,
        ];
    }

    public function agentAbsent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_absent_id');
    }

    public function agentRemplacant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_remplacant_id');
    }

    public function unite(): BelongsTo
    {
        return $this->belongsTo(Unite::class);
    }

    public function createur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActives(Builder $query): Builder
    {
        return $query->where('statut', StatutDelegation::Active);
    }

    public function scopeEnCours(Builder $query, $auJour = null): Builder
    {
        $jour = ($auJour ?? now())->toDateString();

        return $query->actives()
            ->whereDate('date_debut', '<=', $jour)
            ->whereDate('date_fin', '>=', $jour);
    }

    public function estEnCours($auJour = null): bool
    {
        if ($this->statut !== StatutDelegation::Active) {
            return false;
        }

        $jour = ($auJour ?? now())->startOfDay();

        return $this->date_debut->lte($jour) && $this->date_fin->gte($jour);
    }

    public function libelleRemplacement(): string
    {
        return 'Remplacement actif : '.$this->agentAbsent?->name;
    }
}
