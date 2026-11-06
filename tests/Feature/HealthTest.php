<?php

declare(strict_types=1);

// T0 · CA-01.3, api-contract.md §1 y §3.1, D19:
// GET /api/v1/health es público y responde 200 {"status":"ok"} sin tocar la base.

it('responde 200 con status ok en GET /api/v1/health sin token', function (): void {
    $response = $this->getJson('/api/v1/health');

    $response->assertStatus(200)
        ->assertExactJson(['status' => 'ok']);
});
