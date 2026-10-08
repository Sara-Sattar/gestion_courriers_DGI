<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DepartSequence extends Model
{
    protected $fillable = [
        'annee',
        'dernier_numero',
    ];

    protected function casts(): array
    {
        return [
            'annee' => 'integer',
            'dernier_numero' => 'integer',
        ];
    }
}
