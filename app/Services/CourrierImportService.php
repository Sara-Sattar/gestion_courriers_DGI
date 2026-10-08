<?php

namespace App\Services;

use App\Enums\TypeImpot;
use App\Exports\CourrierImportColonnes;
use App\Imports\LectureCourriersImport;
use App\Models\CategorieCourrier;
use App\Models\TypeCourrier;
use App\Models\Unite;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

/**
 * Import de courriers depuis un fichier Excel.
 *
 * Chaque ligne est validée puis enregistrée indépendamment via
 * CourrierEnregistrementService : les lignes correctes sont importées,
 * les lignes en erreur sont signalées avec leur numéro de ligne.
 */
class CourrierImportService
{
    /** Colonnes sans lesquelles le fichier ne peut pas être exploité. */
    private const COLONNES_REQUISES = [
        'nom_raison_sociale',
        'categorie',
        'type_courrier',
        'unite_destinataire',
        'impots',
        'annees',
    ];

    private const IDENTIFIANTS_FISCAUX = ['ifu', 'cin', 'ice', 'numero_tp', 'numero_th'];

    public function __construct(
        private readonly CourrierEnregistrementService $enregistrementService,
    ) {}

    public function importer(UploadedFile $fichier, User $user): CourrierImportResult
    {
        $lignes = $this->lireLignes($fichier);

        if ($lignes === []) {
            return new CourrierImportResult(erreurs: [
                ['ligne' => 0, 'message' => 'Le fichier est vide.'],
            ]);
        }

        $indexEntetes = $this->trouverLigneEntetes($lignes);

        if ($indexEntetes === null) {
            return new CourrierImportResult(erreurs: [
                ['ligne' => 1, 'message' => 'Aucune ligne d\'en-têtes reconnue. Utilisez le modèle Excel fourni.'],
            ]);
        }

        $colonnes = $this->associerColonnes($lignes[$indexEntetes]);
        $manquantes = array_diff(self::COLONNES_REQUISES, array_keys($colonnes));

        if ($manquantes !== []) {
            $intitules = array_map(
                fn (string $cle) => CourrierImportColonnes::COLONNES[$cle],
                $manquantes
            );

            return new CourrierImportResult(erreurs: [[
                'ligne' => $indexEntetes + 1,
                'message' => 'Colonnes manquantes dans le fichier : '.implode(', ', $intitules).'.',
            ]]);
        }

        $succes = [];
        $erreurs = [];

        foreach ($lignes as $index => $ligne) {
            if ($index <= $indexEntetes) {
                continue;
            }

            $donnees = $this->extraireDonnees($ligne, $colonnes);

            if ($this->ligneVide($donnees)) {
                continue;
            }

            $numeroLigne = $index + 1;

            try {
                $courrier = $this->enregistrerLigne($donnees, $user);
                $succes[] = ['ligne' => $numeroLigne, 'numero' => $courrier->numero];
            } catch (LigneImportInvalideException $exception) {
                $erreurs[] = ['ligne' => $numeroLigne, 'message' => $exception->getMessage()];
            } catch (Throwable $exception) {
                $erreurs[] = ['ligne' => $numeroLigne, 'message' => 'Erreur inattendue : '.$exception->getMessage()];
            }
        }

        return new CourrierImportResult($succes, $erreurs);
    }

    /**
     * @return list<array<int, mixed>>
     */
    private function lireLignes(UploadedFile $fichier): array
    {
        $lecture = new LectureCourriersImport();
        Excel::import($lecture, $fichier);

        return $lecture->lignes;
    }

    /**
     * Localise la ligne d'en-têtes : la première ligne dont au moins
     * trois cellules correspondent à une colonne connue.
     *
     * @param  list<array<int, mixed>>  $lignes
     */
    private function trouverLigneEntetes(array $lignes): ?int
    {
        foreach ($lignes as $index => $ligne) {
            if (count($this->associerColonnes($ligne)) >= 3) {
                return $index;
            }
        }

        return null;
    }

    /**
     * Position de chaque colonne reconnue dans la ligne d'en-têtes.
     *
     * @param  array<int, mixed>  $entetes
     * @return array<string, int>
     */
    private function associerColonnes(array $entetes): array
    {
        $colonnes = [];

        foreach ($entetes as $position => $entete) {
            $cle = CourrierImportColonnes::cleDepuisEntete(
                is_scalar($entete) ? (string) $entete : null
            );

            if ($cle !== null && ! array_key_exists($cle, $colonnes)) {
                $colonnes[$cle] = $position;
            }
        }

        return $colonnes;
    }

