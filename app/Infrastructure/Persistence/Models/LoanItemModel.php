<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class LoanItemModel extends LibraryModel
{
    protected $table = 'library.loan_item';

    protected function casts(): array
    {
        return [
            'due_at' => 'immutable_datetime',
            'returned_at' => 'immutable_datetime',
        ];
    }

    public function loan(): BelongsTo
    {
        return $this->belongsTo(LoanModel::class, 'loan_id', 'id');
    }
}
