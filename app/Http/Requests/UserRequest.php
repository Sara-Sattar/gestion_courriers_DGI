<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Gate::allows('manage-users');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->route('user')?->id;

        return [
            'ppr' => ['nullable', 'string', 'max:30', Rule::unique('users', 'ppr')->ignore($userId)],
            'name' => ['required', 'string', 'max:255'],
            'prenom' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($userId)],
            // En modification, un champ vide laisse le mot de passe inchangé.
            'password' => [
                $this->isMethod('post') ? 'required' : 'nullable',
                'string',
                'confirmed',
                Password::defaults(),
            ],
            'role_id' => ['required', 'exists:roles,id'],
            'role_ids' => ['nullable', 'array'],
            'role_ids.*' => ['integer', 'exists:roles,id'],
            'unite_id' => ['required', 'exists:unites,id'],
            'bureau_ordre_id' => ['nullable', 'exists:bureaux_ordre,id'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ];
    }

    public function attributes(): array
    {
        return [
            'permissions' => 'permissions',
            'permissions.*' => 'permission',
        ];
    }
}
