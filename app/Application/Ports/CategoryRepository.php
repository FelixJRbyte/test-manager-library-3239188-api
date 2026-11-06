<?php

declare(strict_types=1);

namespace App\Application\Ports;

use App\Domain\Category;

/**
 * Puerto de lectura de categorías (api-contract.md §3.3, data-model.md §6 Q4, constitution.md Art. II).
 */
interface CategoryRepository
{
    /**
     * @return list<Category>
     */
    public function allOrderedByName(): array;

    public function find(string $id): ?Category;
}
