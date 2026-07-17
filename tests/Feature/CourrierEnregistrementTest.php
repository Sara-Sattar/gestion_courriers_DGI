<?php

namespace Tests\Feature;

use App\Enums\ActionHistorique;
use App\Enums\StatutCourrier;
use App\Models\CategorieCourrier;
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

    public function test_un_utilisateur_peut_enregistrer_un_courrier_et_generer_un_recu(): void
    {
        $this->seed();

        $user = User::factory()->create();
        $categorie = CategorieCourrier::query()->firstOrFail();
        $type = TypeCourrier::query()
            ->where('categorie_courrier_id', $categorie->id)
            ->firstOrFail();
        $unite = Unite::query()->where('code', 'SERV002')->firstOrFail();

        $response = $this->actingAs($user)->post(route('courriers.store'), [
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
            'objet' => 'Demande d\'attestation',
            'type_unite' => $unite->type_unite,
            'unite_destinataire_id' => $unite->id,
            'impots' => ['IR', 'TVA'],
            'annees' => [2024, 2025],
            'remarque' => 'Dossier complet',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('contribuables', [
            'nom_raison_sociale' => 'Société Atlas Fiscal',
            'ifu' => 'IFU123456',
        ]);

        $this->assertDatabaseHas('courriers', [
            'objet' => 'Demande d\'attestation',
            'unite_destinataire_id' => $unite->id,
            'statut' => StatutCourrier::Depose->value,
            'user_id' => $user->id,
        ]);

        $courrier = \App\Models\Courrier::query()->with(['historiques', 'impots', 'annees'])->firstOrFail();

        $this->assertSame($unite->code, $courrier->code_unite);
        $this->assertCount(1, $courrier->historiques);
        $this->assertSame(ActionHistorique::Depot, $courrier->historiques->first()->action);
        $this->assertCount(2, $courrier->impots);
        $this->assertCount(2, $courrier->annees);

        $showResponse = $this->actingAs($user)->get(route('courriers.show', $courrier));
        $showResponse->assertOk()->assertSee($courrier->numero);

        $pdfResponse = $this->actingAs($user)->get(route('courriers.recu', $courrier));
        $pdfResponse->assertOk();
        $pdfResponse->assertHeader('content-type', 'application/pdf');
    }

    public function test_les_pieces_jointes_peuvent_etre_consultees_et_telechargees(): void
    {
        Storage::fake('local');
        $this->seed();

        $user = User::factory()->create();
        $categorie = CategorieCourrier::query()->firstOrFail();
        $type = TypeCourrier::query()
            ->where('categorie_courrier_id', $categorie->id)
            ->firstOrFail();
        $unite = Unite::query()->where('code', 'SERV002')->firstOrFail();

        $this->actingAs($user)->post(route('courriers.store'), [
            'nom_raison_sociale' => 'Société Pièce Jointe',
            'ifu' => 'IFU999999',
            'adresse' => 'Rue de la DGI',
            'ville' => 'Casablanca',
            'categorie_courrier_id' => $categorie->id,
            'type_courrier_id' => $type->id,
            'objet' => 'Dossier avec justificatifs',
            'type_unite' => $unite->type_unite,
            'unite_destinataire_id' => $unite->id,
            'impots' => ['IS'],
            'annees' => [2025],
            'pieces_jointes' => [
                UploadedFile::fake()->create('document.pdf', 120, 'application/pdf'),
            ],
        ])->assertRedirect();

        $courrier = \App\Models\Courrier::query()->with('piecesJointes')->firstOrFail();
        $piece = $courrier->piecesJointes->firstOrFail();

        Storage::disk('local')->assertExists($piece->chemin);

        $showResponse = $this->actingAs($user)->get(route('courriers.pieces-jointes.show', [$courrier, $piece]));
        $showResponse->assertOk();
        $showResponse->assertHeader('content-type', 'application/pdf');

        $downloadResponse = $this->actingAs($user)->get(route('courriers.pieces-jointes.telecharger', [$courrier, $piece]));
        $downloadResponse->assertOk();
        $downloadResponse->assertHeader('content-disposition');
    }
}
