<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Application\Ports\PasswordHasher;
use App\Application\Ports\UserRepository;
use App\Domain\User;
use App\Domain\ValueObjects\Uuid;
use Illuminate\Console\Command;

/**
 * Crea de forma idempotente el administrador inicial leyendo ADMIN_USERNAME y ADMIN_PASSWORD (D3, plan.md D-10).
 *
 * Usa los puertos `UserRepository` y `PasswordHasher` sin referenciar `Infrastructure` (Art. II y III).
 * Jamás imprime ni registra la contraseña (RNF-3, Art. IX).
 */
final class EnsureAdminCommand extends Command
{
    protected $signature = 'library:ensure-admin';

    protected $description = 'Provisiona de forma idempotente el usuario admin inicial desde el entorno.';

    public function handle(UserRepository $users, PasswordHasher $hasher): int
    {
        $rawUsername = (string) env('ADMIN_USERNAME', '');
        $rawPassword = (string) env('ADMIN_PASSWORD', '');

        return $this->provision($rawUsername, $rawPassword, $users, $hasher);
    }

    public function provision(
        string $rawUsername,
        string $rawPassword,
        UserRepository $users,
        PasswordHasher $hasher,
    ): int {
        $username = User::normalizeUsername($rawUsername);
        if ($username === '' || trim($rawPassword) === '') {
            if ($this->output !== null) {
                $this->error('ADMIN_USERNAME y ADMIN_PASSWORD deben estar definidos en el entorno.');
            }

            return self::FAILURE;
        }

        if ($users->findByUsername($username) !== null) {
            if ($this->output !== null) {
                $this->info("El administrador «{$username}» ya existe.");
            }

            return self::SUCCESS;
        }

        $admin = User::provisionAdmin(
            id: Uuid::v4(),
            username: $username,
            passwordHash: $hasher->hash($rawPassword),
        );

        $users->save($admin);

        if ($this->output !== null) {
            $this->info("Administrador inicial «{$username}» creado.");
        }

        return self::SUCCESS;
    }
}
