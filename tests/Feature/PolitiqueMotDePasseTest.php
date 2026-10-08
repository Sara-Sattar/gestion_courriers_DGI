<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Unite;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PolitiqueMotDePasseTest extends TestCase
{
    use RefreshDatabase;

    private const MOT_DE_PASSE_VALIDE = 'Dgi#Marrakech2026';

    private function administrateur(): User
    {
        return User::query()->where('email', 'admin@dgi.ma')->firstOrFail();
    }

    /**
     * @return array<string, mixed>
     */
    private function donneesUtilisateur(string $motDePasse, ?string $confirmation = null): array
    {
        return [
            'name' => 'Agent de test',
            'email' => 'agent.test@dgi.ma',
            'password' => $motDePasse,
            'password_confirmation' => $confirmation ?? $motDePasse,
            'role_id' => Role::query()->where('name', 'Bureau')->firstOrFail()->id,
            'unite_id' => Unite::query()->where('code', 'BO001')->firstOrFail()->id,
        ];
    }

    /**
     * @return array<string, array{string}>
     */
    public static function motsDePasseRefuses(): array
    {
        return [
            'trop court' => ['Ab#1cde'],
            'sans majuscule' => ['dgi#marrakech2026'],
            'sans minuscule' => ['DGI#MARRAKECH2026'],
            'sans chiffre' => ['Dgi#Marrakech'],
            'sans caractère spécial' => ['DgiMarrakech2026'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('motsDePasseRefuses')]
    public function test_un_mot_de_passe_non_conforme_est_refuse_a_la_creation(string $motDePasse): void
    {
        $this->seed();

        $this->actingAs($this->administrateur())
            ->post(route('users.store'), $this->donneesUtilisateur($motDePasse))
            ->assertSessionHasErrors('password');

        $this->assertDatabaseMissing('users', ['email' => 'agent.test@dgi.ma']);
    }

    public function test_un_mot_de_passe_conforme_est_accepte_a_la_creation(): void
    {
        $this->seed();

        $this->actingAs($this->administrateur())
            ->post(route('users.store'), $this->donneesUtilisateur(self::MOT_DE_PASSE_VALIDE))
            ->assertSessionHasNoErrors();

        $utilisateur = User::query()->where('email', 'agent.test@dgi.ma')->firstOrFail();

        $this->assertTrue(Hash::check(self::MOT_DE_PASSE_VALIDE, $utilisateur->password));
    }

    public function test_la_confirmation_doit_correspondre(): void
    {
        $this->seed();

        $this->actingAs($this->administrateur())
            ->post(route('users.store'), $this->donneesUtilisateur(self::MOT_DE_PASSE_VALIDE, 'Dgi#Marrakech2027'))
            ->assertSessionHasErrors('password');

        $this->assertDatabaseMissing('users', ['email' => 'agent.test@dgi.ma']);
    }

    public function test_la_politique_s_applique_aussi_a_la_modification(): void
    {
        $this->seed();

        $utilisateur = User::query()->where('email', 'agent.bureau1@dgi.ma')->firstOrFail();
        $ancienHash = $utilisateur->password;

        $this->actingAs($this->administrateur())
            ->put(route('users.update', $utilisateur), [
                'name' => $utilisateur->name,
                'email' => $utilisateur->email,
                'password' => 'motdepasse',
                'password_confirmation' => 'motdepasse',
                'role_id' => $utilisateur->role_id,
                'unite_id' => $utilisateur->unite_id,
            ])
            ->assertSessionHasErrors('password');

        $this->assertSame($ancienHash, $utilisateur->refresh()->password);
    }

    public function test_un_mot_de_passe_vide_en_modification_conserve_l_ancien(): void
    {
        $this->seed();

        $utilisateur = User::query()->where('email', 'agent.bureau1@dgi.ma')->firstOrFail();
        $ancienHash = $utilisateur->password;

        $this->actingAs($this->administrateur())
            ->put(route('users.update', $utilisateur), [
                'name' => 'Nom modifié',
                'email' => $utilisateur->email,
                'password' => '',
                'password_confirmation' => '',
                'role_id' => $utilisateur->role_id,
                'unite_id' => $utilisateur->unite_id,
            ])
            ->assertSessionHasNoErrors();

        $utilisateur->refresh();

        $this->assertSame('Nom modifié', $utilisateur->name);
        $this->assertSame($ancienHash, $utilisateur->password);
    }

    public function test_la_politique_s_applique_au_changement_depuis_le_profil(): void
    {
        $this->seed();

        $utilisateur = $this->administrateur();

        $this->actingAs($utilisateur)
            ->from(route('profile.edit'))
            ->put(route('password.update'), [
                'current_password' => 'password',
                'password' => 'motdepasse',
                'password_confirmation' => 'motdepasse',
            ])
            ->assertSessionHasErrorsIn('updatePassword', 'password');
    }

    public function test_les_messages_d_erreur_sont_en_francais(): void
    {
        $this->seed();

        $reponse = $this->actingAs($this->administrateur())
            ->post(route('users.store'), $this->donneesUtilisateur('DgiMarrakech2026'));

        $messages = session('errors')->get('password');

        $this->assertStringContainsString('caractère spécial', implode(' ', $messages));
    }
}
