<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class LoanModel extends LibraryModel
{
    protected $table = 'library.loan';

    protected function casts(): array
    {
        return [
            'loaned_at' => 'immutable_datetime',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(MemberModel::class, 'member_id', 'id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(LoanItemModel::class, 'loan_id', 'id');
    }
}
