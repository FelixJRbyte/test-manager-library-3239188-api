<?php

declare(strict_types=1);

use App\Http\Controllers\HealthController;
use Illuminate\Support\Facades\Route;

// El prefijo `/api/v1` se aplica en `bootstrap/app.php` (withRouting apiPrefix).
// GET /api/v1/health es público (api-contract.md §1 y §3.1, CA-01.3).
Route::get('/health', HealthController::class);
