<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use App\Application\Ports\Clock;
use App\Application\Ports\IssuedToken;
use App\Application\Ports\TokenClaims;
use App\Application\Ports\TokenIssuer;
use App\Domain\Exceptions\DomainException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use RuntimeException;
use Throwable;

/**
 * Emisor y verificador JWT HS256 con firebase/php-jwt (plan.md D-11, api-contract.md §3.1 y §4, D4, ADR-L001).
 *
 * Vigencia: 8 horas (28800 s). Claims: `sub` y `role` (sin datos personales).
 */
final readonly class JwtTokenIssuer implements TokenIssuer
{
    private const ALGORITHM = 'HS256';

    public function __construct(
        private Clock $clock,
        private string $secret,
        private int $ttlSeconds = 28800,
    ) {
    }

    public function issue(string $userId, string $role): IssuedToken
    {
        $secret = $this->requireSecret();
        $issuedAt = $this->clock->now()->getTimestamp();
        $expiresAt = $issuedAt + $this->ttlSeconds;

        $payload = [
            'sub' => $userId,
            'role' => $role,
            'iat' => $issuedAt,
            'exp' => $expiresAt,
        ];

        $jwt = JWT::encode($payload, $secret, self::ALGORITHM);

        return new IssuedToken(
            accessToken: $jwt,
            tokenType: 'Bearer',
            expiresIn: $this->ttlSeconds,
            role: $role,
        );
    }

    public function verify(string $token): TokenClaims
    {
        if (trim($token) === '') {
            throw DomainException::invalidCredentials('Sesión no válida o vencida. Inicie sesión de nuevo.');
        }

        $secret = $this->requireSecret();
        JWT::$timestamp = $this->clock->now()->getTimestamp();

        try {
            $decoded = JWT::decode($token, new Key($secret, self::ALGORITHM));
        } catch (Throwable) {
            throw DomainException::invalidCredentials('Sesión no válida o vencida. Inicie sesión de nuevo.');
        } finally {
            JWT::$timestamp = null;
        }

        $sub = isset($decoded->sub) && is_string($decoded->sub) ? trim($decoded->sub) : '';
        $role = isset($decoded->role) && is_string($decoded->role) ? trim($decoded->role) : '';
        $iat = isset($decoded->iat) && is_int($decoded->iat) ? $decoded->iat : 0;
        $exp = isset($decoded->exp) && is_int($decoded->exp) ? $decoded->exp : 0;

        if ($sub === '' || $role === '' || $iat <= 0 || $exp <= 0) {
            throw DomainException::invalidCredentials('Sesión no válida o vencida. Inicie sesión de nuevo.');
        }

        return new TokenClaims(
            userId: $sub,
            role: $role,
            issuedAt: $iat,
            expiresAt: $exp,
        );
    }

    private function requireSecret(): string
    {
        if (trim($this->secret) === '') {
            throw new RuntimeException('JWT_SECRET must be configured.');
        }

        return $this->secret;
    }
}
