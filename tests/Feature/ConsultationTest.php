<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConsultationTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_agent_voit_la_page_statistiques_cdc(): void
    {
        $this->seed();
        $agent = User::query()->where('email', 'accueil@dgi.ma')->firstOrFail();

        $this->actingAs($agent)
            ->get(route('consultation.index'))
            ->assertOk()
            ->assertSee('Statistiques')
            ->assertSee('Synthèse')
            ->assertSee('Type')
            ->assertSee('BO')
            ->assertSee('US')
            ->assertSee('Mois')
            ->assertSee('En cours')
            ->assertSee('Catégorie')
            ->assertSee('Délai réception')
            ->assertSee('Évolution vs période précédente')
            ->assertSee('Croisement type');
    }

    public function test_filtre_periode_semaine_fonctionne(): void
    {
        $this->seed();
        $agent = User::query()->where('email', 'brigade@dgi.ma')->firstOrFail();

        $this->actingAs($agent)
            ->get(route('consultation.index', ['periode' => 'semaine']))
            ->assertOk()
            ->assertSee('Cette semaine');
    }

    public function test_tous_les_axes_sont_accessibles(): void
    {
        $this->seed();
        $agent = User::query()->where('email', 'accueil@dgi.ma')->firstOrFail();

        foreach (['synthese', 'type', 'bo', 'us', 'mois', 'categorie', 'statut', 'priorite', 'reponse', 'livraison'] as $axe) {
            $this->actingAs($agent)
                ->get(route('consultation.index', ['axe' => $axe, 'periode' => 'tout']))
                ->assertOk();
        }
    }

    public function test_export_statistiques_formats_multiples(): void
    {
        $this->seed();
        $agent = User::query()->where('email', 'accueil@dgi.ma')->firstOrFail();

        $this->actingAs($agent)
            ->get(route('consultation.export', ['format' => 'json', 'periode' => 'tout']))
            ->assertOk()
            ->assertJsonStructure([
                'par_type', 'par_bo', 'par_us', 'par_mois',
                'par_categorie', 'par_statut', 'par_priorite', 'par_reponse', 'par_livraison',
                'indicateurs', 'comparaison', 'croisement_type_statut', 'total',
            ]);

        $this->actingAs($agent)
            ->get(route('consultation.export', ['format' => 'csv', 'periode' => 'tout']))
            ->assertOk();

        $this->actingAs($agent)
            ->get(route('consultation.export', ['format' => 'xlsx', 'periode' => 'tout']))
            ->assertOk();

        $this->actingAs($agent)
            ->get(route('consultation.export', ['format' => 'pdf', 'periode' => 'tout']))
            ->assertOk();
    }

    public function test_sans_permission_consultation_est_refusee(): void
    {
        $this->seed();
        $admin = User::query()->where('email', 'admin@dgi.ma')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('consultation.index'))
            ->assertForbidden();
    }
}
