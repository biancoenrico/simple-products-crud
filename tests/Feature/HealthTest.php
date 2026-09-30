<?php

namespace Tests\Feature;

use Tests\TestCase;

class HealthTest extends TestCase
{
    /**
     * La rotta di salute risponde senza autenticazione.
     */
    public function test_health_returns_ok(): void
    {
        $response = $this->get(route('health'));

        $response->assertOk()->assertExactJson(['status' => 'ok']);
    }
}
