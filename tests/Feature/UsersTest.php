<?php

declare(strict_types=1);

use App\Application\Ports\PasswordHasher;
use App\Application\Ports\TokenIssuer;
use App\Application\Ports\UserRepository;
use App\Domain\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// T2 · spec.md HU-02 (CA-02.1, CA-02.2, CA-02.3), api-contract.md §3.2, RNF-3, D21.

beforeEach(function (): void {
    putenv('JWT_SECRET=test-jwt-secret-key-with-at-least-32-chars!!');
    $_ENV['JWT_SECRET'] = 'test-jwt-secret-key-with-at-least-32-chars!!';
});

it('crea un bibliotecario (201) sin exponer password ni password_hash y rechaza duplicados (409) y rol admin (422)', function (): void {
    /** @var UserRepository $users */
    $users = $this->app->make(UserRepository::class);
    /** @var PasswordHasher $hasher */
    $hasher = $this->app->make(PasswordHasher::class);
    /** @var TokenIssuer $tokens */
    $tokens = $this->app->make(TokenIssuer::class);

    $admin = User::provisionAdmin(
        'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
        'admin',
        $hasher->hash('AdminPass123'),
    );
    $users->save($admin);

    $adminToken = $tokens->issue($admin->id, $admin->role)->accessToken;

    $created = $this->withHeader('Authorization', "Bearer {$adminToken}")
        ->postJson('/api/v1/users', [
            'username' => 'Ana ',
            'password' => 'BiblioPass123',
            'role' => 'librarian',
        ]);

    $created->assertStatus(201)
        ->assertJsonStructure(['id', 'username', 'role'])
        ->assertJson([
            'username' => 'ana',
            'role' => 'librarian',
        ])
        ->assertJsonMissingPath('password')
        ->assertJsonMissingPath('password_hash');

    expect($created->getContent())->not->toContain('BiblioPass123');

    // CA-02.2: username repetido (incluso con mayúsculas/espacios) -> 409 USERNAME_TAKEN
    $this->withHeader('Authorization', "Bearer {$adminToken}")
        ->postJson('/api/v1/users', [
            'username' => '  ANA',
            'password' => 'OtraClave123',
            'role' => 'librarian',
        ])
        ->assertStatus(409)
        ->assertJsonPath('code', 'USERNAME_TAKEN');

    // CA-02.3: pedir rol admin -> 422 VALIDATION_ERROR
    $this->withHeader('Authorization', "Bearer {$adminToken}")
        ->postJson('/api/v1/users', [
            'username' => 'otro',
            'password' => 'OtraClave123',
            'role' => 'admin',
        ])
        ->assertStatus(422)
        ->assertJsonPath('code', 'VALIDATION_ERROR');
});

it('responde 403 FORBIDDEN_ROLE cuando un librarian intenta dar de alta usuarios (CA-01.3)', function (): void {
    /** @var TokenIssuer $tokens */
    $tokens = $this->app->make(TokenIssuer::class);
    $librarianToken = $tokens->issue('bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb', User::ROLE_LIBRARIAN)->accessToken;

    $this->withHeader('Authorization', "Bearer {$librarianToken}")
        ->postJson('/api/v1/users', [
            'username' => 'nuevo',
            'password' => 'Clave12345',
            'role' => 'librarian',
        ])
        ->assertStatus(403)
        ->assertJsonPath('code', 'FORBIDDEN_ROLE');
});
