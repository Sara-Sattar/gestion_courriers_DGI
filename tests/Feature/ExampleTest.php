<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_la_racine_redirige_vers_accueil(): void
    {
        $this->get('/')->assertRedirect('/dashboard');
    }

    public function test_accueil_exige_une_authentification(): void
    {
        $this->get('/dashboard')->assertRedirect(route('login'));
    }
}
