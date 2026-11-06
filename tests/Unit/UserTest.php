<?php

declare(strict_types=1);

use App\Domain\Exceptions\DomainException;
use App\Domain\User;

// T1 / T2 · spec.md HU-02 (CA-02.1, CA-02.2, CA-02.3), data-model.md §3, tasks.md T-06, D21.

it('normaliza el nombre de usuario en minúsculas y recortado («Ana » queda «ana»)', function (): void {
    $user = User::registerLibrarian(
        'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
        '  Ana  ',
        '$2y$12$hash',
        'librarian',
    );

    expect($user->username)->toBe('ana')
        ->and($user->role)->toBe(User::ROLE_LIBRARIAN);
});

it('rechaza con 422 VALIDATION_ERROR pedir el rol admin por la API (CA-02.3)', function (): void {
    try {
        User::registerLibrarian(
            'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
            'carlos',
            '$2y$12$hash',
            'admin',
        );
        $this->fail('Debió lanzar DomainException');
    } catch (DomainException $e) {
        expect($e->errorCode)->toBe('VALIDATION_ERROR')
            ->and($e->httpStatus)->toBe(422)
            ->and($e->details[0]['field'])->toBe('role');
    }
});

it('permite provisionar el administrador inicial desde el arranque (D3)', function (): void {
    $admin = User::provisionAdmin(
        'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb',
        ' Admin ',
        '$2y$12$hash',
    );

    expect($admin->username)->toBe('admin')
        ->and($admin->role)->toBe(User::ROLE_ADMIN);
});

it('rechaza un rol desconocido en el agregado User', function (): void {
    expect(fn () => new User(
        'cccccccc-cccc-4ccc-8ccc-cccccccccccc',
        'luisa',
        '$2y$12$hash',
        'member',
    ))->toThrow(DomainException::class);
});
