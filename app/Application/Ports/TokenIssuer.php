<?php

declare(strict_types=1);

namespace App\Application\Ports;

/**
 * Puerto emisor y verificador de tokens JWT (plan.md D-11, ADR-L001, constitution.md Art. II).
 */
interface TokenIssuer
{
    public function issue(string $userId, string $role): IssuedToken;

    /**
     * Valida firma y vigencia; lanza DomainException::invalidCredentials (401) si es inválido o venció.
     */
    public function verify(string $token): TokenClaims;
}
