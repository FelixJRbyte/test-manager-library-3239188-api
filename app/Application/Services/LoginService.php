<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\Ports\IssuedToken;
use App\Application\Ports\PasswordHasher;
use App\Application\Ports\TokenIssuer;
use App\Application\Ports\UserRepository;
use App\Domain\Exceptions\DomainException;
use App\Domain\User;

/**
 * Caso de uso: iniciar sesión (spec.md HU-01: CA-01.1, CA-01.2; architecture.md §2).
 *
 * Depende únicamente del dominio y de los puertos de `Application\Ports` (constitution.md Art. II y IV SRP).
 */
final readonly class LoginService
{
    private const INVALID_MESSAGE = 'Usuario o contraseña incorrectos.';

    public function __construct(
        private UserRepository $users,
        private PasswordHasher $hasher,
        private TokenIssuer $tokens,
    ) {
    }

    public function login(string $username, string $plainPassword): IssuedToken
    {
        $normalized = User::normalizeUsername($username);
        if ($normalized === '' || $plainPassword === '') {
            throw DomainException::invalidCredentials(self::INVALID_MESSAGE);
        }

        $user = $this->users->findByUsername($normalized);
        if ($user === null || ! $this->hasher->verify($plainPassword, $user->passwordHash)) {
            throw DomainException::invalidCredentials(self::INVALID_MESSAGE);
        }

        return $this->tokens->issue($user->id, $user->role);
    }
}
