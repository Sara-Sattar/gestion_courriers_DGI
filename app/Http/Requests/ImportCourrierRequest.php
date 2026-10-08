<?php

namespace App\Http\Requests;

use App\Models\Courrier;
use Illuminate\Foundation\Http\FormRequest;

class ImportCourrierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Courrier::class) ?? false;
    }

    /**
     * L'extension cliente fait foi : c'est elle que PhpSpreadsheet utilise
     * pour choisir son lecteur, et la détection MIME d'un CSV n'est pas fiable.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'fichier' => ['required', 'file', 'extensions:xlsx,xls,csv', 'max:10240'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'fichier.required' => 'Sélectionnez le fichier Excel à importer.',
            'fichier.extensions' => 'Le fichier doit être au format Excel (XLSX, XLS) ou CSV.',
            'fichier.max' => 'Le fichier ne doit pas dépasser 10 Mo.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'fichier' => 'fichier Excel',
        ];
    }
}
