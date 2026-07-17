<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReaffecterCourrierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type_destinataire' => 'required|in:utilisateur,unite',
            'destinataire_id' => 'required',
            'motif' => 'required|string|min:5',
        ];
    }
}
