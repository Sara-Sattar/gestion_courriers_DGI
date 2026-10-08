<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolePermissionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_l_api_droits_du_profil_est_accessible_a_l_admin(): void
    {
        $this->seed();
        $admin = User::query()->where('email', 'admin@dgi.ma')->firstOrFail();
        $role = Role::query()->where('name', 'Bureau')->firstOrFail();

        $this->actingAs($admin)
            ->getJson(route('roles.permissions', $role))
            ->assertOk()
            ->assertJsonStructure(['role', 'permission_ids']);
    }

    public function test_un_agent_ne_peut_pas_lire_les_droits_du_profil(): void
    {
        $this->seed();
        $agent = User::query()->where('email', 'brigade@dgi.ma')->firstOrFail();
        $role = Role::query()->where('name', 'Bureau')->firstOrFail();

        $this->actingAs($agent)
            ->getJson(route('roles.permissions', $role))
            ->assertForbidden();
    }

    public function test_la_page_profils_droits_n_existe_plus(): void
    {
        $this->seed();
        $admin = User::query()->where('email', 'admin@dgi.ma')->firstOrFail();

        $this->actingAs($admin)
            ->get('/roles')
            ->assertNotFound();
    }
}
