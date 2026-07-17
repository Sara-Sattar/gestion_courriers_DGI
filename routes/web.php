<?php

use App\Http\Controllers\ConsultationController;
use App\Http\Controllers\ContribuableController;
use App\Http\Controllers\CourrierController;
use App\Http\Controllers\CourrierReceptionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UniteController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Redirect homepage to dashboard
Route::redirect('/', '/dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // User management routes - Admin only
    Route::resource('users', UserController::class);

    Route::get('/courriers/create', [CourrierController::class, 'create'])->name('courriers.create');
    Route::post('/courriers', [CourrierController::class, 'store'])->name('courriers.store');
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
    Route::post('/courriers/{courrier}/annuler-reception', [CourrierReceptionController::class, 'annulerReception'])->name('courriers.annuler-reception');
    Route::post('/courriers/{courrier}/reaffecter', [CourrierReceptionController::class, 'reaffecter'])->name('courriers.reaffecter');

    Route::get('/consultation', [ConsultationController::class, 'index'])->name('consultation.index');

    Route::get('/contribuables/recherche', [ContribuableController::class, 'recherche'])->name('contribuables.recherche');
    Route::get('/contribuables/{contribuable}', [ContribuableController::class, 'show'])->name('contribuables.show');

    Route::get('/unites/types', [UniteController::class, 'types'])->name('unites.types');
    Route::get('/unites/type/{type}', [UniteController::class, 'parType'])->name('unites.parType');
    Route::get('/categories/{categorie}/types-courrier', [UniteController::class, 'typesCourrier'])->name('categories.types-courrier');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
