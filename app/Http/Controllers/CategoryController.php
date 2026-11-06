<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\Services\ListCategoriesService;
use App\Domain\Category;
use Illuminate\Http\JsonResponse;

/**
 * Controlador de categorías (api-contract.md §3.3: cinco categorías, orden por nombre).
 */
final readonly class CategoryController
{
    public function __construct(
        private ListCategoriesService $listCategories,
    ) {
    }

    public function index(): JsonResponse
    {
        $items = array_map(
            static fn (Category $c): array => [
                'id' => $c->id,
                'name' => $c->name,
            ],
            $this->listCategories->execute(),
        );

        return new JsonResponse(['items' => $items], 200, [], JSON_UNESCAPED_UNICODE);
    }
}
