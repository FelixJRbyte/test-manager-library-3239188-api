<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\Ports\UserRepository;
use App\Domain\Exceptions\DomainException;
use App\Domain\User;
use App\Infrastructure\Persistence\Models\UserModel;
use Illuminate\Database\UniqueConstraintViolationException;

final class EloquentUserRepository implements UserRepository
{
    public function findByUsername(string $normalizedUsername): ?User
    {
        $normalized = User::normalizeUsername($normalizedUsername);
        if ($normalized === '') {
            return null;
        }

        /** @var UserModel|null $model */
        $model = UserModel::query()->where('username', $normalized)->first();
        if ($model === null) {
            return null;
        }

        return new User(
            id: (string) $model->id,
            username: (string) $model->username,
            passwordHash: (string) $model->password_hash,
            role: (string) $model->role,
        );
    }

    public function save(User $user): void
    {
        if (UserModel::query()->where('username', $user->username)->exists()) {
            throw DomainException::usernameTaken($user->username);
        }

        try {
            UserModel::query()->create([
                'id' => $user->id,
                'username' => $user->username,
                'password_hash' => $user->passwordHash,
                'role' => $user->role,
            ]);
        } catch (UniqueConstraintViolationException) {
            throw DomainException::usernameTaken($user->username);
        }
    }
}
