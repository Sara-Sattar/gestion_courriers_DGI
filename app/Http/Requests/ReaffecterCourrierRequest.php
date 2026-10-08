<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReaffecterCourrierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'unite_destination_id' => ['required', 'integer', 'exists:unites,id'],
            'motif' => ['required', 'string', 'min:5', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'unite_destination_id.required' => 'Sélectionnez l\'unité de destination.',
            'unite_destination_id.exists' => 'L\'unité de destination est invalide.',
            'motif.required' => 'Le motif de la réaffectation est obligatoire.',
            'motif.min' => 'Le motif doit contenir au moins 5 caractères.',
        ];
    }

    public function attributes(): array
    {
        return [
            'unite_destination_id' => 'unité de destination',
            'motif' => 'motif',
        ];
    }
}
