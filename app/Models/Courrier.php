<?php

namespace App\Models;

use App\Enums\StatutCourrier;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Courrier extends Model
{
    use HasFactory;

    protected $fillable = [
        'numero',
        'contribuable_id',
        'categorie_courrier_id',
        'type_courrier_id',
        'objet',
        'unite_destinataire_id',
        'unite_actuelle_id',
        'responsable_actuel_id',
        'code_unite',
        'annee_enregistrement',
        'sequence',
        'remarque',
        'statut',
        'date_depot',
        'user_id',
        'priorite',
        'date_reception',
        'heure_reception',
        'responsable_reception_id',
    ];

    protected function casts(): array
    {
        return [
            'statut' => StatutCourrier::class,
            'date_depot' => 'datetime',
            'date_reception' => 'date',
            'heure_reception' => 'datetime:H:i:s',
            'annee_enregistrement' => 'integer',
            'sequence' => 'integer',
        ];
    }

    public function contribuable(): BelongsTo
    {
        return $this->belongsTo(Contribuable::class);
    }

    public function categorie(): BelongsTo
    {
        return $this->belongsTo(CategorieCourrier::class, 'categorie_courrier_id');
    }

    public function typeCourrier(): BelongsTo
    {
        return $this->belongsTo(TypeCourrier::class, 'type_courrier_id');
    }

    public function uniteDestinataire(): BelongsTo
    {
        return $this->belongsTo(Unite::class, 'unite_destinataire_id');
    }

    public function uniteActuelle(): BelongsTo
    {
        return $this->belongsTo(Unite::class, 'unite_actuelle_id');
    }

    public function responsableActuel(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsable_actuel_id');
    }

    public function responsableReception(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsable_reception_id');
    }

    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function impots(): HasMany
    {
        return $this->hasMany(CourrierImpot::class);
    }

    public function annees(): HasMany
    {
        return $this->hasMany(CourrierAnnee::class);
    }

    public function piecesJointes(): HasMany
    {
        return $this->hasMany(PieceJointe::class)->orderBy('ordre');
    }

    public function historiques(): HasMany
    {
        return $this->hasMany(CourrierHistorique::class)->orderByDesc('created_at');
    }

    public function historiqueAffectations(): HasMany
    {
        return $this->hasMany(HistoriqueAffectation::class)->orderByDesc('created_at');
    }
}
