<?php

declare(strict_types=1);

use App\Application\Services\LoginService;
use App\Domain\Exceptions\DomainException;
use App\Domain\User;
use Tests\Fakes\FakePasswordHasher;
use Tests\Fakes\FakeTokenIssuer;
use Tests\Fakes\FakeUserRepository;

// T1 · spec.md HU-01 (CA-01.1, CA-01.2), api-contract.md §3.1, tasks.md T-07, trampa 7.

it('emite un token Bearer de 8 horas (28800 s) con credenciales válidas y normaliza el usuario (CA-01.1)', function (): void {
    $users = new FakeUserRepository;
    $hasher = new FakePasswordHasher;
    $tokens = new FakeTokenIssuer;

    $users->save(new User(
        'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
        'ana',
        $hasher->hash('secreto-seguro'),
        User::ROLE_LIBRARIAN,
    ));

    $service = new LoginService($users, $hasher, $tokens);

    $issued = $service->login('  ANA ', 'secreto-seguro');

    expect($issued->tokenType)->toBe('Bearer')
        ->and($issued->expiresIn)->toBe(28800)
        ->and($issued->role)->toBe('librarian')
        ->and($issued->accessToken)->not->toBeEmpty();
});

it('responde 401 INVALID_CREDENTIALS con mensaje idéntico si falla el usuario o la clave (CA-01.2, trampa 7)', function (): void {
    $users = new FakeUserRepository;
    $hasher = new FakePasswordHasher;
    $tokens = new FakeTokenIssuer;

    $users->save(new User(
        'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
        'ana',
        $hasher->hash('clave-correcta'),
        User::ROLE_LIBRARIAN,
    ));

    $service = new LoginService($users, $hasher, $tokens);

    $missingUserError = null;
    try {
        $service->login('no-existe', 'clave-correcta');
    } catch (DomainException $e) {
        $missingUserError = $e;
    }

    $wrongPassError = null;
    try {
        $service->login('ana', 'clave-erronea');
    } catch (DomainException $e) {
        $wrongPassError = $e;
    }

    expect($missingUserError)->not->toBeNull()
        ->and($wrongPassError)->not->toBeNull()
        ->and($missingUserError->httpStatus)->toBe(401)
        ->and($wrongPassError->httpStatus)->toBe(401)
        ->and($missingUserError->errorCode)->toBe('INVALID_CREDENTIALS')
        ->and($wrongPassError->errorCode)->toBe('INVALID_CREDENTIALS')
        ->and($missingUserError->getMessage())->toBe($wrongPassError->getMessage());
});
