<?php

declare(strict_types=1);

namespace App\Application\Ports;

/**
 * Resultado de emitir un token de acceso (api-contract.md §3.1 y §4).
 */
final readonly class IssuedToken
{
    public function __construct(
        public string $accessToken,
        public string $tokenType,
        public int $expiresIn,
        public string $role,
    ) {
    }
}
