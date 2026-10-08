<?php

namespace App\Models;

use App\Enums\ModeLivraison;
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
        'necessite_reponse',
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
        'date_traitement',
        'traite_par_id',
        'document_genere',
        'motif_rejet',
        'motif_suspension',
        'unite_retour_id',
        'date_delivrance',
        'delivre_par_id',
        'mode_livraison',
        'mandataire_piece',
        'mandataire_nom',
        'mandataire_prenom',
        'mandataire_cin',
        'date_procuration',
        'postal_cin',
        'postal_nom',
        'reference_envoi',
        'numero_depart',
        'annee_depart',
        'sequence_depart',
        'code_unite_depart',
        'date_annulation',
        'motif_annulation',
        'annule_par_id',
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
            'necessite_reponse' => 'boolean',
            'date_traitement' => 'date',
            'document_genere' => 'boolean',
            'date_delivrance' => 'date',
            'date_procuration' => 'date',
            'date_annulation' => 'datetime',
            'mode_livraison' => ModeLivraison::class,
            'annee_depart' => 'integer',
            'sequence_depart' => 'integer',
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

    /**
     * Agent actuellement responsable du dossier.
     * Null tant que le courrier est en file d'attente de l'unité
     * (avant confirmation de réception).
     */
    public function destinataireAffiche(): ?User
    {
        $this->loadMissing(['responsableActuel.role', 'uniteActuelle']);

        $stored = $this->responsableActuel;

        if ($stored && $this->uniteActuelle) {
            $operational = $this->uniteActuelle->responsable();

            if ($operational && $stored->isAdmin() && $operational->id !== $stored->id) {
                return $operational;
            }
        }

        return $stored;
    }

    public function responsableReception(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsable_reception_id');
    }

    public function traitePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'traite_par_id');
    }

    public function delivrePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delivre_par_id');
    }

    public function uniteRetour(): BelongsTo
    {
        return $this->belongsTo(Unite::class, 'unite_retour_id');
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

    /**
     * Chronologie du dossier (plus ancien → plus récent) pour le suivi.
     */
    public function suiviDossier(): HasMany
    {
        return $this->hasMany(CourrierHistorique::class)
            ->orderBy('created_at')
            ->orderBy('id');
    }

    public function historiqueAffectations(): HasMany
    {
        return $this->hasMany(HistoriqueAffectation::class)->orderByDesc('created_at');
    }

    /**
     * Date à laquelle le courrier est arrivé dans l'unité actuelle :
     * dernière réaffectation, ou dépôt initial s'il n'a jamais été réaffecté.
     */
    public function dateArriveeUnite(): \Illuminate\Support\Carbon
    {
        if ($this->derniere_reaffectation_at ?? null) {
            return \Illuminate\Support\Carbon::parse($this->derniere_reaffectation_at);
        }

        $derniere = $this->historiqueAffectations
            ->first(fn (HistoriqueAffectation $h) => $h->action_type === \App\Enums\ActionType::Reaffectation);

        if ($derniere) {
            return $derniere->created_at;
        }

        return $this->date_depot ?? now();
    }

    /**
     * Résultat métier du dernier traitement (Favorable / Rejeté / Suspendu),
     * dérivé du statut courant — sans table d'historique séparée.
     */
    public function resultatTraitementLabel(): ?string
    {
        return match ($this->statut) {
            StatutCourrier::Suspendu => 'Suspendu',
            StatutCourrier::Rejete => 'Rejeté',
            StatutCourrier::TraiteFavorable,
            StatutCourrier::Traite,
            StatutCourrier::Cloture => 'Favorable',
            StatutCourrier::Archive => $this->motif_rejet ? 'Rejeté' : 'Favorable',
            default => null,
        };
    }

    public function resultatTraitementBadgeColor(): string
    {
        return match ($this->resultatTraitementLabel()) {
            'Favorable' => 'success',
            'Rejeté' => 'danger',
            'Suspendu' => 'info',
            default => 'secondary',
        };
    }
}
