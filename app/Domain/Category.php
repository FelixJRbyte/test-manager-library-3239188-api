<?php

declare(strict_types=1);

namespace App\Domain;

/**
 * Entidad de referencia `Category` (data-model.md §3 y §9).
 *
 * PHP puro, sin dependencias externas (constitution.md Art. I).
 */
final readonly class Category
{
    public function __construct(
        public string $id,
        public string $name,
    ) {
    }
}
