<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Models;

final class UserModel extends LibraryModel
{
    protected $table = 'library.user';

    /**
     * El hash jamás sale en respuestas ni serializaciones (RNF-3, Art. IX, trampa 11).
     *
     * @var list<string>
     */
    protected $hidden = [
        'password_hash',
        'password',
    ];
}
