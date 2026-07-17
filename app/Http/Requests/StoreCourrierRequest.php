<?php

namespace App\Http\Requests;

use App\Enums\TypeImpot;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCourrierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $categorieId = $this->integer('categorie_courrier_id') ?: null;
        $typeUnite = $this->string('type_unite')->trim()->value();

        return [
            'contribuable_id' => ['required', 'integer', 'exists:contribuables,id'],

            'categorie_courrier_id' => ['required', 'integer', 'exists:categories_courrier,id'],
            'type_courrier_id' => [
                'required',
                'integer',
                Rule::exists('types_courrier', 'id')->where(
                    fn ($query) => $query->where('categorie_courrier_id', $categorieId)
                        ->where('actif', true)
                ),
            ],
            'objet' => ['required', 'string', 'max:500'],
            'type_unite' => ['required', 'string', 'max:50'],
            'unite_destinataire_id' => [
                'required',
                'integer',
                Rule::exists('unites', 'id')->where(
                    fn ($query) => $query->where('type_unite', $typeUnite)
                        ->where('actif', true)
                ),
            ],
            'impots' => ['required', 'array', 'min:1'],
            'impots.*' => ['required', 'distinct', Rule::enum(TypeImpot::class)],
            'annees' => ['required', 'array', 'min:1'],
            'annees.*' => ['required', 'integer', 'distinct', 'min:1990', 'max:2100'],
            'remarque' => ['nullable', 'string', 'max:2000'],
            'pieces_jointes' => ['nullable', 'array'],
            'pieces_jointes.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'contribuable_id.required' => 'Veuillez sélectionner un contribuable.',
            'categorie_courrier_id.required' => 'La catégorie est obligatoire.',
            'type_courrier_id.exists' => 'Le type de courrier sélectionné ne correspond pas à la catégorie choisie.',
            'type_courrier_id.required' => 'Le type de courrier est obligatoire.',
            'objet.required' => 'L\'objet est obligatoire.',
            'unite_destinataire_id.exists' => 'L\'unité destinataire sélectionnée ne correspond pas au type choisi.',
            'unite_destinataire_id.required' => 'L\'unité destinataire est obligatoire.',
            'impots.required' => 'Sélectionnez au moins un impôt concerné.',
            'annees.required' => 'Sélectionnez au moins une année concernée.',
            'pieces_jointes.*.mimes' => 'Seuls les fichiers PDF, JPG, JPEG et PNG sont autorisés.',
            'pieces_jointes.*.max' => 'Chaque fichier ne doit pas dépasser 5 Mo.',
        ];
    }
}
