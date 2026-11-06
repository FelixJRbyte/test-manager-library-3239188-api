<?php

declare(strict_types=1);

use App\Console\Commands\EnsureAdminCommand;
use App\Domain\User;
use App\Domain\ValueObjects\Uuid;
use Tests\Fakes\FakePasswordHasher;
use Tests\Fakes\FakeUserRepository;

// T1 · spec.md §1, plan.md D-10, data-model.md §9, D3, D8, trampa 1.

it('genera UUID v4 válidos en RFC 4122 y distingue cadenas no UUID', function (): void {
    $id = Uuid::v4();

    expect(Uuid::isValid($id))->toBeTrue()
        ->and($id[14])->toBe('4')
        ->and(Uuid::isValid('no-es-un-uuid'))->toBeFalse();
});

it('crea el administrador inicial de forma idempotente sin duplicarlo ni guardar la clave en claro (D3)', function (): void {
    $users = new FakeUserRepository;
    $hasher = new FakePasswordHasher;
    $command = new EnsureAdminCommand;

    $firstExit = $command->provision(' Admin ', 'ClaveSegura123!', $users, $hasher);
    $secondExit = $command->provision('admin', 'OtraClaveDistinta!', $users, $hasher);

    $stored = $users->findByUsername('admin');

    expect($firstExit)->toBe(EnsureAdminCommand::SUCCESS)
        ->and($secondExit)->toBe(EnsureAdminCommand::SUCCESS)
        ->and($stored)->not->toBeNull()
        ->and($stored->role)->toBe(User::ROLE_ADMIN)
        ->and($stored->passwordHash)->not->toBe('ClaveSegura123!')
        ->and($hasher->verify('ClaveSegura123!', $stored->passwordHash))->toBeTrue();
});
