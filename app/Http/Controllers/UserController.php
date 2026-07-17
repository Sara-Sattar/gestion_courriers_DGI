<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\Role;
use App\Models\Unite;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Gate;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        Gate::authorize('manage-users');
        
        $users = User::with(['role', 'unite'])->paginate(10);
        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        Gate::authorize('manage-users');
        
        $roles = Role::all();
        $unites = Unite::all();
        return view('users.create', compact('roles', 'unites'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserRequest $request): RedirectResponse
    {
        Gate::authorize('manage-users');
        
        $validated = $request->validated();
        $validated['password'] = bcrypt($validated['password']);
        
        User::create($validated);
        
        return redirect()->route('users.index')->with('success', 'Utilisateur créé avec succès');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user): View
    {
        Gate::authorize('manage-users');
        
        $user->load(['role', 'unite']);
        return view('users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user): View
    {
        Gate::authorize('manage-users');
        
        $roles = Role::all();
        $unites = Unite::all();
        return view('users.edit', compact('user', 'roles', 'unites'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UserRequest $request, User $user): RedirectResponse
    {
        Gate::authorize('manage-users');
        
        $validated = $request->validated();
        
        if (empty($validated['password'])) {
            unset($validated['password']);
        } else {
            $validated['password'] = bcrypt($validated['password']);
        }
        
        $user->update($validated);
        
        return redirect()->route('users.index')->with('success', 'Utilisateur mis à jour avec succès');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user): RedirectResponse
    {
        Gate::authorize('manage-users');
        
        $user->delete();
        
        return redirect()->route('users.index')->with('success', 'Utilisateur supprimé avec succès');
    }
}
