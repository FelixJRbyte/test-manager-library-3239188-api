<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\Ports\PasswordHasher;
use App\Application\Ports\UserRepository;
use App\Domain\Exceptions\DomainException;
use App\Domain\User;
use App\Domain\ValueObjects\Uuid;

/**
 * Caso de uso: dar de alta un bibliotecario (spec.md HU-02: CA-02.1, CA-02.2, CA-02.3; api-contract.md §3.2).
 */
final readonly class CreateLibrarianService
{
    public function __construct(
        private UserRepository $users,
        private PasswordHasher $hasher,
    ) {
    }

    public function execute(string $username, string $plainPassword, string $role): User
    {
        if (trim($plainPassword) === '') {
            throw DomainException::validationError(
                'La contraseña es obligatoria.',
                [['field' => 'password', 'issue' => 'obligatoria']],
            );
        }

        $user = User::registerLibrarian(
            id: Uuid::v4(),
            username: $username,
            passwordHash: $this->hasher->hash($plainPassword),
            requestedRole: $role,
        );

        if ($this->users->findByUsername($user->username) !== null) {
            throw DomainException::usernameTaken($user->username);
        }

        $this->users->save($user);

        return $user;
    }
}
