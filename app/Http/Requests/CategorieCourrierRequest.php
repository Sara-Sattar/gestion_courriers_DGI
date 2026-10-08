<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategorieCourrierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-categories') ?? false;
    }

    public function rules(): array
    {
        $categorie = $this->route('categorie');

        return [
            'nom' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories_courrier', 'nom')->ignore($categorie),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'necessite_reponse' => ['required', 'boolean'],
            'actif' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'nom.required' => 'Le nom de la catégorie est obligatoire.',
            'nom.unique' => 'Cette catégorie existe déjà.',
            'necessite_reponse.required' => 'Indiquez si les courriers de cette catégorie nécessitent une réponse.',
        ];
    }
}
