<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PieceJointe extends Model
{
    use HasFactory;

    protected $table = 'pieces_jointes';

    protected $fillable = [
        'courrier_id',
        'nom_original',
        'nom_stockage',
        'chemin',
        'extension',
        'mime_type',
        'taille',
        'ordre',
    ];

    protected function casts(): array
    {
        return [
            'taille' => 'integer',
            'ordre' => 'integer',
        ];
    }

    public function estPrevisualisable(): bool
    {
        return str_starts_with($this->mime_type, 'image/')
            || $this->mime_type === 'application/pdf';
    }

    public function iconeBootstrap(): string
    {
        if ($this->mime_type === 'application/pdf') {
            return 'bi-filetype-pdf';
        }

        if (str_starts_with($this->mime_type, 'image/')) {
            return 'bi-file-earmark-image';
        }

        return 'bi-paperclip';
    }

    public function courrier(): BelongsTo
    {
        return $this->belongsTo(Courrier::class);
    }
}
