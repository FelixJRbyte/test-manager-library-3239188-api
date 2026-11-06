<?php

declare(strict_types=1);

use App\Application\Ports\PasswordHasher;
use App\Application\Ports\UserRepository;
use App\Domain\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// T1 · spec.md HU-01 (CA-01.1, CA-01.2, CA-01.3), api-contract.md §3.1, §3.3 y §4, trampa 7.

beforeEach(function (): void {
    putenv('JWT_SECRET=test-jwt-secret-key-with-at-least-32-chars!!');
    $_ENV['JWT_SECRET'] = 'test-jwt-secret-key-with-at-least-32-chars!!';
});

it('autentica con credenciales válidas y devuelve access_token Bearer de 28800 s y role (CA-01.1)', function (): void {
    /** @var UserRepository $users */
    $users = $this->app->make(UserRepository::class);
    /** @var PasswordHasher $hasher */
    $hasher = $this->app->make(PasswordHasher::class);

    $users->save(new User(
        'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
        'ana',
        $hasher->hash('secreto123'),
        User::ROLE_LIBRARIAN,
    ));

    $response = $this->postJson('/api/v1/auth/login', [
        'username' => ' Ana ',
        'password' => 'secreto123',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure(['access_token', 'token_type', 'expires_in', 'role'])
        ->assertJson([
            'token_type' => 'Bearer',
            'expires_in' => 28800,
            'role' => 'librarian',
        ]);
});

it('responde 401 INVALID_CREDENTIALS con el mismo mensaje para usuario inexistente o clave errónea (CA-01.2)', function (): void {
    /** @var UserRepository $users */
    $users = $this->app->make(UserRepository::class);
    /** @var PasswordHasher $hasher */
    $hasher = $this->app->make(PasswordHasher::class);

    $users->save(new User(
        'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
        'ana',
        $hasher->hash('secreto123'),
        User::ROLE_LIBRARIAN,
    ));

    $r1 = $this->postJson('/api/v1/auth/login', [
        'username' => 'noexiste',
        'password' => 'secreto123',
    ]);

    $r2 = $this->postJson('/api/v1/auth/login', [
        'username' => 'ana',
        'password' => 'otra-clave',
    ]);

    $r1->assertStatus(401)->assertJsonPath('code', 'INVALID_CREDENTIALS');
    $r2->assertStatus(401)->assertJsonPath('code', 'INVALID_CREDENTIALS');
    expect($r1->json('message'))->toBe($r2->json('message'));
});

it('exige token en GET /api/v1/categories (401 sin token) y devuelve las 5 categorías ordenadas por nombre (CA-01.3, §3.3)', function (): void {
    $this->getJson('/api/v1/categories')
        ->assertStatus(401)
        ->assertJsonPath('code', 'INVALID_CREDENTIALS');

    /** @var UserRepository $users */
    $users = $this->app->make(UserRepository::class);
    /** @var PasswordHasher $hasher */
    $hasher = $this->app->make(PasswordHasher::class);

    $users->save(new User(
        'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
        'ana',
        $hasher->hash('secreto123'),
        User::ROLE_LIBRARIAN,
    ));

    $token = (string) $this->postJson('/api/v1/auth/login', [
        'username' => 'ana',
        'password' => 'secreto123',
    ])->json('access_token');

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/categories');

    $response->assertStatus(200);
    $names = array_column($response->json('items'), 'name');
    expect($names)->toBe(['Ciencia', 'Historia', 'Infantil', 'Novela', 'Tecnología']);
});
