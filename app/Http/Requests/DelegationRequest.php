<?php

namespace App\Http\Requests;

use App\Enums\MotifDelegation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DelegationRequest extends FormRequest
{
    public function authorize(): bool
    {
        if ($this->isMethod('POST')) {
            return $this->user()?->can('delegations.creer') ?? false;
        }

        return $this->user()?->can('delegations.modifier') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'unite_id' => ['required', 'exists:unites,id'],
            'agent_absent_id' => ['required', 'exists:users,id', 'different:agent_remplacant_id'],
            'agent_remplacant_id' => ['required', 'exists:users,id'],
            'date_debut' => ['required', 'date'],
            'date_fin' => ['required', 'date', 'after_or_equal:date_debut'],
            'motif' => ['required', Rule::enum(MotifDelegation::class)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'unite_id.required' => 'L\'unité structurelle est obligatoire.',
            'agent_absent_id.required' => 'L\'agent absent est obligatoire.',
            'agent_absent_id.different' => 'Un agent ne peut pas se remplacer lui-même.',
            'agent_remplacant_id.required' => 'L\'agent remplaçant est obligatoire.',
            'date_debut.required' => 'La date de début est obligatoire.',
            'date_fin.required' => 'La date de fin est obligatoire.',
            'date_fin.after_or_equal' => 'La date de fin doit être postérieure ou égale à la date de début.',
            'motif.required' => 'Le motif est obligatoire.',
        ];
    }
}
