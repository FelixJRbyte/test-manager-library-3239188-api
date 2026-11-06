<?php

declare(strict_types=1);

namespace App\Application\Ports;

/**
 * Claims verificados de un JWT (api-contract.md §4: `sub` y `role`, sin datos personales).
 */
final readonly class TokenClaims
{
    public function __construct(
        public string $userId,
        public string $role,
        public int $issuedAt,
        public int $expiresAt,
    ) {
    }
}
