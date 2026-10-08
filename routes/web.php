<?php

use App\Http\Controllers\CategorieCourrierController;
use App\Http\Controllers\ConsultationController;
use App\Http\Controllers\ContribuableController;
use App\Http\Controllers\CourrierController;
use App\Http\Controllers\CourrierReceptionController;
use App\Http\Controllers\CourrierTraitementController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DelegationController;
use App\Http\Controllers\EspaceTravailController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TypeCourrierController;
use App\Http\Controllers\UniteController;
use App\Http\Controllers\UserController;
use App\Models\Courrier;
use Illuminate\Support\Facades\Route;

// Redirect homepage to dashboard
Route::redirect('/', '/dashboard');

// Connexion signée locale pour les captures du rapport (jamais exposée hors local).
if (app()->environment('local')) {
    Route::get('/__rapport/entrer', function (\Illuminate\Http\Request $request) {
        abort_unless($request->hasValidSignature(), 403);

        $email = (string) $request->query('email');
        $autorises = [
            'accueil@dgi.ma',
            'agent.bureau1@dgi.ma',
            'bureau@dgi.ma',
            'admin@dgi.ma',
        ];
        abort_unless(in_array($email, $autorises, true), 403);

        $user = \App\Models\User::query()->where('email', $email)->firstOrFail();
        auth()->login($user);

        $next = (string) $request->query('next', '/dashboard');
        if (! str_starts_with($next, '/') || str_starts_with($next, '//')) {
            $next = '/dashboard';
        }

        return redirect()->to($next);
    })->name('rapport.entrer');
}

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::post('/espace-travail', [EspaceTravailController::class, 'choisir'])->name('espace-travail.choisir');

    Route::get('/delegations/agents', [DelegationController::class, 'agents'])->name('delegations.agents');
    Route::resource('delegations', DelegationController::class)->except(['show']);

    // Administration — profils, utilisateurs, référentiels
    Route::get('/roles/{role}/permissions', [RoleController::class, 'permissionsJson'])->name('roles.permissions');

    Route::resource('users', UserController::class);
    Route::resource('categories', CategorieCourrierController::class)
        ->parameters(['categories' => 'categorie'])
        ->except(['show']);
    Route::resource('types-courrier', TypeCourrierController::class)->except(['show']);
    Route::resource('unites', UniteController::class)->except(['show']);

    Route::get('/courriers/create', [CourrierController::class, 'create'])->name('courriers.create');
    Route::post('/courriers', [CourrierController::class, 'store'])->name('courriers.store');
    // Déclaré avant /courriers/{courrier} pour ne pas être capturé par le paramètre.
    Route::get('/courriers/modele-import', [CourrierController::class, 'telechargerModeleImport'])->name('courriers.import.modele');
    Route::post('/courriers/import', [CourrierController::class, 'import'])->name('courriers.import');
    Route::get('/courriers/{courrier}', [CourrierController::class, 'show'])->name('courriers.show');
    Route::get('/courriers/{courrier}/recu', [CourrierController::class, 'recu'])->name('courriers.recu');
    Route::get('/courriers/{courrier}/recu/telecharger', [CourrierController::class, 'telechargerRecu'])->name('courriers.recu.telecharger');
    Route::get('/courriers/{courrier}/pieces-jointes/{pieceJointe}', [CourrierController::class, 'afficherPieceJointe'])
        ->name('courriers.pieces-jointes.show');
    Route::get('/courriers/{courrier}/pieces-jointes/{pieceJointe}/telecharger', [CourrierController::class, 'telechargerPieceJointe'])
        ->name('courriers.pieces-jointes.telecharger');

    // Reception routes
    Route::get('/reception', [CourrierReceptionController::class, 'index'])->name('courriers.reception.index');
    Route::post('/reception/receptionner', [CourrierReceptionController::class, 'receptionner'])->name('courriers.receptionner');
    Route::post('/courriers/{courrier}/confirmer-reception', [CourrierReceptionController::class, 'confirmerReception'])->name('courriers.confirmer-reception');
    Route::post('/courriers/{courrier}/annuler-reception', [CourrierReceptionController::class, 'annulerReception'])->name('courriers.annuler-reception');
    Route::post('/courriers/{courrier}/refuser-reception', [CourrierReceptionController::class, 'refuserReception'])->name('courriers.refuser-reception');
    Route::post('/courriers/{courrier}/refuser-reaffectation', [CourrierReceptionController::class, 'refuserReaffectation'])->name('courriers.refuser-reaffectation');
    Route::post('/courriers/{courrier}/reaffecter', [CourrierReceptionController::class, 'reaffecter'])->name('courriers.reaffecter');
    Route::post('/courriers/{courrier}/annuler-depot', [CourrierController::class, 'annulerDepot'])->name('courriers.annuler-depot');

    // Traitement
    Route::get('/traitement', [CourrierTraitementController::class, 'index'])->name('courriers.traitement.index');
    Route::get('/courriers/{courrier}/traitement', [CourrierTraitementController::class, 'show'])->name('courriers.traitement.show');
    Route::post('/courriers/{courrier}/traiter', [CourrierTraitementController::class, 'store'])->name('courriers.traiter');
    // Après un refresh navigateur sur l'URL POST /traiter → renvoyer vers le formulaire.
    Route::get('/courriers/{courrier}/traiter', function (Courrier $courrier) {
        return redirect()->route('courriers.traitement.show', $courrier);
    });
    Route::post('/courriers/{courrier}/reprendre-traitement', [CourrierTraitementController::class, 'reprendre'])->name('courriers.reprendre');

    // Délivrance (Accueil)
    Route::get('/delivrance', [CourrierTraitementController::class, 'indexDelivrance'])->name('courriers.delivrance.index');
    Route::get('/courriers/{courrier}/delivrance', [CourrierTraitementController::class, 'showDelivrance'])->name('courriers.delivrance.show');
    Route::post('/courriers/{courrier}/delivrer', [CourrierTraitementController::class, 'delivrer'])->name('courriers.delivrer');
    Route::get('/courriers/{courrier}/delivrer', function (Courrier $courrier) {
        return redirect()->route('courriers.delivrance.show', $courrier);
    });
    Route::post('/courriers/{courrier}/cloturer-sans-document', [CourrierTraitementController::class, 'cloturerSansDocument'])->name('courriers.cloturer-sans-document');

    Route::get('/consultation', [ConsultationController::class, 'index'])->name('consultation.index');
    Route::get('/consultation/export', [ConsultationController::class, 'exporter'])->name('consultation.export');

    Route::get('/contribuables/recherche', [ContribuableController::class, 'recherche'])->name('contribuables.recherche');
    Route::get('/contribuables/{contribuable}', [ContribuableController::class, 'show'])->name('contribuables.show');

    Route::get('/unites/types', [UniteController::class, 'types'])->name('unites.types');
    Route::get('/unites/prochain-code/{type}', [UniteController::class, 'prochainCode'])->name('unites.prochainCode');
    Route::get('/unites/parents/{type}', [UniteController::class, 'parentsPossibles'])->name('unites.parentsPossibles');
    Route::get('/unites/type/{type}', [UniteController::class, 'parType'])->name('unites.parType');
    Route::get('/unites/role/{roleId}', [UniteController::class, 'parRole'])->name('unites.parRole');
    Route::get('/categories/{categorie}/types-courrier', [UniteController::class, 'typesCourrier'])->name('categories.types-courrier');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
