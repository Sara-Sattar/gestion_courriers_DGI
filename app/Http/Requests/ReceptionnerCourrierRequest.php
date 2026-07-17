<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReceptionnerCourrierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'courrier_ids' => 'required|array|min:1',
            'courrier_ids.*' => 'exists:courriers,id',
        ];
    }
}
