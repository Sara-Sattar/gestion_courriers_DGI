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
            'mode_contribuable' => ['required', Rule::in(['recherche', 'manuel'])],

            'contribuable_id' => [
                'nullable',
                'required_if:mode_contribuable,recherche',
                'integer',
                'exists:contribuables,id',
            ],

            'nom_raison_sociale' => [
                'nullable',
                'required_if:mode_contribuable,manuel',
                'string',
                'max:255',
            ],
            'type_contribuable' => [
                'nullable',
                'required_if:mode_contribuable,manuel',
                Rule::enum(\App\Enums\TypeContribuable::class),
            ],
            'ifu' => ['nullable', 'string', 'max:20'],
            'cin' => ['nullable', 'string', 'max:20'],
            'ice' => ['nullable', 'string', 'max:20'],
            'numero_tp' => ['nullable', 'string', 'max:30'],
            'numero_th' => ['nullable', 'string', 'max:30'],
            'adresse' => ['nullable', 'string', 'max:255'],
            'ville' => ['nullable', 'string', 'max:255'],
            'telephone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],

            'categorie_courrier_id' => [
                'required',
                'integer',
                Rule::exists('categories_courrier', 'id')->where('actif', true),
            ],
            'type_courrier_id' => [
                'required',
                'integer',
                Rule::exists('types_courrier', 'id')->where(
                    fn ($query) => $query->where('categorie_courrier_id', $categorieId)
                        ->where('actif', true)
                ),
            ],
            'objet' => ['nullable', 'string', 'max:500'],
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
            'pieces_jointes.*' => ['file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:10240'],
        ];
    }

    /**
     * En saisie manuelle, au moins un identifiant fiscal doit être renseigné
     * pour pouvoir retrouver le contribuable par la suite.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->input('mode_contribuable') !== 'manuel') {
                return;
            }

            $identifiants = ['ifu', 'cin', 'ice', 'numero_tp', 'numero_th'];

            $renseigne = collect($identifiants)
                ->contains(fn (string $champ) => filled($this->input($champ)));

            if (! $renseigne) {
                $validator->errors()->add(
                    'ifu',
                    'Renseignez au moins un identifiant : IFU, CIN, ICE, N° TP ou N° TH.'
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'mode_contribuable.required' => 'Choisissez un mode de saisie du contribuable.',
            'contribuable_id.required_if' => 'Veuillez sélectionner un contribuable.',
            'nom_raison_sociale.required_if' => 'Le nom ou la raison sociale est obligatoire.',
            'categorie_courrier_id.required' => 'La catégorie est obligatoire.',
            'categorie_courrier_id.exists' => 'La catégorie sélectionnée est inconnue ou inactive.',
            'type_courrier_id.exists' => 'Le type de courrier sélectionné ne correspond pas à la catégorie choisie.',
            'type_courrier_id.required' => 'Le type de courrier est obligatoire.',
            'unite_destinataire_id.exists' => 'L\'unité destinataire sélectionnée ne correspond pas au type choisi.',
            'unite_destinataire_id.required' => 'L\'unité destinataire est obligatoire.',
            'impots.required' => 'Sélectionnez au moins un impôt concerné.',
            'impots.*.enum' => 'Un impôt sélectionné n\'est pas valide.',
            'impots.*.distinct' => 'Un même impôt ne peut être sélectionné qu\'une fois.',
            'annees.required' => 'Sélectionnez au moins une année concernée.',
            'annees.*.integer' => 'Les années concernées doivent être des années valides.',
            'annees.*.min' => 'Les années concernées doivent être comprises entre 1990 et 2100.',
            'annees.*.max' => 'Les années concernées doivent être comprises entre 1990 et 2100.',
            'annees.*.distinct' => 'Une même année ne peut être sélectionnée qu\'une fois.',
            'pieces_jointes.*.mimes' => 'Seuls les fichiers PDF, DOC, DOCX, JPG, JPEG et PNG sont autorisés.',
            'pieces_jointes.*.max' => 'Chaque fichier ne doit pas dépasser 10 Mo.',
        ];
    }
}
