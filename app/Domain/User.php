<?php

declare(strict_types=1);

namespace App\Domain;

use App\Domain\Exceptions\DomainException;

/**
 * Agregado de dominio `User` (spec.md §1, HU-01, HU-02; data-model.md §3; architecture.md §3).
 *
 * PHP puro, sin dependencias externas (constitution.md Art. I).
 * Nunca recibe ni almacena la contraseña en claro (plan.md D-09, Art. IX).
 */
final class User
{
    public const ROLE_ADMIN = 'admin';

    public const ROLE_LIBRARIAN = 'librarian';

    public readonly string $username;

    public function __construct(
        public readonly string $id,
        string $username,
        public readonly string $passwordHash,
        public readonly string $role,
    ) {
        $normalized = self::normalizeUsername($username);
        if ($normalized === '' || strlen($normalized) > 120) {
            throw DomainException::validationError(
                'El nombre de usuario es obligatorio y no puede superar 120 caracteres.',
                [['field' => 'username', 'issue' => 'obligatorio, máximo 120 caracteres']],
            );
        }

        if (trim($passwordHash) === '') {
            throw DomainException::validationError(
                'El hash de contraseña no puede estar vacío.',
                [['field' => 'password', 'issue' => 'obligatoria']],
            );
        }

        if (! in_array($role, [self::ROLE_ADMIN, self::ROLE_LIBRARIAN], true)) {
            throw DomainException::validationError(
                'El rol debe ser «admin» o «librarian».',
                [['field' => 'role', 'issue' => 'rol inválido']],
            );
        }

        $this->username = $normalized;
    }

    public static function normalizeUsername(string $username): string
    {
        return mb_strtolower(trim($username), 'UTF-8');
    }

    /**
     * Alta de bibliotecario por la API (HU-02: CA-02.1, CA-02.2, CA-02.3; D21).
     * Nadie otorga el rol `admin` en ejecución.
     */
    public static function registerLibrarian(
        string $id,
        string $username,
        string $passwordHash,
        string $requestedRole,
    ): self {
        if ($requestedRole !== self::ROLE_LIBRARIAN) {
            throw DomainException::validationError(
                'Por la API solo se permite dar de alta usuarios con el rol «librarian».',
                [['field' => 'role', 'issue' => 'solo se admite librarian']],
            );
        }

        return new self($id, $username, $passwordHash, self::ROLE_LIBRARIAN);
    }

    /**
     * Provisiona el administrador inicial desde variables de entorno (spec.md §1, D3).
     */
    public static function provisionAdmin(
        string $id,
        string $username,
        string $passwordHash,
    ): self {
        return new self($id, $username, $passwordHash, self::ROLE_ADMIN);
    }
}
