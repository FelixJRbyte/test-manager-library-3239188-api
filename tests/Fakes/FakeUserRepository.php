<?php

declare(strict_types=1);

namespace Tests\Fakes;

use App\Application\Ports\UserRepository;
use App\Domain\Exceptions\DomainException;
use App\Domain\User;

final class FakeUserRepository implements UserRepository
{
    /** @var array<string, User> */
    private array $byUsername = [];

    public function findByUsername(string $normalizedUsername): ?User
    {
        return $this->byUsername[$normalizedUsername] ?? null;
    }

    public function save(User $user): void
    {
        if (isset($this->byUsername[$user->username])) {
            throw DomainException::usernameTaken($user->username);
        }

        $this->byUsername[$user->username] = $user;
    }
}
