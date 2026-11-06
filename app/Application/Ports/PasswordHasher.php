<?php

declare(strict_types=1);

namespace App\Application\Ports;

/**
 * Puerto de hash de contraseñas (plan.md D-09, constitution.md Art. II, IV ISP y IX).
 */
interface PasswordHasher
{
    public function hash(string $plainPassword): string;

    public function verify(string $plainPassword, string $passwordHash): bool;
}
