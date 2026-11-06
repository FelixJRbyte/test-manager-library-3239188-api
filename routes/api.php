<?php

declare(strict_types=1);

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\HealthController;
use Illuminate\Support\Facades\Route;

// El prefijo `/api/v1` se aplica en `bootstrap/app.php` (withRouting apiPrefix).
// Públicos (api-contract.md §1 y §3.1, CA-01.3):
Route::get('/health', HealthController::class);
Route::post('/auth/login', [AuthController::class, 'login']);

// Autenticados con Bearer JWT (CA-01.3):
Route::middleware('jwt.auth')->group(function (): void {
    Route::get('/categories', [CategoryController::class, 'index']);
});
