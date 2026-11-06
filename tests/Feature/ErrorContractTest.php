<?php

declare(strict_types=1);

use App\Domain\Exceptions\DomainException;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;

// T1 (paso 3) · RNF-4, api-contract.md §1 y §2, D12, D13.
// Todo error HTTP bajo /api/* responde { code, message, details } en español,
// sin trazas, SQL, rutas del servidor ni contraseñas.

it('devuelve 404 NOT_FOUND con cuerpo único en rutas inexistentes bajo /api/v1', function (): void {
    $response = $this->getJson('/api/v1/ruta-que-no-existe');

    $response->assertStatus(404)
        ->assertExactJson([
            'code' => 'NOT_FOUND',
            'message' => 'El recurso solicitado no existe.',
            'details' => [],
        ]);
});

it('traduce DomainException al código y estado del contrato con details', function (): void {
    Route::get('/api/v1/_test/domain-error', static function (): never {
        throw DomainException::bookNotAvailable('Cien años de soledad', 0);
    });

    $response = $this->getJson('/api/v1/_test/domain-error');

    $response->assertStatus(409)
        ->assertExactJson([
            'code' => 'BOOK_NOT_AVAILABLE',
            'message' => 'No quedan ejemplares disponibles de «Cien años de soledad».',
            'details' => [
                ['field' => 'items[0].book_id', 'issue' => 'sin ejemplares'],
            ],
        ]);
});

it('traduce ValidationException a 422 VALIDATION_ERROR con lista de {field, issue}', function (): void {
    Route::post('/api/v1/_test/validation-error', static function (): never {
        throw ValidationException::withMessages([
            'isbn' => ['El ISBN debe tener 13 dígitos.'],
        ]);
    });

    $response = $this->postJson('/api/v1/_test/validation-error');

    $response->assertStatus(422)
        ->assertExactJson([
            'code' => 'VALIDATION_ERROR',
            'message' => 'Los datos enviados no son válidos.',
            'details' => [
                ['field' => 'isbn', 'issue' => 'El ISBN debe tener 13 dígitos.'],
            ],
        ]);
});

it('oculta trazas, SQL y contraseñas ante un error inesperado (500 INTERNAL_ERROR)', function (): void {
    Route::get('/api/v1/_test/unexpected-error', static function (): never {
        throw new RuntimeException('SELECT * FROM library."user" WHERE password_hash = secret123 in /var/www/html');
    });

    $response = $this->getJson('/api/v1/_test/unexpected-error');

    $response->assertStatus(500)
        ->assertExactJson([
            'code' => 'INTERNAL_ERROR',
            'message' => 'Ocurrió un error interno. Intente nuevamente.',
            'details' => [],
        ]);

    expect($response->getContent())
        ->not->toContain('SELECT')
        ->not->toContain('secret123')
        ->not->toContain('/var/www')
        ->not->toContain('trace');
});
