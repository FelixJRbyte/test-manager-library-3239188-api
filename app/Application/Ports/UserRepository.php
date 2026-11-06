<?php

declare(strict_types=1);

namespace App\Application\Ports;

use App\Domain\User;

/**
 * Puerto de persistencia de usuarios (data-model.md §3 y §6 Q10, constitution.md Art. II).
 */
interface UserRepository
{
    public function findByUsername(string $normalizedUsername): ?User;

    /**
     * Persiste un usuario nuevo; si el username ya existe lanza DomainException::usernameTaken (409).
     */
    public function save(User $user): void;
}
