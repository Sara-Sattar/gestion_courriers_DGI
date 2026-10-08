<?php

namespace App\Http\Requests;

use App\Models\Unite;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UniteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-unites') ?? false;
    }

    public function rules(): array
    {
        $unite = $this->route('unite');
        $typeUnite = (string) $this->input('type_unite');

        $parentsAutorises = Unite::parentsPossibles($typeUnite, $unite)
            ->pluck('id')
            ->all();

        return [
            'nom' => ['required', 'string', 'max:255'],
            // À la création le code est généré côté serveur ; en modification il
            // est conservé tel quel car il figure dans les numéros de courrier.
            'code' => $unite
                ? ['required', 'string', 'max:50', Rule::unique('unites', 'code')->ignore($unite)]
                : ['nullable'],
            'type_unite' => ['required', 'string', Rule::in(Unite::typesDisponibles())],
            'parent_id' => ['nullable', 'integer', Rule::in($parentsAutorises)],
            'description' => ['nullable', 'string', 'max:2000'],
            'actif' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'nom.required' => 'Le nom de l\'unité est obligatoire.',
            'code.unique' => 'Ce code est déjà utilisé par une autre unité.',
            'type_unite.required' => 'Le type d\'unité est obligatoire.',
            'type_unite.in' => 'Choisissez un type d\'unité dans la liste proposée.',
            'parent_id.in' => 'L\'unité parente doit se situer au même niveau ou à un niveau supérieur dans l\'organisation.',
        ];
    }
}
