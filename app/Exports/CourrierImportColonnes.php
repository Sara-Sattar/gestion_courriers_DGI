<?php

namespace App\Exports;

use Illuminate\Support\Str;

/**
 * Colonnes attendues dans le fichier Excel d'import de courriers.
 *
 * Le modèle téléchargé et le service d'import partagent cette définition
 * afin qu'ils ne puissent pas diverger.
 */
class CourrierImportColonnes
{
    /**
     * Clé interne => intitulé affiché dans le modèle Excel.
     *
     * @var array<string, string>
     */
    public const COLONNES = [
        'nom_raison_sociale' => 'Nom / Raison sociale (obligatoire)',
        'ifu' => 'IFU',
        'cin' => 'CIN',
        'ice' => 'ICE',
        'numero_tp' => 'Numéro TP',
        'numero_th' => 'Numéro TH',
        'adresse' => 'Adresse',
        'ville' => 'Ville',
        'telephone' => 'Téléphone',
        'email' => 'Email',
        'categorie' => 'Catégorie (obligatoire)',
        'type_courrier' => 'Type de courrier (obligatoire)',
        'objet' => 'Objet',
        'type_contribuable' => 'Type contribuable',
        'unite_destinataire' => 'Code unité destinataire (obligatoire)',
        'impots' => 'Impôts (obligatoire)',
        'annees' => 'Années (obligatoire)',
        'remarque' => 'Remarque',
    ];

    /**
     * Intitulés alternatifs tolérés lors de la lecture d'un fichier.
     *
     * @var array<string, list<string>>
     */
    private const ALIAS = [
        'nom_raison_sociale' => ['nom', 'raison_sociale', 'contribuable'],
        'numero_tp' => ['n_tp', 'tp'],
        'numero_th' => ['n_th', 'th'],
        'telephone' => ['tel'],
        'email' => ['e_mail', 'courriel'],
        'categorie' => ['categorie_de_courrier'],
        'type_courrier' => ['type', 'type_de_courrier'],
        'type_contribuable' => ['type_de_contribuable'],
        'unite_destinataire' => ['code_unite', 'unite', 'destinataire'],
        'impots' => ['impot', 'impots_concernes'],
        'annees' => ['annee', 'annees_concernees'],
        'remarque' => ['remarques', 'observation'],
    ];

    /**
     * @return list<string>
     */
    public static function entetes(): array
    {
        return array_values(self::COLONNES);
    }

    /**
     * Associe un intitulé de colonne lu dans le fichier à sa clé interne.
     * Les accents, la casse, la ponctuation et les mentions entre
     * parenthèses sont ignorés pour tolérer les fichiers retouchés.
     */
    public static function cleDepuisEntete(?string $entete): ?string
    {
        $normalise = self::normaliser((string) $entete);

        if ($normalise === '') {
            return null;
        }

        foreach (self::COLONNES as $cle => $intitule) {
            if ($normalise === $cle || $normalise === self::normaliser($intitule)) {
                return $cle;
            }
        }

        foreach (self::ALIAS as $cle => $alias) {
            if (in_array($normalise, $alias, true)) {
                return $cle;
            }
        }

        return null;
    }

    private static function normaliser(string $valeur): string
    {
        return Str::slug(preg_replace('/\(.*?\)/u', '', $valeur), '_');
    }
}
