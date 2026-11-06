<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\Ports\CategoryRepository;
use App\Domain\Category;
use App\Infrastructure\Persistence\Models\CategoryModel;
use Illuminate\Support\Str;

final class EloquentCategoryRepository implements CategoryRepository
{
    public function allOrderedByName(): array
    {
        return CategoryModel::query()
            ->orderBy('name')
            ->get()
            ->map(static fn (CategoryModel $model): Category => new Category(
                id: (string) $model->id,
                name: (string) $model->name,
            ))
            ->values()
            ->all();
    }

    public function find(string $id): ?Category
    {
        if (! Str::isUuid($id)) {
            return null;
        }

        /** @var CategoryModel|null $model */
        $model = CategoryModel::query()->find($id);
        if ($model === null) {
            return null;
        }

        return new Category(
            id: (string) $model->id,
            name: (string) $model->name,
        );
    }
}