    /**
     * @param  array<int, mixed>  $ligne
     * @param  array<string, int>  $colonnes
     * @return array<string, string>
     */
    private function extraireDonnees(array $ligne, array $colonnes): array
    {
        $donnees = [];

        foreach ($colonnes as $cle => $position) {
            $donnees[$cle] = $this->normaliser($ligne[$position] ?? null);
        }

        return $donnees;
    }

    /**
     * Convertit une cellule en chaîne exploitable. Les nombres entiers
     * lus comme flottants (IFU, ICE…) ne doivent pas devenir "1.2345E+14".
     */
    private function normaliser(mixed $valeur): string
    {
        if ($valeur === null || is_bool($valeur)) {
            return '';
        }

        if (is_float($valeur) && floor($valeur) === $valeur && abs($valeur) < PHP_INT_MAX) {
            return (string) (int) $valeur;
        }

        if (! is_scalar($valeur)) {
            return '';
        }

        return trim((string) $valeur);
    }

    /**
     * @param  array<string, string>  $donnees
     */
    private function ligneVide(array $donnees): bool
    {
        foreach ($donnees as $valeur) {
            if ($valeur !== '') {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  array<string, string>  $donnees
     *
     * @throws LigneImportInvalideException
     */
    private function enregistrerLigne(array $donnees, User $user): \App\Models\Courrier
    {
        $this->validerChampsTexte($donnees);

        $categorie = $this->resoudreCategorie($donnees['categorie']);
        $type = $this->resoudreType($donnees['type_courrier'], $categorie);
        $unite = $this->resoudreUnite($donnees['unite_destinataire']);
        $impots = $this->resoudreImpots($donnees['impots']);
        $annees = $this->resoudreAnnees($donnees['annees']);

        $this->verifierIdentifiantFiscal($donnees);

        return $this->enregistrementService->enregistrer([
            'mode_contribuable' => 'manuel',
            'type_contribuable' => $this->resoudreTypeContribuable($donnees['type_contribuable'] ?? null),
            'nom_raison_sociale' => $donnees['nom_raison_sociale'],
            'ifu' => $donnees['ifu'] ?? null,
            'cin' => $donnees['cin'] ?? null,
            'ice' => $donnees['ice'] ?? null,
            'numero_tp' => $donnees['numero_tp'] ?? null,
            'numero_th' => $donnees['numero_th'] ?? null,
            'adresse' => $donnees['adresse'] ?? null,
            'ville' => $donnees['ville'] ?? null,
            'telephone' => $donnees['telephone'] ?? null,
            'email' => $donnees['email'] ?? null,
            'categorie_courrier_id' => $categorie->id,
            'type_courrier_id' => $type->id,
            'objet' => ($donnees['objet'] ?? '') !== '' ? $donnees['objet'] : null,
            'unite_destinataire_id' => $unite->id,
            'impots' => $impots,
            'annees' => $annees,
            'remarque' => $donnees['remarque'] ?? null,
        ], $user);
    }

    private function resoudreTypeContribuable(?string $valeur): string
    {
        $normalise = Str::slug((string) $valeur, '_');

        return match ($normalise) {
            'personne_physique_pro', 'physique_pro', 'pro' => 'personne_physique_pro',
            'personne_physique_particulier', 'physique_particulier', 'particulier' => 'personne_physique_particulier',
            'autre' => 'autre',
            default => 'personne_morale',
        };
    }

    /**
     * @param  array<string, string>  $donnees
     *
     * @throws LigneImportInvalideException
     */
    private function validerChampsTexte(array $donnees): void
    {
        $validateur = Validator::make($donnees, [
            'nom_raison_sociale' => ['required', 'string', 'max:255'],
            'objet' => ['nullable', 'string', 'max:500'],
            'email' => ['nullable', 'email', 'max:255'],
            'ifu' => ['nullable', 'string', 'max:20'],
            'cin' => ['nullable', 'string', 'max:20'],
            'ice' => ['nullable', 'string', 'max:20'],
            'numero_tp' => ['nullable', 'string', 'max:30'],
            'numero_th' => ['nullable', 'string', 'max:30'],
            'adresse' => ['nullable', 'string', 'max:255'],
            'ville' => ['nullable', 'string', 'max:255'],
            'telephone' => ['nullable', 'string', 'max:30'],
            'remarque' => ['nullable', 'string', 'max:2000'],
        ], [
            'nom_raison_sociale.required' => 'Le nom ou la raison sociale est obligatoire.',
            'email.email' => 'L\'adresse e-mail n\'est pas valide.',
        ]);

        if ($validateur->fails()) {
            throw new LigneImportInvalideException(
                implode(' ', $validateur->errors()->all())
            );
        }
    }

    /**
     * @throws LigneImportInvalideException
     */
    private function resoudreCategorie(string $nom): CategorieCourrier
    {
        $categorie = CategorieCourrier::query()
            ->where('actif', true)
            ->get()
            ->first(fn (CategorieCourrier $item) => $this->memeLibelle($item->nom, $nom));

        if (! $categorie) {
            throw new LigneImportInvalideException(
                'Catégorie inconnue ou inactive : « '.$nom.' ».'
            );
        }

        return $categorie;
    }

    /**
     * @throws LigneImportInvalideException
     */
    private function resoudreType(string $nom, CategorieCourrier $categorie): TypeCourrier
    {
        $type = TypeCourrier::query()
            ->where('actif', true)
            ->where('categorie_courrier_id', $categorie->id)
            ->get()
            ->first(fn (TypeCourrier $item) => $this->memeLibelle($item->nom, $nom));

        if (! $type) {
            throw new LigneImportInvalideException(
                'Type de courrier « '.$nom.' » inconnu pour la catégorie « '.$categorie->nom.' ».'
            );
        }

        return $type;
    }

    /**
     * L'unité est identifiée par son code, ou à défaut par son nom.
     *
     * @throws LigneImportInvalideException
     */
    private function resoudreUnite(string $valeur): Unite
    {
        $unites = Unite::query()->where('actif', true)->get();

        $unite = $unites->first(fn (Unite $item) => $this->memeLibelle($item->code, $valeur))
            ?? $unites->first(fn (Unite $item) => $this->memeLibelle($item->nom, $valeur));

        if (! $unite) {
            throw new LigneImportInvalideException(
                'Unité destinataire inconnue ou inactive : « '.$valeur.' ».'
            );
        }

        return $unite;
    }

    /**
     * @return list<string>
     *
     * @throws LigneImportInvalideException
     */
    private function resoudreImpots(string $valeur): array
    {
        $impots = [];

        foreach ($this->separer($valeur) as $element) {
            $code = strtoupper($element);
            $impot = TypeImpot::tryFrom($code);

            if (! $impot) {
                throw new LigneImportInvalideException(
                    'Impôt inconnu : « '.$element.' ». Valeurs acceptées : '
                    .implode(', ', array_column(TypeImpot::cases(), 'value')).'.'
                );
            }

            $impots[$impot->value] = $impot->value;
        }

        if ($impots === []) {
            throw new LigneImportInvalideException('Indiquez au moins un impôt concerné.');
        }

        return array_values($impots);
    }

    /**
     * @return list<int>
     *
     * @throws LigneImportInvalideException
     */
    private function resoudreAnnees(string $valeur): array
    {
        $annees = [];

        foreach ($this->separer($valeur) as $element) {
            if (! preg_match('/^\d{4}$/', $element)) {
                throw new LigneImportInvalideException(
                    'Année invalide : « '.$element.' ». Attendu : une année sur 4 chiffres.'
                );
            }

            $annee = (int) $element;

            if ($annee < 1990 || $annee > 2100) {
                throw new LigneImportInvalideException(
                    'Année hors limites : '.$annee.'. Attendu entre 1990 et 2100.'
                );
            }

            $annees[$annee] = $annee;
        }

        if ($annees === []) {
            throw new LigneImportInvalideException('Indiquez au moins une année concernée.');
        }

        return array_values($annees);
    }

    /**
     * @param  array<string, string>  $donnees
     *
     * @throws LigneImportInvalideException
     */
    private function verifierIdentifiantFiscal(array $donnees): void
    {
        foreach (self::IDENTIFIANTS_FISCAUX as $identifiant) {
            if (($donnees[$identifiant] ?? '') !== '') {
                return;
            }
        }

        throw new LigneImportInvalideException(
            'Renseignez au moins un identifiant : IFU, CIN, ICE, Numéro TP ou Numéro TH.'
        );
    }

    /**
     * @return list<string>
     */
    private function separer(string $valeur): array
    {
        $elements = preg_split('/[,;\/\|\n\r]+/', $valeur) ?: [];

        return array_values(array_filter(
            array_map('trim', $elements),
            fn (string $element) => $element !== ''
        ));
    }

    private function memeLibelle(?string $reference, string $saisie): bool
    {
        return Str::slug((string) $reference) === Str::slug($saisie);
    }
}
