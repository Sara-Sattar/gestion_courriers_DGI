<?php

namespace App\Http\Requests;

use App\Enums\ResultatTraitement;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class TraiterCourrierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'resultat' => ['required', Rule::enum(ResultatTraitement::class)],
            'date_traitement' => ['required', 'date', 'before_or_equal:today'],
            'document_genere' => ['nullable', 'boolean'],
            'motif_rejet' => ['nullable', 'string', 'min:5', 'max:2000'],
            'motif_suspension' => ['nullable', 'string', 'min:5', 'max:2000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $resultat = $this->input('resultat');

            if ($resultat === ResultatTraitement::Favorable->value) {
                if ($this->input('document_genere') === null || $this->input('document_genere') === '') {
                    $validator->errors()->add('document_genere', 'Indiquez si un document a été généré.');
                }
            }

            if ($resultat === ResultatTraitement::Rejet->value && ! $this->filled('motif_rejet')) {
                $validator->errors()->add('motif_rejet', 'Le motif du rejet est obligatoire.');
            }

            if ($resultat === ResultatTraitement::Suspendu->value && ! $this->filled('motif_suspension')) {
                $validator->errors()->add('motif_suspension', 'Le motif de suspension est obligatoire.');
            }
        });
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('document_genere')) {
            $this->merge([
                'document_genere' => filter_var($this->input('document_genere'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
            ]);
        }
    }

    public function messages(): array
    {
        return [
            'resultat.required' => 'Choisissez le résultat du traitement.',
            'date_traitement.required' => 'La date de traitement est obligatoire.',
            'date_traitement.before_or_equal' => 'La date de traitement ne peut pas être dans le futur.',
        ];
    }

    public function attributes(): array
    {
        return [
            'resultat' => 'résultat',
            'date_traitement' => 'date de traitement',
            'document_genere' => 'document généré',
            'motif_rejet' => 'motif du rejet',
            'motif_suspension' => 'motif de suspension',
        ];
    }
}
