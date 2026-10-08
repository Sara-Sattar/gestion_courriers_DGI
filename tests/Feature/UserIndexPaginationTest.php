<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserIndexPaginationTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_pagination_affiche_suivant_et_precedent_entre_les_pages(): void
    {
        $this->seed();

        $admin = User::query()->where('email', 'admin@dgi.ma')->firstOrFail();

        User::factory()->count(8)->create();

        $page1 = $this->actingAs($admin)->get(route('users.index'));
        $page1->assertOk();
        $page1->assertSee('Suivant', false);
        $page1->assertSee('Précédent', false);
        $this->assertSame(10, $page1->viewData('users')->count());
        $this->assertSame(20, $page1->viewData('users')->total());
        $this->assertTrue($page1->viewData('users')->hasMorePages());
        $this->assertTrue($page1->viewData('users')->onFirstPage());
        $page1->assertSee('Précédent', false);
        $page1->assertSee('Suivant', false);

        $page2 = $this->actingAs($admin)->get(route('users.index', ['page' => 2]));
        $page2->assertOk();
        $page2->assertSee('Suivant', false);
        $page2->assertSee('Précédent', false);
        $this->assertSame(10, $page2->viewData('users')->count());
        $this->assertFalse($page2->viewData('users')->hasMorePages());
        $this->assertFalse($page2->viewData('users')->onFirstPage());

        $nextUrl = $page1->viewData('users')->nextPageUrl();
        $this->assertNotNull($nextUrl);

        $this->actingAs($admin)->get($nextUrl)->assertOk();

        $previousUrl = $page2->viewData('users')->previousPageUrl();
        $this->assertNotNull($previousUrl);

        $this->actingAs($admin)->get($previousUrl)->assertOk();
    }

    public function test_un_utilisateur_non_admin_ne_peut_pas_acceder_a_la_liste(): void
    {
        $this->seed();

        $user = User::query()->where('email', 'accueil@dgi.ma')->firstOrFail();

        $this->actingAs($user)->get(route('users.index'))->assertForbidden();
    }
}
