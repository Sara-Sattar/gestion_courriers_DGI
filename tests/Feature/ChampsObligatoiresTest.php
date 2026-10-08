<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChampsObligatoiresTest extends TestCase
{
    use RefreshDatabase;

    private const MARQUEUR = '<span class="text-danger" title="Champ obligatoire">*';

    private function utilisateur(string $email): User
    {
        return User::query()->where('email', $email)->firstOrFail();
    }

    /**
     * @return array<string, array{0: string, 1: string, 2: array<int, string>}>
     */
    public static function formulaires(): array
    {
        return [
            'enregistrement d\'un courrier' => [
                'accueil@dgi.ma',
                'courriers.create',
                ['Catégorie', 'Type de courrier', 'Unité destinataire', 'Impôts concernés', 'Années concernées'],
            ],
            'création d\'un utilisateur' => [
                'admin@dgi.ma',
                'users.create',
                ['Nom', 'Adresse e-mail', 'Mot de passe', 'Profil principal', 'Unité de rattachement'],
            ],
            'création d\'une catégorie' => [
                'admin@dgi.ma',
                'categories.create',
                ['Nom de la catégorie'],
            ],
            'création d\'un type de courrier' => [
                'admin@dgi.ma',
                'types-courrier.create',
                ['Catégorie', 'Nom du type'],
            ],
            'création d\'une unité' => [
                'admin@dgi.ma',
                'unites.create',
                ['Type d\'unité', 'Nom de l\'unité'],
            ],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('formulaires')]
    public function test_les_champs_obligatoires_portent_l_asterisque(string $email, string $route, array $libelles): void
    {
        $this->seed();

        $reponse = $this->actingAs($this->utilisateur($email))
            ->get(route($route))
            ->assertOk();

        foreach ($libelles as $libelle) {
            $reponse->assertSee($libelle.' '.self::MARQUEUR, false);
        }
    }

    public function test_les_champs_facultatifs_ne_portent_pas_l_asterisque_au_chargement(): void
    {
        $this->seed();

        $this->actingAs($this->utilisateur('accueil@dgi.ma'))
            ->get(route('courriers.create'))
            ->assertOk()
            ->assertSee('>Objet</label>', false)
            ->assertSee('>Remarque</label>', false)
            ->assertSee('>Documents justificatifs liés au courrier (facultatif)</label>', false)
            ->assertSee('marqueur-obligatoire-manuel d-none', false)
            ->assertSee('id="aide-identifiants-manuel"', false);
    }

    public function test_les_champs_contribuable_manuel_affichent_l_asterisque(): void
    {
        $this->seed();

        $this->actingAs($this->utilisateur('accueil@dgi.ma'))
            ->from(route('courriers.create'))
            ->post(route('courriers.store'), [
                'mode_contribuable' => 'manuel',
            'type_contribuable' => 'personne_morale',
            'nom_raison_sociale' => 'Société Test',
            ])
            ->assertRedirect(route('courriers.create'))
            ->assertSessionHasErrors();

        $this->actingAs($this->utilisateur('accueil@dgi.ma'))
            ->get(route('courriers.create'))
            ->assertOk()
            ->assertSee('id="aide-identifiants-manuel"', false)
            ->assertSee('Renseignez au moins un identifiant fiscal', false)
            ->assertSee('Nom / Raison sociale', false)
            ->assertSee('for="ifu"', false)
            ->assertSee('for="numero_th"', false)
            ->assertDontSee('marqueur-obligatoire-manuel d-none', false);
    }
}
