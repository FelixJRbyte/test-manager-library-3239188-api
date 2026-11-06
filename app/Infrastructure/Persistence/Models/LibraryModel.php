<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Modelo Eloquent base de Manager Library (D8, ADR-L004).
 *
 * - Sin `created_at` ni `updated_at` (data-model.md §3 y §8).
 * - Clave primaria UUID v4 como texto (`Str::uuid()`), nunca UUID v7 (trampa 1, D8).
 * - Vive en `Infrastructure\Persistence`: el dominio y la aplicación no conocen Eloquent (Arts. I y II).
 */
abstract class LibraryModel extends Model
{
    use HasUuids;

    public $timestamps = false;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    public function newUniqueId(): string
    {
        return (string) Str::uuid();
    }
}
