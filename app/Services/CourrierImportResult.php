<?php

namespace App\Services;

class CourrierImportResult
{
    /**
     * @param  list<array{ligne: int, numero: string}>  $succes
     * @param  list<array{ligne: int, message: string}>  $erreurs
     */
    public function __construct(
        public readonly array $succes = [],
        public readonly array $erreurs = [],
    ) {}

    public function nbSucces(): int
    {
        return count($this->succes);
    }

    public function nbErreurs(): int
    {
        return count($this->erreurs);
    }

    public function aDesErreurs(): bool
    {
        return $this->nbErreurs() > 0;
    }

    public function aDesSucces(): bool
    {
        return $this->nbSucces() > 0;
    }
}
