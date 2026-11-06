<?php

declare(strict_types=1);

use App\Application\Services\CreateLibrarianService;
use App\Domain\Exceptions\DomainException;
use Tests\Fakes\FakePasswordHasher;
use Tests\Fakes\FakeUserRepository;

// T2 · spec.md HU-02 (CA-02.1, CA-02.2, CA-02.3), api-contract.md §3.2, tasks.md T-07, D21.

it('crea un bibliotecario con username normalizado en minúsculas y recortado y clave hasheada (CA-02.1, CA-02.2)', function (): void {
    $users = new FakeUserRepository;
    $hasher = new FakePasswordHasher;
    $service = new CreateLibrarianService($users, $hasher);

    $created = $service->execute('  Ana  ', 'ClaveSegura123', 'librarian');

    expect($created->username)->toBe('ana')
        ->and($created->role)->toBe('librarian')
        ->and($created->passwordHash)->not->toBe('ClaveSegura123')
        ->and($hasher->verify('ClaveSegura123', $created->passwordHash))->toBeTrue();
});

it('lanza 409 USERNAME_TAKEN si el nombre normalizado ya existe (CA-02.2)', function (): void {
    $users = new FakeUserRepository;
    $hasher = new FakePasswordHasher;
    $service = new CreateLibrarianService($users, $hasher);

    $service->execute('ana', 'ClaveSegura123', 'librarian');

    try {
        $service->execute(' ANA ', 'OtraClave123', 'librarian');
        $this->fail('Debió lanzar DomainException USERNAME_TAKEN');
    } catch (DomainException $e) {
        expect($e->errorCode)->toBe('USERNAME_TAKEN')
            ->and($e->httpStatus)->toBe(409);
    }
});

it('lanza 422 VALIDATION_ERROR si se pide el rol admin por la API (CA-02.3)', function (): void {
    $users = new FakeUserRepository;
    $hasher = new FakePasswordHasher;
    $service = new CreateLibrarianService($users, $hasher);

    try {
        $service->execute('carlos', 'ClaveSegura123', 'admin');
        $this->fail('Debió lanzar DomainException VALIDATION_ERROR');
    } catch (DomainException $e) {
        expect($e->errorCode)->toBe('VALIDATION_ERROR')
            ->and($e->httpStatus)->toBe(422);
    }
});
