<?php

namespace Tests\Feature;

use App\Enums\ActionHistorique;
use App\Enums\StatutCourrier;
use App\Models\CategorieCourrier;
use App\Models\Contribuable;
use App\Models\Courrier;
use App\Models\TypeCourrier;
use App\Models\Unite;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CourrierEnregistrementTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Seul un utilisateur de la profil Accueil peut enregistrer un courrier.
     */
    private function agentEnregistrement(): User
    {
        return User::query()->where('email', 'accueil@dgi.ma')->firstOrFail();
    }

    private function typePour(CategorieCourrier $categorie): TypeCourrier
    {
        return TypeCourrier::query()
            ->where('categorie_courrier_id', $categorie->id)
            ->firstOrFail();
    }

    public function test_un_courrier_peut_etre_enregistre_par_saisie_manuelle_du_contribuable(): void
    {
        $this->seed();

        $user = $this->agentEnregistrement();
        $categorie = CategorieCourrier::query()->firstOrFail();
        $type = $this->typePour($categorie);
        $unite = Unite::query()->where('code', 'SERV002')->firstOrFail();

        $response = $this->actingAs($user)->post(route('courriers.store'), [
            'mode_contribuable' => 'manuel',
            'type_contribuable' => 'personne_morale',
            'nom_raison_sociale' => 'Société Atlas Fiscal',
            'ifu' => 'IFU123456',
            'cin' => 'AB123456',
            'ice' => '001234567890123',
            'numero_tp' => 'TP-2026-01',
            'numero_th' => 'TH-2026-01',
            'adresse' => 'Avenue Mohammed V',
            'ville' => 'Marrakech',
            'telephone' => '0600000000',
            'email' => 'atlas@example.test',
            'categorie_courrier_id' => $categorie->id,
            'type_courrier_id' => $type->id,
            'necessite_reponse' => true,
            'objet' => 'Demande d\'attestation',
            'type_unite' => $unite->type_unite,
            'unite_destinataire_id' => $unite->id,
            'impots' => ['IR', 'TVA'],
            'annees' => [2024, 2025],
            'remarque' => 'Dossier complet',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('contribuables', [
            'nom_raison_sociale' => 'Société Atlas Fiscal',
            'ifu' => 'IFU123456',
        ]);

        $this->assertDatabaseHas('courriers', [
            'necessite_reponse' => true,
            'objet' => 'Demande d\'attestation',
            'unite_destinataire_id' => $unite->id,
            'statut' => StatutCourrier::Depose->value,
            'user_id' => $user->id,
        ]);

        $courrier = Courrier::query()
            ->with(['historiques', 'impots', 'annees'])
            ->where('objet', 'Demande d\'attestation')
            ->firstOrFail();

        $this->assertSame($unite->code, $courrier->code_unite);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{6}-'.preg_quote($unite->code, '/').'$/', $courrier->numero);
        $this->assertCount(2, $courrier->historiques);
        $this->assertSame(ActionHistorique::Depot, $courrier->historiques->sortBy('id')->first()->action);
        $this->assertTrue(
            $courrier->historiques->contains(fn ($h) => $h->action === ActionHistorique::Affectation)
        );
        $this->assertCount(2, $courrier->impots);
        $this->assertCount(2, $courrier->annees);

        $this->actingAs($user)
            ->get(route('courriers.show', $courrier))
            ->assertOk()
            ->assertSee($courrier->numero);

        $this->actingAs($user)
            ->get(route('courriers.recu', $courrier))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_necessite_reponse_est_impose_par_la_categorie(): void
    {
        $this->seed();

        $user = $this->agentEnregistrement();
        $categorie = CategorieCourrier::query()->firstOrFail();
        $categorie->update(['necessite_reponse' => false]);
        $type = $this->typePour($categorie);
        $unite = Unite::query()->where('code', 'SERV002')->firstOrFail();

        // La valeur envoyée par le formulaire est ignorée au profit de la catégorie.
        $this->actingAs($user)->post(route('courriers.store'), [
            'mode_contribuable' => 'manuel',
            'type_contribuable' => 'personne_morale',
            'nom_raison_sociale' => 'Société Sans Réponse',
            'ifu' => 'IFU999001',
            'categorie_courrier_id' => $categorie->id,
            'type_courrier_id' => $type->id,
            'necessite_reponse' => true,
            'objet' => 'Courrier pour information',
            'type_unite' => $unite->type_unite,
            'unite_destinataire_id' => $unite->id,
            'impots' => ['IR'],
            'annees' => [2025],
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('courriers', [
            'objet' => 'Courrier pour information',
            'categorie_courrier_id' => $categorie->id,
            'necessite_reponse' => false,
            'statut' => StatutCourrier::Archive->value,
        ]);
    }

    public function test_l_accueil_retrouve_son_courrier_quelle_que_soit_l_unite_destinataire(): void
    {
        $this->seed();

        $accueil = $this->agentEnregistrement();
        $categorie = CategorieCourrier::query()->firstOrFail();
        $type = $this->typePour($categorie);
        // Unité hors de l'arborescence du bureau d'ordre de l'Accueil.
        $secteur = Unite::query()->where('code', 'SECT001')->firstOrFail();
        $this->assertNotContains($secteur->id, $accueil->unite->hierarchy_ids);

        $this->actingAs($accueil)->post(route('courriers.store'), [
            'mode_contribuable' => 'manuel',
            'type_contribuable' => 'personne_morale',
            'nom_raison_sociale' => 'Société Hors Périmètre',
            'ifu' => 'IFU999004',
            'categorie_courrier_id' => $categorie->id,
            'type_courrier_id' => $type->id,
            'type_unite' => $secteur->type_unite,
            'unite_destinataire_id' => $secteur->id,
            'impots' => ['IR'],
            'annees' => [2025],
        ])->assertSessionHasNoErrors();

        $courrier = Courrier::query()->latest('id')->firstOrFail();
        $this->assertSame(StatutCourrier::Depose, $courrier->statut);
        $this->assertSame($secteur->id, $courrier->unite_actuelle_id);

        $this->actingAs($accueil)->get(route('dashboard'))
            ->assertOk()
            ->assertSee($courrier->numero);

        $chefSecteur = User::query()->where('email', 'secteur.gueliz@dgi.ma')->firstOrFail();
        $this->actingAs($chefSecteur)->get(route('courriers.reception.index'))
            ->assertOk()
            ->assertSee($courrier->numero);

        // Après réception, le courrier reste visible pour l'Accueil et sa date de dépôt ne change pas.
        $dateDepot = $courrier->date_depot->toDateTimeString();
        $this->travel(2)->hours();
        $this->actingAs($chefSecteur)->post(route('courriers.confirmer-reception', $courrier))
            ->assertSessionHas('success');
        $this->assertSame($dateDepot, $courrier->fresh()->date_depot->toDateTimeString());
        $this->actingAs($accueil)->get(route('dashboard'))->assertSee($courrier->numero);
        $this->actingAs($chefSecteur)->get(route('courriers.traitement.index'))->assertSee($courrier->numero);
    }

    public function test_le_numero_suit_le_format_annee_sequence_unite(): void
    {
        $this->seed();

        $service = app(\App\Services\CourrierNumeroService::class);
        $bo = Unite::query()->where('code', 'BO001')->firstOrFail();
        $brigade = Unite::query()->where('code', 'BRIG001')->firstOrFail();
        $dernierBo = (int) Courrier::query()->where('code_unite', 'BO001')->where('annee_enregistrement', 2026)->max('sequence');

        $premier = $service->generer($bo, 2026);
        $second = $service->generer($bo, 2026);
        $autreUnite = $service->generer($brigade, 2030);

        $this->assertSame(sprintf('2026-%06d-BO001', $dernierBo + 1), $premier['numero']);
        $this->assertSame(sprintf('2026-%06d-BO001', $dernierBo + 2), $second['numero']);
        // Séquence propre à chaque unité, qui repart à 1 pour une nouvelle année.
        $this->assertSame('2030-000001-BRIG001', $autreUnite['numero']);
    }

    public function test_une_categorie_inactive_est_refusee(): void
    {
        $this->seed();

        $categorie = CategorieCourrier::query()->firstOrFail();
        $categorie->update(['actif' => false]);
        $type = $this->typePour($categorie);
        $unite = Unite::query()->where('code', 'SERV002')->firstOrFail();

        $this->actingAs($this->agentEnregistrement())->post(route('courriers.store'), [
            'mode_contribuable' => 'manuel',
            'type_contribuable' => 'personne_morale',
            'nom_raison_sociale' => 'Société Catégorie Inactive',
            'ifu' => 'IFU999003',
            'categorie_courrier_id' => $categorie->id,
            'type_courrier_id' => $type->id,
            'type_unite' => $unite->type_unite,
            'unite_destinataire_id' => $unite->id,
            'impots' => ['IR'],
            'annees' => [2025],
        ])->assertSessionHasErrors(['categorie_courrier_id' => 'La catégorie sélectionnée est inconnue ou inactive.']);

        $this->assertDatabaseMissing('contribuables', ['nom_raison_sociale' => 'Société Catégorie Inactive']);
    }

    public function test_l_objet_est_facultatif(): void
    {
        $this->seed();

        $user = $this->agentEnregistrement();
        $categorie = CategorieCourrier::query()->firstOrFail();
        $type = $this->typePour($categorie);
        $unite = Unite::query()->where('code', 'SERV002')->firstOrFail();

        $this->actingAs($user)->post(route('courriers.store'), [
            'mode_contribuable' => 'manuel',
            'type_contribuable' => 'personne_morale',
            'nom_raison_sociale' => 'Société Sans Objet',
            'ifu' => 'IFU999002',
            'categorie_courrier_id' => $categorie->id,
            'type_courrier_id' => $type->id,
            'objet' => '',
            'type_unite' => $unite->type_unite,
            'unite_destinataire_id' => $unite->id,
            'impots' => ['IR'],
            'annees' => [2025],
        ])->assertSessionHasNoErrors();

        $courrier = Courrier::query()
            ->whereHas('contribuable', fn ($q) => $q->where('nom_raison_sociale', 'Société Sans Objet'))
            ->firstOrFail();

        $this->assertNull($courrier->objet);

        $this->actingAs($user)->get(route('courriers.show', $courrier))->assertOk();
    }

    public function test_un_courrier_peut_etre_enregistre_pour_un_contribuable_existant(): void
    {
        $this->seed();

        $user = $this->agentEnregistrement();
        $categorie = CategorieCourrier::query()->firstOrFail();
        $type = $this->typePour($categorie);
        $unite = Unite::query()->where('code', 'SERV002')->firstOrFail();
        $contribuable = Contribuable::query()->firstOrFail();

        $response = $this->actingAs($user)->post(route('courriers.store'), [
            'mode_contribuable' => 'recherche',
            'contribuable_id' => $contribuable->id,
            'categorie_courrier_id' => $categorie->id,
            'type_courrier_id' => $type->id,
            'necessite_reponse' => true,
            'objet' => 'Courrier pour contribuable existant',
            'type_unite' => $unite->type_unite,
            'unite_destinataire_id' => $unite->id,
            'impots' => ['TSC'],
            'annees' => [2025],
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('courriers', [
            'necessite_reponse' => true,
            'objet' => 'Courrier pour contribuable existant',
            'contribuable_id' => $contribuable->id,
        ]);
    }

    public function test_la_saisie_manuelle_reutilise_un_contribuable_deja_connu(): void
    {
        $this->seed();

        $user = $this->agentEnregistrement();
        $categorie = CategorieCourrier::query()->firstOrFail();
        $type = $this->typePour($categorie);
        $unite = Unite::query()->where('code', 'SERV002')->firstOrFail();
        $contribuable = Contribuable::query()->whereNotNull('ifu')->firstOrFail();
        $nombreAvant = Contribuable::query()->count();

        $this->actingAs($user)->post(route('courriers.store'), [
            'mode_contribuable' => 'manuel',
            'type_contribuable' => 'personne_morale',
            'nom_raison_sociale' => $contribuable->nom_raison_sociale,
            'ifu' => $contribuable->ifu,
            'ville' => 'Marrakech',
            'categorie_courrier_id' => $categorie->id,
            'type_courrier_id' => $type->id,
            'necessite_reponse' => true,
            'objet' => 'Second courrier même contribuable',
            'type_unite' => $unite->type_unite,
            'unite_destinataire_id' => $unite->id,
            'impots' => ['IR'],
            'annees' => [2025],
        ])->assertSessionHasNoErrors();

        $this->assertSame($nombreAvant, Contribuable::query()->count());

        $this->assertDatabaseHas('courriers', [
            'necessite_reponse' => true,
            'objet' => 'Second courrier même contribuable',
            'contribuable_id' => $contribuable->id,
        ]);
    }

    public function test_la_saisie_manuelle_exige_au_moins_un_identifiant_fiscal(): void
    {
        $this->seed();

        $user = $this->agentEnregistrement();
        $categorie = CategorieCourrier::query()->firstOrFail();
        $type = $this->typePour($categorie);
        $unite = Unite::query()->where('code', 'SERV002')->firstOrFail();

        $this->actingAs($user)
            ->post(route('courriers.store'), [
                'mode_contribuable' => 'manuel',
            'type_contribuable' => 'personne_morale',
            'nom_raison_sociale' => 'Contribuable sans identifiant',
                'ville' => 'Marrakech',
                'categorie_courrier_id' => $categorie->id,
                'type_courrier_id' => $type->id,
                'necessite_reponse' => true,
            'objet' => 'Courrier incomplet',
                'type_unite' => $unite->type_unite,
                'unite_destinataire_id' => $unite->id,
                'impots' => ['IR'],
                'annees' => [2025],
            ])
            ->assertSessionHasErrors('ifu');

        $this->assertDatabaseMissing('courriers', [
            'objet' => 'Courrier incomplet',
        ]);
    }

    public function test_unite_bureau_a_pour_responsable_le_chef_de_bureau(): void
    {
        $this->seed();

        $unite = Unite::query()->where('code', 'BO001')->firstOrFail();
        $chefBureau = User::query()->where('email', 'bureau@dgi.ma')->firstOrFail();
        $admin = User::query()->where('email', 'admin@dgi.ma')->firstOrFail();

        $responsable = $unite->responsable();

        $this->assertNotNull($responsable);
        $this->assertSame($chefBureau->id, $responsable->id);
        $this->assertNotSame($admin->id, $responsable->id);
    }

    public function test_courrier_vers_direction_regionale_reste_en_file_d_unite(): void
    {
        $this->seed();

        $user = $this->agentEnregistrement();
        $categorie = CategorieCourrier::query()->firstOrFail();
        $type = $this->typePour($categorie);
        $unite = Unite::query()->where('code', 'DR001')->firstOrFail();

        $this->actingAs($user)->post(route('courriers.store'), [
            'mode_contribuable' => 'manuel',
            'type_contribuable' => 'personne_morale',
            'nom_raison_sociale' => 'Contribuable Test DR',
            'ifu' => 'IFU-DR-001',
            'adresse' => 'Avenue Mohammed VI',
            'ville' => 'Marrakech',
            'categorie_courrier_id' => $categorie->id,
            'type_courrier_id' => $type->id,
            'necessite_reponse' => true,
            'objet' => 'Courrier pour la direction régionale',
            'type_unite' => $unite->type_unite,
            'unite_destinataire_id' => $unite->id,
            'impots' => ['IR'],
            'annees' => [2025],
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('courriers', [
            'necessite_reponse' => true,
            'objet' => 'Courrier pour la direction régionale',
            'unite_destinataire_id' => $unite->id,
            'unite_actuelle_id' => $unite->id,
            'responsable_actuel_id' => null,
        ]);
    }

    public function test_les_pieces_jointes_pdf_word_et_images_sont_acceptees(): void
    {
        Storage::fake('local');
        $this->seed();

        $user = $this->agentEnregistrement();
        $categorie = CategorieCourrier::query()->firstOrFail();
        $type = $this->typePour($categorie);
        $unite = Unite::query()->where('code', 'SERV002')->firstOrFail();

        $this->actingAs($user)->post(route('courriers.store'), [
            'mode_contribuable' => 'manuel',
            'type_contribuable' => 'personne_morale',
            'nom_raison_sociale' => 'Société Pièce Jointe',
            'ifu' => 'IFU999999',
            'adresse' => 'Rue de la DGI',
            'ville' => 'Casablanca',
            'categorie_courrier_id' => $categorie->id,
            'type_courrier_id' => $type->id,
            'necessite_reponse' => true,
            'objet' => 'Dossier avec justificatifs',
            'type_unite' => $unite->type_unite,
            'unite_destinataire_id' => $unite->id,
            'impots' => ['IS'],
            'annees' => [2025],
            'pieces_jointes' => [
                UploadedFile::fake()->create('document.pdf', 120, 'application/pdf'),
                UploadedFile::fake()->create('courrier.docx', 80, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'),
            ],
        ])->assertSessionHasNoErrors();

        $courrier = Courrier::query()
            ->with('piecesJointes')
            ->where('objet', 'Dossier avec justificatifs')
            ->firstOrFail();

        $this->assertCount(2, $courrier->piecesJointes);

        $piece = $courrier->piecesJointes->firstOrFail();
        Storage::disk('local')->assertExists($piece->chemin);

        $this->actingAs($user)
            ->get(route('courriers.pieces-jointes.show', [$courrier, $piece]))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('courriers.pieces-jointes.telecharger', [$courrier, $piece]))
            ->assertOk()
            ->assertHeader('content-disposition');
    }
}
