<?php

namespace App\Modules\Assistant\Application\Contracts;

interface EmbeddingProvider
{
    /**
     * @return list<float>
     */
    public function embed(string $text): array;

    public function model(): string;
}
