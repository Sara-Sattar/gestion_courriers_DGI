<?php

namespace Tests\Feature;

use App\Enums\ActionHistorique;
use App\Enums\ActionType;
use App\Enums\StatutCourrier;
use App\Models\Courrier;
use App\Models\CourrierHistorique;
use App\Models\Unite;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourrierReceptionTest extends TestCase
{
    use RefreshDatabase;

    private function agentReception(): User
    {
        return User::query()->where('email', 'agent.bureau1@dgi.ma')->firstOrFail();
    }

    private function courrierDeposePour(User $utilisateur, array $attributs = []): Courrier
    {
        $modele = Courrier::query()->where('statut', StatutCourrier::Depose)->firstOrFail();

        return Courrier::query()->create(array_merge([
            'numero' => '2026-TEST-'.uniqid(),
            'contribuable_id' => $modele->contribuable_id,
            'categorie_courrier_id' => $modele->categorie_courrier_id,
            'type_courrier_id' => $modele->type_courrier_id,
            'objet' => 'Courrier de test réception',
            'unite_destinataire_id' => $utilisateur->unite_id,
            'unite_actuelle_id' => $utilisateur->unite_id,
            'responsable_actuel_id' => null,
            'code_unite' => $utilisateur->unite->code,
            'annee_enregistrement' => 2026,
            'sequence' => random_int(9000, 9999),
            'statut' => StatutCourrier::Depose,
            'date_depot' => now(),
            'user_id' => $utilisateur->id,
            'priorite' => 'normale',
        ], $attributs));
    }

    public function test_la_page_reception_liste_les_deposes_du_jour(): void
    {
        $this->seed();
        $utilisateur = $this->agentReception();
        $courrier = $this->courrierDeposePour($utilisateur);

        $this->actingAs($utilisateur)
            ->get(route('courriers.reception.index'))
            ->assertOk()
            ->assertSee($courrier->numero)
            ->assertSee('Confirmer la réception')
            ->assertSee('En instance de réception')
            ->assertSee('Historique des réceptions');
    }

    public function test_confirmer_la_reception_passe_au_statut_receptionne(): void
    {
        $this->seed();
        $utilisateur = $this->agentReception();
        $courrier = $this->courrierDeposePour($utilisateur);

        $this->actingAs($utilisateur)
            ->post(route('courriers.confirmer-reception', $courrier))
            ->assertRedirect(route('courriers.reception.index'))
            ->assertSessionHas('success');

        $courrier->refresh();

        $this->assertSame(StatutCourrier::Receptionne, $courrier->statut);
        $this->assertNotNull($courrier->date_reception);
        $this->assertNotNull($courrier->heure_reception);
        $this->assertSame($utilisateur->id, $courrier->responsable_reception_id);
        $this->assertSame($utilisateur->id, $courrier->responsable_actuel_id);
        $this->assertSame($utilisateur->unite_id, $courrier->unite_actuelle_id);

        $this->assertDatabaseHas('courrier_historiques', [
            'courrier_id' => $courrier->id,
            'user_id' => $utilisateur->id,
            'action' => ActionHistorique::Reception->value,
        ]);

        $this->assertDatabaseHas('historique_affectations', [
            'courrier_id' => $courrier->id,
            'user_id' => $utilisateur->id,
            'action_type' => ActionType::Reception->value,
            'unite_destination_id' => $utilisateur->unite_id,
        ]);
    }

    public function test_la_reception_en_masse_fonctionne(): void
    {
        $this->seed();
        $utilisateur = $this->agentReception();
        $a = $this->courrierDeposePour($utilisateur);
        $b = $this->courrierDeposePour($utilisateur);

        $this->actingAs($utilisateur)
            ->post(route('courriers.receptionner'), [
                'courrier_ids' => [$a->id, $b->id],
            ])
            ->assertRedirect(route('courriers.reception.index'));

        $this->assertSame(StatutCourrier::Receptionne, $a->fresh()->statut);
        $this->assertSame(StatutCourrier::Receptionne, $b->fresh()->statut);
    }

    public function test_annuler_la_reception_remet_le_statut_depose_et_conserve_l_historique(): void
    {
        $this->seed();
        $utilisateur = $this->agentReception();
        $courrier = $this->courrierDeposePour($utilisateur);

        $this->actingAs($utilisateur)->post(route('courriers.confirmer-reception', $courrier));
        $this->actingAs($utilisateur)
            ->post(route('courriers.annuler-reception', $courrier))
            ->assertSessionHas('success');

        $courrier->refresh();
        $this->assertSame(StatutCourrier::Depose, $courrier->statut);
        $this->assertNull($courrier->date_reception);
        $this->assertNull($courrier->responsable_reception_id);
        $this->assertNull($courrier->responsable_actuel_id);

        $this->assertSame(2, CourrierHistorique::query()->where('courrier_id', $courrier->id)->count());
        $this->assertDatabaseHas('historique_affectations', [
            'courrier_id' => $courrier->id,
            'action_type' => ActionType::AnnulationReception->value,
        ]);
    }

    public function test_la_reaffectation_met_a_jour_l_unite_et_l_historique(): void
    {
        $this->seed();
        $utilisateur = $this->agentReception();
        $courrier = $this->courrierDeposePour($utilisateur);
        $brigade = Unite::query()->where('code', 'BRIG001')->firstOrFail();

        $this->actingAs($utilisateur)->post(route('courriers.confirmer-reception', $courrier));

        $this->actingAs($utilisateur)
            ->post(route('courriers.reaffecter', $courrier), [
                'unite_destination_id' => $brigade->id,
                'motif' => 'Transfert vers la brigade de vérification',
            ])
            ->assertSessionHas('success');

        $courrier->refresh();
        $this->assertSame(StatutCourrier::Reaffecte, $courrier->statut);
        $this->assertSame($brigade->id, $courrier->unite_actuelle_id);
        $this->assertSame($brigade->id, $courrier->unite_destinataire_id);
        $this->assertNull($courrier->date_reception);
        $this->assertNull($courrier->responsable_reception_id);
        $this->assertNull($courrier->responsable_actuel_id);

        $this->assertDatabaseHas('historique_affectations', [
            'courrier_id' => $courrier->id,
            'action_type' => ActionType::Reaffectation->value,
            'unite_source_id' => $utilisateur->unite_id,
            'unite_destination_id' => $brigade->id,
            'motif' => 'Transfert vers la brigade de vérification',
            'user_id' => $utilisateur->id,
            'nouveau_responsable_id' => null,
        ]);

        // Tous les agents de la brigade doivent le retrouver.
        foreach (User::query()->where('unite_id', $brigade->id)->get() as $agent) {
            $this->actingAs($agent)
                ->get(route('courriers.reception.index'))
                ->assertOk()
                ->assertSee($courrier->numero);
        }
    }

    public function test_la_reaffectation_est_bloquee_tant_que_la_reception_n_est_pas_confirmee(): void
    {
        $this->seed();
        $utilisateur = $this->agentReception();
        $courrier = $this->courrierDeposePour($utilisateur);
        $brigade = Unite::query()->where('code', 'BRIG001')->firstOrFail();

        // Aucune action de réaffectation tant que la réception n'est pas confirmée.
        $page = $this->actingAs($utilisateur)
            ->get(route('courriers.reception.index'))
            ->assertOk()
            ->assertSee($courrier->numero)
            ->getContent();
        $this->assertDoesNotMatchRegularExpression('/data-reaffecter\s+data-courrier-id="'.$courrier->id.'"/', $page);

        $this->actingAs($utilisateur)
            ->post(route('courriers.reaffecter', $courrier), [
                'unite_destination_id' => $brigade->id,
                'motif' => 'Réaffectation sans réception préalable',
            ])
            ->assertSessionHas('error', 'Confirmez d\'abord la réception de ce courrier avant de le réaffecter.');

        $courrier->refresh();
        $this->assertSame(StatutCourrier::Depose, $courrier->statut);
        $this->assertSame($utilisateur->unite_id, $courrier->unite_actuelle_id);
        $this->assertDatabaseMissing('historique_affectations', [
            'courrier_id' => $courrier->id,
            'action_type' => ActionType::Reaffectation->value,
        ]);
    }

    public function test_apres_confirmation_la_reaffectation_est_proposee_dans_l_historique(): void
    {
        $this->seed();
        $agent1 = User::query()->where('email', 'agent.bureau1@dgi.ma')->firstOrFail();
        $agent2 = User::query()->where('email', 'agent.bureau2@dgi.ma')->firstOrFail();
        $brigade = Unite::query()->where('code', 'BRIG001')->firstOrFail();
        $courrier = $this->courrierDeposePour($agent1);
        $boutonReaffecter = '/data-reaffecter\s+data-courrier-id="'.$courrier->id.'"/';

        $this->actingAs($agent1)->post(route('courriers.confirmer-reception', $courrier))
            ->assertSessionHas('success');

        // Le responsable retrouve l'action dans « Historique des réceptions ».
        $this->actingAs($agent1)
            ->get(route('courriers.reception.index'))
            ->assertOk()
            ->assertSeeInOrder(['Historique des réceptions', $courrier->numero, 'Historique des réaffectations']);
        $page = $this->actingAs($agent1)->get(route('courriers.reception.index'))->getContent();
        $historique = substr($page, strpos($page, 'Historique des réceptions'), strpos($page, 'Historique des réaffectations') - strpos($page, 'Historique des réceptions'));
        $this->assertMatchesRegularExpression($boutonReaffecter, $historique);

        // Un collègue de la même unité ne la voit pas.
        $this->assertDoesNotMatchRegularExpression(
            $boutonReaffecter,
            $this->actingAs($agent2)->get(route('courriers.reception.index'))->assertOk()->getContent()
        );

        $this->actingAs($agent1)
            ->post(route('courriers.reaffecter', $courrier), [
                'unite_destination_id' => $brigade->id,
                'motif' => 'Transfert après réception confirmée',
            ])
            ->assertSessionHas('success');

        $this->assertSame(StatutCourrier::Reaffecte, $courrier->fresh()->statut);

        // Une fois réaffecté, l'action disparaît de l'historique.
        $this->assertDoesNotMatchRegularExpression(
            $boutonReaffecter,
            $this->actingAs($agent1)->get(route('courriers.reception.index'))->getContent()
        );
    }

    public function test_la_reaffectation_fonctionne_quand_l_unite_cible_a_deja_la_meme_sequence(): void
    {
        $this->seed();
        $agent = $this->agentReception();
        $brigade = Unite::query()->where('code', 'BRIG001')->firstOrFail();

        // Même séquence annuelle dans les deux unités (numérotation propre à chaque unité).
        $courrier = $this->courrierDeposePour($agent, ['sequence' => 777]);
        $this->courrierDeposePour($agent, [
            'sequence' => 777,
            'unite_destinataire_id' => $brigade->id,
            'unite_actuelle_id' => $brigade->id,
            'code_unite' => $brigade->code,
        ]);

        $this->actingAs($agent)->post(route('courriers.confirmer-reception', $courrier));
        $this->actingAs($agent)
            ->post(route('courriers.reaffecter', $courrier), [
                'unite_destination_id' => $brigade->id,
                'motif' => 'Transfert vers la brigade',
            ])
            ->assertSessionHas('success');

        $this->assertSame(StatutCourrier::Reaffecte, $courrier->fresh()->statut);
        $this->assertSame($brigade->id, $courrier->fresh()->unite_destinataire_id);
    }

    public function test_seul_l_agent_qui_a_receptionne_peut_reaffecter_ou_annuler(): void
    {
        $this->seed();
        $agent1 = User::query()->where('email', 'agent.bureau1@dgi.ma')->firstOrFail();
        $agent2 = User::query()->where('email', 'agent.bureau2@dgi.ma')->firstOrFail();
        $brigade = Unite::query()->where('code', 'BRIG001')->firstOrFail();
        $courrier = $this->courrierDeposePour($agent1);

        $this->actingAs($agent1)->post(route('courriers.confirmer-reception', $courrier));

        $this->actingAs($agent2)
            ->post(route('courriers.reaffecter', $courrier), [
                'unite_destination_id' => $brigade->id,
                'motif' => 'Réaffectation par un collègue',
            ])
            ->assertSessionHas('error', 'Seul l\'agent qui a réceptionné ce courrier peut effectuer cette action.');

        $this->actingAs($agent2)
            ->post(route('courriers.annuler-reception', $courrier))
            ->assertSessionHas('error', 'Seul l\'agent qui a réceptionné ce courrier peut effectuer cette action.');

        $courrier->refresh();
        $this->assertSame(StatutCourrier::Receptionne, $courrier->statut);
        $this->assertSame($agent1->id, $courrier->responsable_actuel_id);
        $this->assertSame($agent1->unite_id, $courrier->unite_actuelle_id);
    }

    public function test_un_courrier_reaffecte_apparait_chez_le_destinataire_a_confirmer(): void
    {
        $this->seed();
        $chefBrigade = User::query()->where('email', 'brigade@dgi.ma')->firstOrFail();
        $secteur = Unite::query()->where('code', 'SECT001')->firstOrFail();
        $agentSecteur = User::query()->where('email', 'secteur.gueliz@dgi.ma')->firstOrFail();

        $courrier = $this->courrierDeposePour($chefBrigade);

        $this->actingAs($chefBrigade)->post(route('courriers.confirmer-reception', $courrier));
        $this->actingAs($chefBrigade)
            ->post(route('courriers.reaffecter', $courrier), [
                'unite_destination_id' => $secteur->id,
                'motif' => 'Orientation vers le secteur fiscal Guéliz',
            ])
            ->assertSessionHas('success');

        $this->assertSame(StatutCourrier::Reaffecte, $courrier->fresh()->statut);
        $this->assertSame($secteur->id, $courrier->fresh()->unite_actuelle_id);
        $this->assertNull($courrier->fresh()->responsable_actuel_id);

        $this->actingAs($agentSecteur)
            ->get(route('courriers.reception.index'))
            ->assertOk()
            ->assertSee($courrier->numero)
            ->assertSee('À confirmer');
    }

    public function test_tous_les_agents_du_bureau_voient_un_courrier_reaffecte(): void
    {
        $this->seed();
        $agent = $this->agentReception();
        $serviceIt = Unite::query()->where('code', 'SERV002')->firstOrFail();
        $agentsService = User::query()->where('unite_id', $serviceIt->id)->get();
        $this->assertGreaterThanOrEqual(1, $agentsService->count());

        $courrier = $this->courrierDeposePour($agent);
        $this->actingAs($agent)->post(route('courriers.confirmer-reception', $courrier));
        $this->actingAs($agent)
            ->post(route('courriers.reaffecter', $courrier), [
                'unite_destination_id' => $serviceIt->id,
                'motif' => 'Transmission au service IT pour traitement',
            ])
            ->assertSessionHas('success');

        foreach ($agentsService as $agentService) {
            $this->actingAs($agentService)
                ->get(route('courriers.reception.index'))
                ->assertOk()
                ->assertSee($courrier->numero);
        }
    }

    public function test_un_bureau_peut_reaffecter_vers_un_secteur(): void
    {
        $this->seed();
        $chefBureau = User::query()->where('email', 'bureau@dgi.ma')->firstOrFail();
        $secteur = Unite::query()->where('code', 'SECT001')->firstOrFail();

        $courrier = $this->courrierDeposePour($chefBureau);
        $this->actingAs($chefBureau)->post(route('courriers.confirmer-reception', $courrier));
        $this->actingAs($chefBureau)
            ->post(route('courriers.reaffecter', $courrier), [
                'unite_destination_id' => $secteur->id,
                'motif' => 'Orientation vers le secteur fiscal',
            ])
            ->assertSessionHas('success');

        $courrier->refresh();
        $this->assertSame(StatutCourrier::Reaffecte, $courrier->statut);
        $this->assertSame($secteur->id, $courrier->unite_actuelle_id);

        foreach (User::query()->where('unite_id', $secteur->id)->get() as $agent) {
            $this->actingAs($agent)
                ->get(route('courriers.reception.index'))
                ->assertOk()
                ->assertSee($courrier->numero);
        }
    }

    public function test_un_bureau_ne_peut_pas_reaffecter_vers_un_autre_bureau(): void
    {
        $this->seed();
        $chefRecouvrement = User::query()->where('email', 'recouvrement@dgi.ma')->firstOrFail();
        $bo = Unite::query()->where('code', 'BO001')->firstOrFail();

        $courrier = $this->courrierDeposePour($chefRecouvrement);
        $this->actingAs($chefRecouvrement)->post(route('courriers.confirmer-reception', $courrier));
        $this->actingAs($chefRecouvrement)
            ->post(route('courriers.reaffecter', $courrier), [
                'unite_destination_id' => $bo->id,
                'motif' => 'Tentative de réaffectation vers un autre bureau',
            ])
            ->assertSessionHas('error');
    }

    public function test_un_secteur_ne_peut_pas_reaffecter_vers_une_brigade(): void
    {
        $this->seed();
        $agentSecteur = User::query()->where('email', 'secteur.gueliz@dgi.ma')->firstOrFail();
        $brigade = Unite::query()->where('code', 'BRIG001')->firstOrFail();

        $courrier = $this->courrierDeposePour($agentSecteur);
        $this->actingAs($agentSecteur)->post(route('courriers.confirmer-reception', $courrier));

        $this->actingAs($agentSecteur)
            ->post(route('courriers.reaffecter', $courrier), [
                'unite_destination_id' => $brigade->id,
                'motif' => 'Orientation libre vers la brigade de vérification',
            ])
            ->assertSessionHas('error');

        $courrier->refresh();
        $this->assertSame($agentSecteur->unite_id, $courrier->unite_actuelle_id);
        $this->assertSame(StatutCourrier::Receptionne, $courrier->statut);
    }

    public function test_refus_reaffectation_retourne_vers_unite_precedente(): void
    {
        $this->seed();
        $utilisateur = $this->agentReception();
        $brigade = Unite::query()->where('code', 'BRIG001')->firstOrFail();
        $agentBrigade = User::query()->where('email', 'brigade@dgi.ma')->firstOrFail();

        $courrier = $this->courrierDeposePour($utilisateur);
        $this->actingAs($utilisateur)->post(route('courriers.confirmer-reception', $courrier));
        $this->actingAs($utilisateur)
            ->post(route('courriers.reaffecter', $courrier), [
                'unite_destination_id' => $brigade->id,
                'motif' => 'Transfert pour test de refus',
            ])
            ->assertSessionHas('success');

        $this->actingAs($agentBrigade)
            ->post(route('courriers.refuser-reaffectation', $courrier), [
                'motif' => 'Destination incorrecte pour ce dossier',
            ])
            ->assertSessionHas('success');

        $courrier->refresh();
        $this->assertSame($utilisateur->unite_id, $courrier->unite_actuelle_id);
        $this->assertSame(StatutCourrier::Depose, $courrier->statut);
    }

    public function test_on_ne_peut_pas_reaffecter_vers_sa_propre_unite(): void
    {
        $this->seed();
        $chefBureau = User::query()->where('email', 'bureau@dgi.ma')->firstOrFail();
        $courrier = $this->courrierDeposePour($chefBureau);

        $this->actingAs($chefBureau)->post(route('courriers.confirmer-reception', $courrier));
        $this->actingAs($chefBureau)
            ->post(route('courriers.reaffecter', $courrier), [
                'unite_destination_id' => $chefBureau->unite_id,
                'motif' => 'Tentative de réaffectation vers la même unité',
            ])
            ->assertSessionHas('error');

        $this->assertSame($chefBureau->unite_id, $courrier->fresh()->unite_actuelle_id);
    }

    public function test_apres_acceptation_le_courrier_disparait_pour_les_autres_agents_de_l_unite(): void
    {
        $this->seed();
        $agent1 = User::query()->where('email', 'agent.bureau1@dgi.ma')->firstOrFail();
        $agent2 = User::query()->where('email', 'agent.bureau2@dgi.ma')->firstOrFail();
        $this->assertSame($agent1->unite_id, $agent2->unite_id);

        $courrier = $this->courrierDeposePour($agent1);

        $this->actingAs($agent1)
            ->get(route('courriers.reception.index'))
            ->assertOk()
            ->assertSee($courrier->numero);

        $this->actingAs($agent2)
            ->get(route('courriers.reception.index'))
            ->assertOk()
            ->assertSee($courrier->numero);

        $this->actingAs($agent1)
            ->post(route('courriers.confirmer-reception', $courrier))
            ->assertSessionHas('success');

        $courrier->refresh();
        $this->assertSame(StatutCourrier::Receptionne, $courrier->statut);
        $this->assertSame($agent1->id, $courrier->responsable_actuel_id);

        // L'autre agent ne peut plus le réceptionner (déjà pris en charge).
        $this->actingAs($agent2)
            ->post(route('courriers.confirmer-reception', $courrier))
            ->assertSessionHas('error');

        $this->actingAs($agent2)
            ->get(route('courriers.traitement.index'))
            ->assertOk()
            ->assertDontSee($courrier->numero);

        $this->actingAs($agent1)
            ->get(route('courriers.traitement.index'))
            ->assertOk()
            ->assertSee($courrier->numero);

        $this->actingAs($agent2)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee($courrier->numero);

        $this->actingAs($agent1)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee($courrier->numero);
    }

    public function test_suivi_du_dossier_contient_toutes_les_actions(): void
    {
        $this->seed();
        $agent = User::query()->where('email', 'agent.bureau1@dgi.ma')->firstOrFail();
        $courrier = $this->courrierDeposePour($agent);

        $this->actingAs($agent)->post(route('courriers.confirmer-reception', $courrier));
        $this->actingAs($agent)
            ->post(route('courriers.traiter', $courrier), [
                'resultat' => 'favorable',
                'date_traitement' => now()->toDateString(),
                'document_genere' => '1',
            ])
            ->assertRedirect(route('courriers.traitement.index'));

        $courrier->refresh();
        $this->assertSame(StatutCourrier::TraiteFavorable, $courrier->statut);

        $this->assertDatabaseHas('courrier_historiques', [
            'courrier_id' => $courrier->id,
            'action' => ActionHistorique::Reception->value,
        ]);
        $this->assertDatabaseHas('courrier_historiques', [
            'courrier_id' => $courrier->id,
            'action' => ActionHistorique::TraitementFavorable->value,
        ]);

        $this->actingAs($agent)
            ->get(route('courriers.show', $courrier))
            ->assertOk()
            ->assertSee('Traçabilité du dossier')
            ->assertSee('Réception')
            ->assertSee('Traitement favorable');
    }

    public function test_accueil_ne_peut_pas_acceder_a_la_reception(): void
    {
        $this->seed();
        $accueil = User::query()->where('email', 'accueil@dgi.ma')->firstOrFail();

        $this->assertFalse($accueil->hasPermission('menu.reception'));
        $this->assertFalse(\Illuminate\Support\Facades\Gate::forUser($accueil)->allows('menu.reception'));

        $this->actingAs($accueil)
            ->get(route('courriers.reception.index'))
            ->assertForbidden();
    }
}