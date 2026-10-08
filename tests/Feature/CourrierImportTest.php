<?php

namespace Tests\Feature;

use App\Enums\StatutCourrier;
use App\Enums\TypeImpot;
use App\Exports\CourrierImportColonnes;
use App\Models\CategorieCourrier;
use App\Models\Courrier;
use App\Models\TypeCourrier;
use App\Models\Unite;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class CourrierImportTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Seul un utilisateur de la profil Accueil peut enregistrer un courrier.
     */
    private function agentEnregistrement(): User
    {
        return User::query()->where('email', 'accueil@dgi.ma')->firstOrFail();
    }

    private function categorie(): CategorieCourrier
    {
        return CategorieCourrier::query()->where('actif', true)->firstOrFail();
    }

    private function type(CategorieCourrier $categorie): TypeCourrier
    {
        return TypeCourrier::query()
            ->where('categorie_courrier_id', $categorie->id)
            ->where('actif', true)
            ->firstOrFail();
    }

    private function unite(): Unite
    {
        return Unite::query()->where('code', 'SERV002')->firstOrFail();
    }

    /**
     * Construit un CSV lisible par l'import, avec les en-têtes du modèle.
     *
     * @param  list<array<string, string>>  $lignes
     */
    private function fichierCsv(array $lignes, string $nom = 'import.csv'): UploadedFile
    {
        $colonnes = array_keys(CourrierImportColonnes::COLONNES);

        $echapper = fn (string $valeur) => '"'.str_replace('"', '""', $valeur).'"';

        $contenu = implode(';', array_map($echapper, CourrierImportColonnes::entetes()))."\n";

        foreach ($lignes as $ligne) {
            $cellules = array_map(
                fn (string $colonne) => $echapper($ligne[$colonne] ?? ''),
                $colonnes
            );

            $contenu .= implode(';', $cellules)."\n";
        }

        $chemin = tempnam(sys_get_temp_dir(), 'import').'.csv';
        file_put_contents($chemin, $contenu);

        return new UploadedFile($chemin, $nom, 'text/csv', null, true);
    }

    /**
     * @return array<string, string>
     */
    private function ligneValide(array $remplacements = []): array
    {
        $categorie = $this->categorie();

        return array_merge([
            'nom_raison_sociale' => 'Société Atlas Import',
            'ifu' => '12345678',
            'categorie' => $categorie->nom,
            'type_courrier' => $this->type($categorie)->nom,
            'objet' => 'Réclamation importée',
            'unite_destinataire' => $this->unite()->code,
            'impots' => 'IR, TVA',
            'annees' => '2023, 2024',
        ], $remplacements);
    }

    public function test_le_modele_excel_peut_etre_telecharge(): void
    {
        $this->seed();

        $reponse = $this->actingAs($this->agentEnregistrement())
            ->get(route('courriers.import.modele'));

        $reponse->assertOk();
        $reponse->assertDownload('modele-import-courriers.xlsx');
    }

    public function test_un_fichier_valide_cree_les_courriers(): void
    {
        $this->seed();

        $avant = Courrier::query()->count();

        $fichier = $this->fichierCsv([
            $this->ligneValide(),
            $this->ligneValide([
                'nom_raison_sociale' => 'Entreprise Souss',
                'ifu' => '87654321',
                'objet' => 'Demande de délai de paiement',
                'impots' => 'TP',
                'annees' => '2025',
            ]),
        ]);

        $reponse = $this->actingAs($this->agentEnregistrement())
            ->post(route('courriers.import'), ['fichier' => $fichier]);

        $reponse->assertRedirect(route('courriers.create'));
        $reponse->assertSessionHas('success');

        $this->assertSame($avant + 2, Courrier::query()->count());

        $this->assertDatabaseHas('courriers', [
            'objet' => 'Réclamation importée',
            'unite_destinataire_id' => $this->unite()->id,
            'statut' => StatutCourrier::Depose->value,
            'user_id' => $this->agentEnregistrement()->id,
        ]);

        $this->assertDatabaseHas('contribuables', [
            'nom_raison_sociale' => 'Entreprise Souss',
            'ifu' => '87654321',
        ]);

        $courrier = Courrier::query()->where('objet', 'Réclamation importée')->firstOrFail();
        $this->assertEqualsCanonicalizing(
            ['IR', 'TVA'],
            $courrier->impots->pluck('impot')->map(fn ($impot) => $impot instanceof TypeImpot ? $impot->value : $impot)->all()
        );
        $this->assertEqualsCanonicalizing([2023, 2024], $courrier->annees->pluck('annee')->all());
    }

    public function test_le_numero_est_attribue_automatiquement_a_chaque_ligne(): void
    {
        $this->seed();

        $fichier = $this->fichierCsv([
            $this->ligneValide(),
            $this->ligneValide(['nom_raison_sociale' => 'Deuxième société', 'ifu' => '22222222']),
        ]);

        $this->actingAs($this->agentEnregistrement())
            ->post(route('courriers.import'), ['fichier' => $fichier])
            ->assertSessionHas('import_resultat');

        $numeros = collect(session('import_resultat')['succes'])->pluck('numero');

        $this->assertCount(2, $numeros);
        $this->assertCount(2, $numeros->unique(), 'Les numéros attribués doivent être uniques.');
        $this->assertNotEmpty($numeros->first());
    }

    public function test_une_ligne_invalide_est_signalee_sans_bloquer_les_autres(): void
    {
        $this->seed();

        $avant = Courrier::query()->count();

        $fichier = $this->fichierCsv([
            $this->ligneValide(),
            $this->ligneValide([
                'nom_raison_sociale' => 'Société sans catégorie',
                'ifu' => '33333333',
                'categorie' => 'Catégorie qui n\'existe pas',
            ]),
        ]);

        $this->actingAs($this->agentEnregistrement())
            ->post(route('courriers.import'), ['fichier' => $fichier]);

        $resultat = session('import_resultat');

        $this->assertCount(1, $resultat['succes']);
        $this->assertCount(1, $resultat['erreurs']);
        $this->assertSame(3, $resultat['erreurs'][0]['ligne']);
        $this->assertStringContainsString('Catégorie inconnue', $resultat['erreurs'][0]['message']);

        $this->assertSame($avant + 1, Courrier::query()->count());
    }

    public function test_une_ligne_sans_identifiant_fiscal_est_refusee(): void
    {
        $this->seed();

        $avant = Courrier::query()->count();

        $fichier = $this->fichierCsv([
            $this->ligneValide(['ifu' => '']),
        ]);

        $this->actingAs($this->agentEnregistrement())
            ->post(route('courriers.import'), ['fichier' => $fichier])
            ->assertSessionHas('error');

        $resultat = session('import_resultat');

        $this->assertCount(1, $resultat['erreurs']);
        $this->assertStringContainsString('au moins un identifiant', $resultat['erreurs'][0]['message']);
        $this->assertSame($avant, Courrier::query()->count());
    }

    public function test_un_impot_inconnu_est_refuse(): void
    {
        $this->seed();

        $avant = Courrier::query()->count();

        $fichier = $this->fichierCsv([
            $this->ligneValide(['impots' => 'IR, XYZ']),
        ]);

        $this->actingAs($this->agentEnregistrement())
            ->post(route('courriers.import'), ['fichier' => $fichier]);

        $resultat = session('import_resultat');

        $this->assertStringContainsString('Impôt inconnu', $resultat['erreurs'][0]['message']);
        $this->assertSame($avant, Courrier::query()->count());
    }

    public function test_une_annee_invalide_est_refusee(): void
    {
        $this->seed();

        $avant = Courrier::query()->count();

        $fichier = $this->fichierCsv([
            $this->ligneValide(['annees' => '2024, mille']),
        ]);

        $this->actingAs($this->agentEnregistrement())
            ->post(route('courriers.import'), ['fichier' => $fichier]);

        $resultat = session('import_resultat');

        $this->assertStringContainsString('Année invalide', $resultat['erreurs'][0]['message']);
        $this->assertSame($avant, Courrier::query()->count());
    }

    public function test_un_type_ne_correspondant_pas_a_la_categorie_est_refuse(): void
    {
        $this->seed();

        $avant = Courrier::query()->count();

        $autreCategorie = CategorieCourrier::query()
            ->where('actif', true)
            ->where('id', '!=', $this->categorie()->id)
            ->firstOrFail();

        $fichier = $this->fichierCsv([
            $this->ligneValide(['type_courrier' => $this->type($autreCategorie)->nom]),
        ]);

        $this->actingAs($this->agentEnregistrement())
            ->post(route('courriers.import'), ['fichier' => $fichier]);

        $resultat = session('import_resultat');

        $this->assertStringContainsString('inconnu pour la catégorie', $resultat['erreurs'][0]['message']);
        $this->assertSame($avant, Courrier::query()->count());
    }

    public function test_les_lignes_vides_sont_ignorees(): void
    {
        $this->seed();

        $fichier = $this->fichierCsv([
            $this->ligneValide(),
            [],
            $this->ligneValide(['nom_raison_sociale' => 'Troisième société', 'ifu' => '44444444']),
        ]);

        $this->actingAs($this->agentEnregistrement())
            ->post(route('courriers.import'), ['fichier' => $fichier]);

        $resultat = session('import_resultat');

        $this->assertCount(2, $resultat['succes']);
        $this->assertCount(0, $resultat['erreurs']);
    }

    public function test_un_fichier_sans_les_colonnes_attendues_est_refuse(): void
    {
        $this->seed();

        $avant = Courrier::query()->count();

        $chemin = tempnam(sys_get_temp_dir(), 'import').'.csv';
        file_put_contents($chemin, "Colonne A;Colonne B\nvaleur;valeur\n");

        $this->actingAs($this->agentEnregistrement())
            ->post(route('courriers.import'), [
                'fichier' => new UploadedFile($chemin, 'mauvais.csv', 'text/csv', null, true),
            ])
            ->assertSessionHas('error');

        $this->assertSame($avant, Courrier::query()->count());
    }

    public function test_le_format_du_fichier_est_verifie(): void
    {
        $this->seed();

        $this->actingAs($this->agentEnregistrement())
            ->post(route('courriers.import'), [
                'fichier' => UploadedFile::fake()->create('document.pdf', 10, 'application/pdf'),
            ])
            ->assertSessionHasErrors('fichier');
    }

    public function test_un_utilisateur_non_autorise_ne_peut_pas_importer(): void
    {
        $this->seed();

        $avant = Courrier::query()->count();

        $agent = User::query()
            ->whereHas('role', fn ($query) => $query->where('name', 'Bureau'))
            ->firstOrFail();

        $this->actingAs($agent)
            ->post(route('courriers.import'), ['fichier' => $this->fichierCsv([$this->ligneValide()])])
            ->assertForbidden();

        $this->actingAs($agent)
            ->get(route('courriers.import.modele'))
            ->assertForbidden();

        $this->assertSame($avant, Courrier::query()->count());
    }

    public function test_la_page_propose_la_saisie_manuelle_et_l_import(): void
    {
        $this->seed();

        $this->actingAs($this->agentEnregistrement())
            ->get(route('courriers.create'))
            ->assertOk()
            ->assertSee('Saisie manuelle')
            ->assertSee('Import Excel')
            ->assertSee(route('courriers.import.modele'), false);
    }
}
