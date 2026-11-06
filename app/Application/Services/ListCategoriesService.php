<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\Ports\CategoryRepository;
use App\Domain\Category;

/**
 * Caso de uso: listar las cinco categorías ordenadas por nombre (api-contract.md §3.3, data-model.md §6 Q4).
 */
final readonly class ListCategoriesService
{
    public function __construct(
        private CategoryRepository $categories,
    ) {
    }

    /**
     * @return list<Category>
     */
    public function execute(): array
    {
        return $this->categories->allOrderedByName();
    }
}
