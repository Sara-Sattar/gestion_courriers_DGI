<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TypeCourrierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-types-courrier') ?? false;
    }

    public function rules(): array
    {
        $type = $this->route('types_courrier');

        return [
            'categorie_courrier_id' => ['required', 'integer', 'exists:categories_courrier,id'],
            'nom' => [
                'required',
                'string',
                'max:255',
                Rule::unique('types_courrier', 'nom')
                    ->where('categorie_courrier_id', $this->integer('categorie_courrier_id'))
                    ->ignore($type),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'actif' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'categorie_courrier_id.required' => 'La catégorie est obligatoire.',
            'nom.required' => 'Le nom du type de courrier est obligatoire.',
            'nom.unique' => 'Ce type existe déjà pour cette catégorie.',
        ];
    }
}
