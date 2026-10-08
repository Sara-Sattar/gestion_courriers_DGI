<?php

namespace Tests\Feature;

use App\Enums\MotifDelegation;
use App\Enums\StatutCourrier;
use App\Enums\StatutDelegation;
use App\Models\Courrier;
use App\Models\Delegation;
use App\Models\HistoriqueAffectation;
use App\Models\Unite;
use App\Models\User;
use App\Services\DelegationContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DelegationTest extends TestCase
{
    use RefreshDatabase;

    private function chefBureau(): User
    {
        return User::query()->where('email', 'bureau@dgi.ma')->firstOrFail();
    }

    private function creerDelegation(array $attributs = []): Delegation
    {
        $bureau = Unite::query()->where('code', 'BO001')->firstOrFail();
        $absent = User::query()->where('email', 'agent.bureau1@dgi.ma')->firstOrFail();
        $remplacant = User::query()->where('email', 'agent.bureau2@dgi.ma')->firstOrFail();
        $chef = $this->chefBureau();

        return Delegation::query()->create(array_merge([
            'agent_absent_id' => $absent->id,
            'agent_remplacant_id' => $remplacant->id,
            'unite_id' => $bureau->id,
            'date_debut' => now()->toDateString(),
            'date_fin' => now()->addDays(7)->toDateString(),
            'motif' => MotifDelegation::Conge,
            'statut' => StatutDelegation::Active,
            'created_by' => $chef->id,
        ], $attributs));
    }

    private function courrierDeposePour(User $utilisateur): Courrier
    {
        $modele = Courrier::query()->where('statut', StatutCourrier::Depose)->firstOrFail();

        return Courrier::query()->create([
            'numero' => '2026-DEL-'.uniqid(),
            'contribuable_id' => $modele->contribuable_id,
            'categorie_courrier_id' => $modele->categorie_courrier_id,
            'type_courrier_id' => $modele->type_courrier_id,
            'objet' => 'Courrier test délégation',
            'unite_destinataire_id' => $utilisateur->unite_id,
            'unite_actuelle_id' => $utilisateur->unite_id,
            'responsable_actuel_id' => null,
            'code_unite' => $utilisateur->unite->code,
            'annee_enregistrement' => 2026,
            'sequence' => random_int(8000, 8999),
            'statut' => StatutCourrier::Depose,
            'date_depot' => now(),
            'user_id' => $utilisateur->id,
            'priorite' => 'normale',
        ]);
    }

    public function test_chef_bureau_peut_lister_et_creer_une_delegation(): void
    {
        $this->seed();
        $chef = $this->chefBureau();
        $bureau = Unite::query()->where('code', 'BO001')->firstOrFail();
        $absent = User::query()->where('email', 'agent.bureau1@dgi.ma')->firstOrFail();
        $remplacant = User::query()->where('email', 'agent.bureau2@dgi.ma')->firstOrFail();

        $this->actingAs($chef)
            ->get(route('delegations.index'))
            ->assertOk()
            ->assertSee('Gestion des délégations')
            ->assertSee('Délégant');

        $this->actingAs($chef)
            ->post(route('delegations.store'), [
                'unite_id' => $bureau->id,
                'agent_absent_id' => $absent->id,
                'agent_remplacant_id' => $remplacant->id,
                'date_debut' => now()->toDateString(),
                'date_fin' => now()->addDays(5)->toDateString(),
                'motif' => MotifDelegation::Maladie->value,
            ])
            ->assertRedirect(route('delegations.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('delegations', [
            'agent_absent_id' => $absent->id,
            'agent_remplacant_id' => $remplacant->id,
            'created_by' => $chef->id,
            'statut' => StatutDelegation::Active->value,
        ]);
    }

    public function test_admin_et_accueil_ne_gerent_pas_les_delegations_metier(): void
    {
        $this->seed();
        $admin = User::query()->where('email', 'admin@dgi.ma')->firstOrFail();
        $accueil = User::query()->where('email', 'accueil@dgi.ma')->firstOrFail();

        $this->actingAs($admin)->get(route('delegations.index'))->assertForbidden();
        $this->actingAs($accueil)->get(route('delegations.index'))->assertForbidden();
    }

    public function test_delegation_future_est_planifiee(): void
    {
        $this->seed();
        $chef = $this->chefBureau();
        $bureau = Unite::query()->where('code', 'BO001')->firstOrFail();
        $absent = User::query()->where('email', 'agent.bureau1@dgi.ma')->firstOrFail();
        $remplacant = User::query()->where('email', 'agent.bureau2@dgi.ma')->firstOrFail();

        $this->actingAs($chef)
            ->post(route('delegations.store'), [
                'unite_id' => $bureau->id,
                'agent_absent_id' => $absent->id,
                'agent_remplacant_id' => $remplacant->id,
                'date_debut' => now()->addDays(3)->toDateString(),
                'date_fin' => now()->addDays(10)->toDateString(),
                'motif' => MotifDelegation::Conge->value,
            ])
            ->assertRedirect(route('delegations.index'));

        $this->assertDatabaseHas('delegations', [
            'agent_absent_id' => $absent->id,
            'statut' => StatutDelegation::Planifiee->value,
        ]);
    }

    public function test_refuse_delegation_hors_perimetre(): void
    {
        $this->seed();
        $chef = $this->chefBureau();
        $bureau = Unite::query()->where('code', 'BO001')->firstOrFail();
        $agentBureau = User::query()->where('email', 'agent.bureau1@dgi.ma')->firstOrFail();
        $agentService = User::query()->where('email', 'agent.service1@dgi.ma')->firstOrFail();

        $this->actingAs($chef)
            ->post(route('delegations.store'), [
                'unite_id' => $bureau->id,
                'agent_absent_id' => $agentBureau->id,
                'agent_remplacant_id' => $agentService->id,
                'date_debut' => now()->toDateString(),
                'date_fin' => now()->addDays(3)->toDateString(),
                'motif' => MotifDelegation::Mission->value,
            ])
            ->assertSessionHasErrors();
    }

    public function test_refuse_chevauchement_de_periodes(): void
    {
        $this->seed();
        $chef = $this->chefBureau();
        $this->creerDelegation();
        $bureau = Unite::query()->where('code', 'BO001')->firstOrFail();
        $absent = User::query()->where('email', 'agent.bureau1@dgi.ma')->firstOrFail();
        $remplacant = User::query()->where('email', 'agent.bureau2@dgi.ma')->firstOrFail();

        $this->actingAs($chef)
            ->post(route('delegations.store'), [
                'unite_id' => $bureau->id,
                'agent_absent_id' => $absent->id,
                'agent_remplacant_id' => $remplacant->id,
                'date_debut' => now()->addDays(2)->toDateString(),
                'date_fin' => now()->addDays(10)->toDateString(),
                'motif' => MotifDelegation::Formation->value,
            ])
            ->assertSessionHasErrors('date_debut');
    }

    public function test_annulation_conserve_l_historique(): void
    {
        $this->seed();
        $chef = $this->chefBureau();
        $delegation = $this->creerDelegation([
            'date_debut' => now()->addDays(2)->toDateString(),
            'date_fin' => now()->addDays(8)->toDateString(),
            'statut' => StatutDelegation::Planifiee,
        ]);

        $this->actingAs($chef)
            ->delete(route('delegations.destroy', $delegation))
            ->assertRedirect(route('delegations.index'));

        $this->assertDatabaseHas('delegations', [
            'id' => $delegation->id,
            'statut' => StatutDelegation::Annulee->value,
        ]);
    }

    public function test_modification_refusee_si_active(): void
    {
        $this->seed();
        $chef = $this->chefBureau();
        $delegation = $this->creerDelegation();

        $this->actingAs($chef)
            ->get(route('delegations.edit', $delegation))
            ->assertForbidden();
    }

    public function test_utilisateur_sans_permission_ne_voit_pas_le_menu(): void
    {
        $this->seed();
        $agent = User::query()->where('email', 'agent.bureau1@dgi.ma')->firstOrFail();

        $this->actingAs($agent)
            ->get(route('delegations.index'))
            ->assertForbidden();
    }

    public function test_un_agent_meme_profil_unite_ne_gere_pas_les_delegations(): void
    {
        $this->seed();

        // Même si un agent porte encore un profil « Section », seul le responsable d'unité gère.
        $agentSection = User::query()->where('email', 'agent.section1@dgi.ma')->firstOrFail();
        $this->assertFalse(app(\App\Services\DelegationService::class)->estResponsableHierarchique($agentSection));

        $this->actingAs($agentSection)
            ->get(route('delegations.index'))
            ->assertForbidden();
    }

    public function test_remplacant_peut_basculer_et_agir_pour_le_compte_de_l_absent(): void
    {
        $this->seed();
        $delegation = $this->creerDelegation();
        $remplacant = $delegation->agentRemplacant;
        $absent = $delegation->agentAbsent;
        $courrier = $this->courrierDeposePour($absent);

        $this->actingAs($remplacant)
            ->post(route('espace-travail.choisir'), ['espace' => $delegation->id])
            ->assertRedirect()
            ->assertSessionHas(DelegationContext::SESSION_ESPACE, $delegation->id);

        $context = app(DelegationContext::class);
        $this->assertTrue($context->enRemplacement());
        $this->assertSame($absent->id, $context->sujet()->id);
        $this->assertSame($remplacant->id, $context->acteur()->id);

        $this->actingAs($remplacant)
            ->post(route('courriers.confirmer-reception', $courrier))
            ->assertRedirect(route('courriers.reception.index'));

        $courrier->refresh();
        $this->assertSame(StatutCourrier::Receptionne, $courrier->statut);
        $this->assertSame($absent->id, $courrier->responsable_actuel_id);
        $this->assertSame($absent->id, $courrier->responsable_reception_id);

        $this->assertDatabaseHas('historique_affectations', [
            'courrier_id' => $courrier->id,
            'user_id' => $remplacant->id,
            'pour_compte_de_id' => $absent->id,
        ]);

        $this->assertDatabaseHas('courrier_historiques', [
            'courrier_id' => $courrier->id,
            'user_id' => $remplacant->id,
            'pour_compte_de_id' => $absent->id,
        ]);
    }

    public function test_plusieurs_delegations_permettent_de_choisir_l_espace(): void
    {
        $this->seed();
        $bureau = Unite::query()->where('code', 'BO001')->firstOrFail();
        $chef = $this->chefBureau();
        $agent1 = User::query()->where('email', 'agent.bureau1@dgi.ma')->firstOrFail();
        $agent2 = User::query()->where('email', 'agent.bureau2@dgi.ma')->firstOrFail();

        $d1 = Delegation::query()->create([
            'agent_absent_id' => $agent1->id,
            'agent_remplacant_id' => $chef->id,
            'unite_id' => $bureau->id,
            'date_debut' => now()->toDateString(),
            'date_fin' => now()->addDays(5)->toDateString(),
            'motif' => MotifDelegation::Conge,
            'statut' => StatutDelegation::Active,
            'created_by' => $chef->id,
        ]);

        $d2 = Delegation::query()->create([
            'agent_absent_id' => $agent2->id,
            'agent_remplacant_id' => $chef->id,
            'unite_id' => $bureau->id,
            'date_debut' => now()->toDateString(),
            'date_fin' => now()->addDays(5)->toDateString(),
            'motif' => MotifDelegation::Mission,
            'statut' => StatutDelegation::Active,
            'created_by' => $chef->id,
        ]);

        $this->actingAs($chef)
            ->post(route('espace-travail.choisir'), ['espace' => $d1->id])
            ->assertRedirect();

        $this->assertSame($agent1->id, app(DelegationContext::class)->sujet()->id);

        $this->actingAs($chef)
            ->post(route('espace-travail.choisir'), ['espace' => $d2->id])
            ->assertRedirect();

        $this->assertSame($agent2->id, app(DelegationContext::class)->sujet()->id);

        $this->actingAs($chef)
            ->post(route('espace-travail.choisir'), ['espace' => 'self'])
            ->assertRedirect();

        $this->assertFalse(app(DelegationContext::class)->enRemplacement());
        $this->assertSame($chef->id, app(DelegationContext::class)->sujet()->id);
    }

    public function test_expiration_automatique_des_delegations(): void
    {
        $this->seed();
        $delegation = $this->creerDelegation([
            'date_debut' => now()->subDays(10)->toDateString(),
            'date_fin' => now()->subDay()->toDateString(),
        ]);

        $this->artisan('delegations:expirer')->assertSuccessful();

        $this->assertSame(StatutDelegation::Terminee, $delegation->fresh()->statut);
    }

    public function test_activation_automatique_des_planifiees(): void
    {
        $this->seed();
        $delegation = $this->creerDelegation([
            'date_debut' => now()->toDateString(),
            'date_fin' => now()->addDays(5)->toDateString(),
            'statut' => StatutDelegation::Planifiee,
        ]);

        $this->artisan('delegations:expirer')->assertSuccessful();

        $this->assertSame(StatutDelegation::Active, $delegation->fresh()->statut);
    }

    public function test_sans_delegation_le_comportement_reste_inchangé(): void
    {
        $this->seed();
        $agent = User::query()->where('email', 'agent.bureau1@dgi.ma')->firstOrFail();
        $courrier = $this->courrierDeposePour($agent);

        $this->actingAs($agent)
            ->post(route('courriers.confirmer-reception', $courrier))
            ->assertRedirect();

        $this->assertSame($agent->id, $courrier->fresh()->responsable_actuel_id);
        $this->assertNull(HistoriqueAffectation::query()
            ->where('courrier_id', $courrier->id)
            ->value('pour_compte_de_id'));
    }
}
