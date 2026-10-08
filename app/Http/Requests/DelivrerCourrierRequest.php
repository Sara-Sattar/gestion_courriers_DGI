<?php

namespace App\Http\Requests;

use App\Enums\ModeLivraison;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class DelivrerCourrierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'date_delivrance' => ['required', 'date', 'before_or_equal:today'],
            'mode_livraison' => ['required', Rule::enum(ModeLivraison::class)],
            'mandataire_piece' => ['nullable', 'string', 'max:100'],
            'mandataire_nom' => ['nullable', 'string', 'max:255'],
            'mandataire_prenom' => ['nullable', 'string', 'max:255'],
            'mandataire_cin' => ['nullable', 'string', 'max:50'],
            'date_procuration' => ['nullable', 'date', 'before_or_equal:today'],
            'postal_cin' => ['nullable', 'string', 'max:50'],
            'postal_nom' => ['nullable', 'string', 'max:255'],
            'reference_envoi' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $mode = $this->input('mode_livraison');

            if ($mode === ModeLivraison::Mandataire->value) {
                foreach (['mandataire_nom' => 'Le nom', 'mandataire_prenom' => 'Le prénom', 'mandataire_cin' => 'Le CIN', 'date_procuration' => 'La date de procuration'] as $champ => $libelle) {
                    if (! $this->filled($champ)) {
                        $validator->errors()->add($champ, "{$libelle} du mandataire est obligatoire.");
                    }
                }
            }

            if ($mode === ModeLivraison::EnvoiPostal->value) {
                if (! $this->filled('postal_cin')) {
                    $validator->errors()->add('postal_cin', 'Le CIN est obligatoire pour un envoi postal.');
                }
                if (! $this->filled('postal_nom')) {
                    $validator->errors()->add('postal_nom', 'Le nom postal est obligatoire.');
                }
                if (! $this->filled('reference_envoi')) {
                    $validator->errors()->add('reference_envoi', 'La référence d\'envoi est obligatoire pour un envoi postal.');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'date_delivrance.required' => 'La date de délivrance est obligatoire.',
            'date_delivrance.before_or_equal' => 'La date de délivrance ne peut pas être dans le futur.',
            'date_procuration.before_or_equal' => 'La date de procuration ne peut pas être dans le futur.',
            'mode_livraison.required' => 'Le mode de livraison est obligatoire.',
        ];
    }
}
