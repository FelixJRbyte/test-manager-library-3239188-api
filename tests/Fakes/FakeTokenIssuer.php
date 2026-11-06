<?php

declare(strict_types=1);

namespace Tests\Fakes;

use App\Application\Ports\IssuedToken;
use App\Application\Ports\TokenClaims;
use App\Application\Ports\TokenIssuer;
use App\Domain\Exceptions\DomainException;

final class FakeTokenIssuer implements TokenIssuer
{
    private const TTL = 28800;

    public function issue(string $userId, string $role): IssuedToken
    {
        $payload = base64_encode((string) json_encode([
            'sub' => $userId,
            'role' => $role,
            'iat' => 1760451300,
            'exp' => 1760451300 + self::TTL,
        ]));

        return new IssuedToken(
            accessToken: "fake.{$payload}.sig",
            tokenType: 'Bearer',
            expiresIn: self::TTL,
            role: $role,
        );
    }

    public function verify(string $token): TokenClaims
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3 || $parts[0] !== 'fake') {
            throw DomainException::invalidCredentials('Sesión no válida o vencida. Inicie sesión de nuevo.');
        }

        $decoded = json_decode((string) base64_decode($parts[1], true), true);
        if (! is_array($decoded) || ! isset($decoded['sub'], $decoded['role'], $decoded['iat'], $decoded['exp'])) {
            throw DomainException::invalidCredentials('Sesión no válida o vencida. Inicie sesión de nuevo.');
        }

        return new TokenClaims(
            userId: (string) $decoded['sub'],
            role: (string) $decoded['role'],
            issuedAt: (int) $decoded['iat'],
            expiresAt: (int) $decoded['exp'],
        );
    }
}
