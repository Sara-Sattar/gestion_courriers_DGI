<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RefuserReaffectationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'motif' => ['required', 'string', 'min:5', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'motif.required' => 'Le motif du refus est obligatoire.',
            'motif.min' => 'Le motif doit contenir au moins 5 caractères.',
        ];
    }
}
