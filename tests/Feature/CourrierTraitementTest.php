<?php

namespace Tests\Feature;

use App\Enums\ActionHistorique;
use App\Enums\ModeLivraison;
use App\Enums\StatutCourrier;
use App\Models\Courrier;
use App\Models\Unite;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourrierTraitementTest extends TestCase
{
    use RefreshDatabase;

    private function courrierReceptionnePour(User $agent): Courrier
    {
        $modele = Courrier::query()->where('statut', StatutCourrier::Depose)->firstOrFail();

        return Courrier::query()->create([
            'numero' => '2026-TRT-'.uniqid(),
            'contribuable_id' => $modele->contribuable_id,
            'categorie_courrier_id' => $modele->categorie_courrier_id,
            'type_courrier_id' => $modele->type_courrier_id,
            'objet' => 'Courrier de test traitement',
            'unite_destinataire_id' => $agent->unite_id,
            'unite_actuelle_id' => $agent->unite_id,
            'responsable_actuel_id' => $agent->id,
            'code_unite' => $agent->unite->code,
            'annee_enregistrement' => 2026,
            'sequence' => random_int(8000, 8999),
            'statut' => StatutCourrier::Receptionne,
            'date_depot' => now()->subDay(),
            'date_reception' => now()->toDateString(),
            'heure_reception' => now()->format('H:i:s'),
            'responsable_reception_id' => $agent->id,
            'user_id' => $agent->id,
            'priorite' => 'Moyenne',
        ]);
    }

    public function test_seul_un_courrier_receptionne_apparait_dans_le_traitement(): void
    {
        $this->seed();
        $agent = User::query()->where('email', 'agent.bureau1@dgi.ma')->firstOrFail();
        $depose = Courrier::query()->where('statut', StatutCourrier::Depose)->firstOrFail();
        $receptionne = $this->courrierReceptionnePour($agent);

        $this->actingAs($agent)
            ->get(route('courriers.traitement.index'))
            ->assertOk()
            ->assertSee($receptionne->numero)
            ->assertDontSee($depose->numero);
    }

    public function test_traitement_favorable_retourne_automatiquement_a_accueil(): void
    {
        $this->seed();
        $agent = User::query()->where('email', 'service.it@dgi.ma')->firstOrFail();
        $accueil = Unite::query()->where('code', 'BO001')->firstOrFail();
        $courrier = $this->courrierReceptionnePour($agent);

        $this->actingAs($agent)
            ->post(route('courriers.traiter', $courrier), [
                'resultat' => 'favorable',
                'date_traitement' => now()->toDateString(),
                'document_genere' => '1',
            ])
            ->assertRedirect(route('courriers.traitement.index'))
            ->assertSessionHas('success');

        $courrier->refresh();
        $this->assertSame(StatutCourrier::TraiteFavorable, $courrier->statut);
        $this->assertTrue($courrier->document_genere);
        $this->assertSame($accueil->id, $courrier->unite_actuelle_id);
        $this->assertSame($accueil->id, $courrier->unite_retour_id);
        $this->assertSame($agent->unite_id, $courrier->unite_destinataire_id);
        $this->assertNull($courrier->responsable_actuel_id);

        $this->assertDatabaseHas('courrier_historiques', [
            'courrier_id' => $courrier->id,
            'action' => ActionHistorique::DebutTraitement->value,
        ]);
        $this->assertDatabaseHas('courrier_historiques', [
            'courrier_id' => $courrier->id,
            'action' => ActionHistorique::TraitementFavorable->value,
        ]);
        $this->assertDatabaseHas('courrier_historiques', [
            'courrier_id' => $courrier->id,
            'action' => ActionHistorique::RetourAccueil->value,
        ]);

        $agentAccueil = User::query()->where('email', 'accueil@dgi.ma')->firstOrFail();
        $this->actingAs($agentAccueil)
            ->get(route('courriers.delivrance.index'))
            ->assertOk()
            ->assertSee($courrier->numero)
            ->assertSee('Documents à délivrer');
    }

    public function test_rejet_enregistre_le_motif_et_le_statut(): void
    {
        $this->seed();
        $agent = User::query()->where('email', 'agent.bureau1@dgi.ma')->firstOrFail();
        $courrier = $this->courrierReceptionnePour($agent);

        $this->actingAs($agent)
            ->post(route('courriers.traiter', $courrier), [
                'resultat' => 'rejet',
                'date_traitement' => now()->toDateString(),
                'motif_rejet' => 'Pièces justificatives insuffisantes',
            ])
            ->assertSessionHas('success');

        $courrier->refresh();
        $this->assertSame(StatutCourrier::Rejete, $courrier->statut);
        $this->assertSame('Pièces justificatives insuffisantes', $courrier->motif_rejet);
        $this->assertDatabaseHas('courrier_historiques', [
            'courrier_id' => $courrier->id,
            'action' => ActionHistorique::TraitementRejete->value,
        ]);
    }

    public function test_suspension_puis_reprise_permet_un_traitement_favorable(): void
    {
        $this->seed();
        $agent = User::query()->where('email', 'agent.bureau1@dgi.ma')->firstOrFail();
        $courrier = $this->courrierReceptionnePour($agent);

        $this->actingAs($agent)
            ->post(route('courriers.traiter', $courrier), [
                'resultat' => 'suspendu',
                'date_traitement' => now()->toDateString(),
                'motif_suspension' => 'Dossier incomplet — attente CIN',
            ])
            ->assertSessionHas('success');

        $courrier->refresh();
        $this->assertSame(StatutCourrier::Suspendu, $courrier->statut);

        $this->actingAs($agent)
            ->get(route('courriers.traitement.index'))
            ->assertOk()
            ->assertSee($courrier->numero)
            ->assertSee('Reprendre');

        $this->actingAs($agent)
            ->post(route('courriers.traiter', $courrier), [
                'resultat' => 'favorable',
                'date_traitement' => now()->toDateString(),
                'document_genere' => '1',
            ])
            ->assertSessionHas('error');

        $this->actingAs($agent)
            ->post(route('courriers.reprendre', $courrier))
            ->assertRedirect(route('courriers.traitement.show', $courrier))
            ->assertSessionHas('success');

        $courrier->refresh();
        $this->assertSame(StatutCourrier::Receptionne, $courrier->statut);
        $this->assertDatabaseHas('courrier_historiques', [
            'courrier_id' => $courrier->id,
            'action' => ActionHistorique::Suspension->value,
        ]);
        $this->assertDatabaseHas('courrier_historiques', [
            'courrier_id' => $courrier->id,
            'action' => ActionHistorique::Reprise->value,
        ]);

        $this->actingAs($agent)
            ->post(route('courriers.traiter', $courrier), [
                'resultat' => 'favorable',
                'date_traitement' => now()->toDateString(),
                'document_genere' => '1',
            ])
            ->assertSessionHas('success');

        $this->assertSame(StatutCourrier::TraiteFavorable, $courrier->fresh()->statut);
    }

    public function test_delivrance_attribue_numero_depart_unique_par_annee_et_cloture(): void
    {
        $this->seed();
        $agent = User::query()->where('email', 'agent.bureau1@dgi.ma')->firstOrFail();
        $agentAccueil = User::query()->where('email', 'accueil@dgi.ma')->firstOrFail();
        $courrier = $this->courrierReceptionnePour($agent);

        $this->actingAs($agent)->post(route('courriers.traiter', $courrier), [
            'resultat' => 'favorable',
            'date_traitement' => now()->toDateString(),
            'document_genere' => '1',
        ]);

        $this->actingAs($agentAccueil)
            ->post(route('courriers.delivrer', $courrier->fresh()), [
                'date_delivrance' => now()->toDateString(),
                'mode_livraison' => ModeLivraison::Interesse->value,
            ])
            ->assertRedirect(route('courriers.delivrance.index'))
            ->assertSessionHas('success');

        $courrier->refresh();
        $this->assertSame(StatutCourrier::Cloture, $courrier->statut);
        $this->assertSame(ModeLivraison::Interesse, $courrier->mode_livraison);
        $this->assertSame($agentAccueil->id, $courrier->delivre_par_id);
        $this->assertNotNull($courrier->date_delivrance);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{3}-BO001$/', (string) $courrier->numero_depart);

        $this->assertDatabaseHas('courrier_historiques', [
            'courrier_id' => $courrier->id,
            'action' => ActionHistorique::Delivrance->value,
        ]);
        $this->assertDatabaseHas('courrier_historiques', [
            'courrier_id' => $courrier->id,
            'action' => ActionHistorique::Cloture->value,
        ]);
    }

    public function test_delivrance_envoi_postal_exige_reference_envoi(): void
    {
        $this->seed();
        $agent = User::query()->where('email', 'agent.bureau1@dgi.ma')->firstOrFail();
        $agentAccueil = User::query()->where('email', 'accueil@dgi.ma')->firstOrFail();
        $courrier = $this->courrierReceptionnePour($agent);

        $this->actingAs($agent)->post(route('courriers.traiter', $courrier), [
            'resultat' => 'favorable',
            'date_traitement' => now()->toDateString(),
            'document_genere' => '1',
        ]);

        $this->actingAs($agentAccueil)
            ->post(route('courriers.delivrer', $courrier->fresh()), [
                'date_delivrance' => now()->toDateString(),
                'mode_livraison' => ModeLivraison::EnvoiPostal->value,
                'postal_cin' => 'AB123456',
                'postal_nom' => 'Dupont Paul',
            ])
            ->assertSessionHasErrors('reference_envoi');

        $this->actingAs($agentAccueil)
            ->post(route('courriers.delivrer', $courrier->fresh()), [
                'date_delivrance' => now()->toDateString(),
                'mode_livraison' => ModeLivraison::EnvoiPostal->value,
                'postal_cin' => 'AB123456',
                'postal_nom' => 'Dupont Paul',
                'reference_envoi' => 'LR123456789MA',
            ])
            ->assertSessionHas('success');

        $courrier->refresh();
        $this->assertSame('LR123456789MA', $courrier->reference_envoi);
        $this->assertSame(StatutCourrier::Cloture, $courrier->statut);
    }

    public function test_tracabilite_chronologique_sur_la_fiche(): void
    {
        $this->seed();
        $agent = User::query()->where('email', 'agent.bureau1@dgi.ma')->firstOrFail();
        $courrier = $this->courrierReceptionnePour($agent);

        $this->actingAs($agent)->post(route('courriers.traiter', $courrier), [
            'resultat' => 'favorable',
            'date_traitement' => now()->toDateString(),
            'document_genere' => '1',
        ]);

        $this->actingAs($agent)
            ->get(route('courriers.show', $courrier->fresh()))
            ->assertOk()
            ->assertSee('Traçabilité du dossier')
            ->assertSee('Début traitement')
            ->assertSee('Traitement favorable')
            ->assertSee('Retour à l\'Accueil');
    }

    public function test_un_non_accueil_ne_peut_pas_acceder_a_la_delivrance(): void
    {
        $this->seed();
        $agent = User::query()->where('email', 'agent.bureau1@dgi.ma')->firstOrFail();

        $this->actingAs($agent)
            ->get(route('courriers.delivrance.index'))
            ->assertForbidden();
    }

    public function test_accueil_ne_peut_pas_acceder_au_module_traitement_ni_historique(): void
    {
        $this->seed();
        $accueil = User::query()->where('email', 'accueil@dgi.ma')->firstOrFail();

        $this->assertFalse($accueil->hasPermission('menu.traitement'));
        $this->assertFalse($accueil->hasPermission('menu.reception'));
        $this->assertFalse(\Illuminate\Support\Facades\Gate::forUser($accueil)->allows('menu.traitement'));
        $this->assertFalse(\Illuminate\Support\Facades\Gate::forUser($accueil)->allows('menu.reception'));

        $this->actingAs($accueil)
            ->get(route('courriers.traitement.index'))
            ->assertForbidden();

        $this->actingAs($accueil)
            ->get(route('courriers.reception.index'))
            ->assertForbidden();

        $this->actingAs($accueil)
            ->get(route('courriers.delivrance.index'))
            ->assertOk();
    }

    public function test_historique_contient_favorable_et_rejet_mais_pas_la_file_active(): void
    {
        $this->seed();
        $agent = User::query()->where('email', 'agent.bureau1@dgi.ma')->firstOrFail();
        $favorable = $this->courrierReceptionnePour($agent);
        $rejete = $this->courrierReceptionnePour($agent);
        $enCours = $this->courrierReceptionnePour($agent);

        $this->actingAs($agent)->post(route('courriers.traiter', $favorable), [
            'resultat' => 'favorable',
            'date_traitement' => now()->toDateString(),
            'document_genere' => '1',
        ]);

        $this->actingAs($agent)->post(route('courriers.traiter', $rejete), [
            'resultat' => 'rejet',
            'date_traitement' => now()->toDateString(),
            'motif_rejet' => 'Dossier incomplet',
        ]);

        $this->actingAs($agent)
            ->get(route('courriers.traitement.index'))
            ->assertOk()
            ->assertSee('Historique de mes traitements')
            ->assertSee($favorable->numero)
            ->assertSee($rejete->numero)
            ->assertSee($enCours->numero)
            ->assertSee('Favorable')
            ->assertSee('Rejeté');

        $this->assertSame(StatutCourrier::TraiteFavorable, $favorable->fresh()->statut);
        $this->assertSame(StatutCourrier::Rejete, $rejete->fresh()->statut);
        $this->assertNull($favorable->fresh()->responsable_actuel_id);
        $this->assertNull($rejete->fresh()->responsable_actuel_id);
        $this->assertSame(StatutCourrier::Receptionne, $enCours->fresh()->statut);

        $this->actingAs($agent)
            ->get(route('courriers.traitement.show', $favorable->fresh()))
            ->assertOk();
    }

    public function test_suspendu_reste_dans_la_file_et_apparait_dans_l_historique(): void
    {
        $this->seed();
        $agent = User::query()->where('email', 'agent.bureau1@dgi.ma')->firstOrFail();
        $courrier = $this->courrierReceptionnePour($agent);

        $this->actingAs($agent)->post(route('courriers.traiter', $courrier), [
            'resultat' => 'suspendu',
            'date_traitement' => now()->toDateString(),
            'motif_suspension' => 'Attente pièce',
        ]);

        $this->actingAs($agent)
            ->get(route('courriers.traitement.index'))
            ->assertOk()
            ->assertSee($courrier->numero)
            ->assertSee('Reprendre')
            ->assertSee('Historique de mes traitements')
            ->assertSee('Suspendu');

        $this->assertSame(StatutCourrier::Suspendu, $courrier->fresh()->statut);
        $this->assertSame($agent->id, $courrier->fresh()->responsable_actuel_id);
        $this->assertDatabaseHas('courrier_historiques', [
            'courrier_id' => $courrier->id,
            'action' => ActionHistorique::Suspension->value,
        ]);
    }

    public function test_apres_reprise_le_courrier_quitte_l_historique_jusqu_au_prochain_traitement(): void
    {
        $this->seed();
        $agent = User::query()->where('email', 'agent.bureau1@dgi.ma')->firstOrFail();
        $courrier = $this->courrierReceptionnePour($agent);

        $this->actingAs($agent)->post(route('courriers.traiter', $courrier), [
            'resultat' => 'suspendu',
            'date_traitement' => now()->toDateString(),
            'motif_suspension' => 'Attente',
        ]);

        $this->assertTrue(
            Courrier::query()
                ->whereKey($courrier->id)
                ->where('traite_par_id', $agent->id)
                ->whereIn('statut', StatutCourrier::pourHistoriqueTraitement())
                ->exists()
        );

        $this->actingAs($agent)->post(route('courriers.reprendre', $courrier));

        $this->assertSame(StatutCourrier::Receptionne, $courrier->fresh()->statut);
        $this->assertFalse(
            Courrier::query()
                ->whereKey($courrier->id)
                ->whereIn('statut', StatutCourrier::pourHistoriqueTraitement())
                ->exists()
        );

        $this->actingAs($agent)
            ->get(route('courriers.traitement.index'))
            ->assertOk()
            ->assertSee($courrier->numero)
            ->assertSee('Traiter');
    }
}
