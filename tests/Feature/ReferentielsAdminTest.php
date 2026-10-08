<?php

namespace Tests\Feature;

use App\Models\CategorieCourrier;
use App\Models\TypeCourrier;
use App\Models\Unite;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReferentielsAdminTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::query()->where('email', 'admin@dgi.ma')->firstOrFail();
    }

    private function nonAdmin(): User
    {
        return User::query()->where('email', 'accueil@dgi.ma')->firstOrFail();
    }

    public function test_l_admin_peut_creer_une_categorie(): void
    {
        $this->seed();

        $this->actingAs($this->admin())
            ->post(route('categories.store'), [
                'nom' => 'Opposition',
                'description' => 'Opposition à un acte de recouvrement',
                'necessite_reponse' => 0,
                'actif' => 1,
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('categories.index'));

        $this->assertDatabaseHas('categories_courrier', [
            'nom' => 'Opposition',
            'necessite_reponse' => false,
            'actif' => true,
        ]);
    }

    public function test_l_admin_peut_ajouter_un_type_a_une_categorie_existante(): void
    {
        $this->seed();

        $categorie = CategorieCourrier::query()->where('nom', 'Attestation')->firstOrFail();

        $this->actingAs($this->admin())
            ->post(route('types-courrier.store'), [
                'categorie_courrier_id' => $categorie->id,
                'nom' => 'Attestation de situation fiscale',
                'actif' => 1,
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('types-courrier.index'));

        $this->assertDatabaseHas('types_courrier', [
            'categorie_courrier_id' => $categorie->id,
            'nom' => 'Attestation de situation fiscale',
        ]);
    }

    public function test_l_admin_peut_creer_une_unite_destinataire(): void
    {
        $this->seed();

        $parent = Unite::query()->where('code', 'DR001')->firstOrFail();

        $this->actingAs($this->admin())
            ->post(route('unites.store'), [
                'nom' => 'Brigade de recoupement',
                'type_unite' => 'Brigade',
                'parent_id' => $parent->id,
                'actif' => 1,
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('unites.index'));

        $this->assertDatabaseHas('unites', [
            'nom' => 'Brigade de recoupement',
            'type_unite' => 'Brigade',
            'parent_id' => $parent->id,
        ]);
    }

    public function test_le_code_d_une_nouvelle_unite_suit_la_numerotation_du_type(): void
    {
        $this->seed();

        // Le jeu de données contient déjà BRIG001.
        $this->actingAs($this->admin())
            ->post(route('unites.store'), [
                'nom' => 'Brigade de recoupement',
                'code' => 'CODE-IGNORE',
                'type_unite' => 'Brigade',
                'actif' => 1,
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame('BRIG002', Unite::query()->where('nom', 'Brigade de recoupement')->value('code'));
    }

    public function test_le_prochain_code_est_annonce_avant_la_creation(): void
    {
        $this->seed();

        $this->actingAs($this->admin())
            ->getJson(route('unites.prochainCode', ['type' => 'Brigade']))
            ->assertOk()
            ->assertJson(['code' => 'BRIG002']);
    }

    public function test_les_parents_proposes_dependent_du_type_choisi(): void
    {
        $this->seed();

        $typesProposes = collect(
            $this->actingAs($this->admin())
                ->getJson(route('unites.parentsPossibles', ['type' => 'Service']))
                ->assertOk()
                ->json()
        )->pluck('type_unite')->unique();

        // Un service se rattache à une DR ou à un autre service, jamais plus bas.
        $this->assertEqualsCanonicalizing(['DR', 'Service'], $typesProposes->all());
    }

    public function test_une_unite_ne_peut_pas_etre_rattachee_a_un_niveau_inferieur(): void
    {
        $this->seed();

        $secteur = Unite::query()->where('type_unite', 'Secteur')->firstOrFail();

        $this->actingAs($this->admin())
            ->post(route('unites.store'), [
                'nom' => 'Service du contentieux',
                'type_unite' => 'Service',
                'parent_id' => $secteur->id,
                'actif' => 1,
            ])
            ->assertSessionHasErrors('parent_id');
    }

    public function test_une_categorie_utilisee_ne_peut_pas_etre_supprimee(): void
    {
        $this->seed();

        $categorie = CategorieCourrier::query()
            ->whereHas('courriers')
            ->firstOrFail();

        $this->actingAs($this->admin())
            ->delete(route('categories.destroy', $categorie))
            ->assertRedirect(route('categories.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('categories_courrier', ['id' => $categorie->id]);
    }

    public function test_un_type_en_double_dans_la_meme_categorie_est_refuse(): void
    {
        $this->seed();

        $type = TypeCourrier::query()->firstOrFail();

        $this->actingAs($this->admin())
            ->post(route('types-courrier.store'), [
                'categorie_courrier_id' => $type->categorie_courrier_id,
                'nom' => $type->nom,
            ])
            ->assertSessionHasErrors('nom');
    }

    public function test_la_recherche_restreint_la_liste_des_unites(): void
    {
        $this->seed();

        $unites = $this->actingAs($this->admin())
            ->get(route('unites.index', ['recherche' => 'DR001']))
            ->assertOk()
            ->viewData('unites');

        $this->assertCount(1, $unites);
        $this->assertSame('DR001', $unites->first()->code);
    }

    public function test_le_filtre_de_statut_ecarte_les_categories_inactives(): void
    {
        $this->seed();

        $categorie = CategorieCourrier::query()->firstOrFail();
        $categorie->update(['actif' => false]);

        $actives = $this->actingAs($this->admin())
            ->get(route('categories.index', ['statut' => 'actifs']))
            ->assertOk()
            ->viewData('categories');

        $this->assertFalse($actives->contains('id', $categorie->id));
        $this->assertTrue($actives->every(fn (CategorieCourrier $active) => $active->actif));
    }

    public function test_un_non_admin_ne_peut_pas_gerer_les_referentiels(): void
    {
        $this->seed();

        $utilisateur = $this->nonAdmin();

        $this->actingAs($utilisateur)->get(route('categories.index'))->assertForbidden();
        $this->actingAs($utilisateur)->get(route('types-courrier.index'))->assertForbidden();
        $this->actingAs($utilisateur)->get(route('unites.index'))->assertForbidden();
    }
}
