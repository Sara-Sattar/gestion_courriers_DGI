<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\Unite;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserPermissionsTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $this->seed();

        return User::query()->where('email', 'admin@dgi.ma')->firstOrFail();
    }

    public function test_la_creation_utilisateur_enregistre_les_permissions(): void
    {
        $admin = $this->admin();
        $roleBureau = Role::query()->where('name', 'Bureau')->firstOrFail();
        $unite = Unite::query()->where('code', 'BO001')->firstOrFail();
        $permissionIds = Permission::query()
            ->whereIn('code', ['menu.dashboard', 'menu.reception', 'courriers.receptionner'])
            ->pluck('id')
            ->all();

        $this->actingAs($admin)
            ->post(route('users.store'), [
                'name' => 'Agent Test Permissions',
                'email' => 'agent.perms@dgi.ma',
                'password' => 'Dgi#Marrakech2026',
                'password_confirmation' => 'Dgi#Marrakech2026',
                'role_id' => $roleBureau->id,
                'unite_id' => $unite->id,
                'permissions' => $permissionIds,
            ])
            ->assertRedirect(route('users.index'));

        $user = User::query()->where('email', 'agent.perms@dgi.ma')->firstOrFail();
        $user->load('permissions');

        // Profil Bureau : les 3 droits choisis + les droits délégation imposés aux chefs.
        $this->assertTrue($user->hasPermission('menu.dashboard'));
        $this->assertTrue($user->hasPermission('menu.reception'));
        $this->assertTrue($user->hasPermission('courriers.receptionner'));
        $this->assertTrue($user->hasPermission('delegations.voir'));
        $this->assertFalse($user->hasPermission('menu.traitement'));
        $this->assertFalse($user->hasPermission('users.gerer'));
    }

    public function test_la_modification_utilisateur_met_a_jour_les_permissions(): void
    {
        $admin = $this->admin();
        $agent = User::query()->where('email', 'bureau@dgi.ma')->firstOrFail();
        $receptionId = Permission::query()->where('code', 'menu.reception')->value('id');
        $dashboardId = Permission::query()->where('code', 'menu.dashboard')->value('id');

        $this->actingAs($admin)
            ->put(route('users.update', $agent), [
                'name' => $agent->name,
                'email' => $agent->email,
                'role_id' => $agent->role_id,
                'unite_id' => $agent->unite_id,
                'permissions' => [$dashboardId, $receptionId],
            ])
            ->assertRedirect(route('users.index'));

        $agent->refresh()->load('permissions');
        $this->assertTrue($agent->hasPermission('menu.reception'));
        $this->assertFalse($agent->hasPermission('menu.consultation'));
    }

    public function test_sans_permissions_personnelles_on_herite_du_profil(): void
    {
        $this->seed();
        $agent = User::query()->where('email', 'brigade@dgi.ma')->firstOrFail();

        $this->assertTrue($agent->permissions()->doesntExist());
        $this->assertTrue($agent->hasPermission('menu.reception'));
        $this->assertTrue($agent->hasPermission('courriers.reaffecter'));
    }

    public function test_le_formulaire_creation_affiche_la_section_droits(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->get(route('users.create'))
            ->assertOk()
            ->assertSee('Informations')
            ->assertSee('Suivant')
            ->assertSee('Créer le compte')
            ->assertSee('menu.reception');
    }
}
