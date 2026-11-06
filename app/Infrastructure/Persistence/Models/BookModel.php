<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

final class BookModel extends LibraryModel
{
    use SoftDeletes;

    protected $table = 'library.book';

    protected function casts(): array
    {
        return [
            'total_copies' => 'integer',
            'available_copies' => 'integer',
            'deleted_at' => 'immutable_datetime',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(CategoryModel::class, 'category_id', 'id');
    }
}
