<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use App\Application\Ports\PasswordHasher;
use Illuminate\Support\Facades\Hash;

/**
 * Adaptador BCrypt sobre Hash de Laravel (plan.md D-09, trampa 11).
 */
final class BcryptPasswordHasher implements PasswordHasher
{
    public function hash(string $plainPassword): string
    {
        return Hash::make($plainPassword);
    }

    public function verify(string $plainPassword, string $passwordHash): bool
    {
        return Hash::check($plainPassword, $passwordHash);
    }
}
