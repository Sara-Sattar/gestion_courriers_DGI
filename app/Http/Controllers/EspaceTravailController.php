<?php

namespace App\Http\Controllers;

use App\Services\DelegationContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EspaceTravailController extends Controller
{
    public function __construct(
        private readonly DelegationContext $context,
    ) {}

    public function choisir(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'espace' => ['required'],
        ]);

        $espace = $validated['espace'];
        if (is_numeric($espace)) {
            $espace = (int) $espace;
        }

        $this->context->choisirEspace($espace);

        return redirect()
            ->intended(route('dashboard'))
            ->with('success', $this->context->enRemplacement()
                ? 'Espace de travail : '.$this->context->libelleEspaceActif().'.'
                : 'Vous travaillez dans votre espace personnel.');
    }
}
