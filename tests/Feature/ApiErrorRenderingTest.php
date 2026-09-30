<?php

namespace Tests\Feature;

use Tests\TestCase;

class ApiErrorRenderingTest extends TestCase
{
    /**
     * Sotto /api gli errori escono in JSON anche se il client non chiede JSON.
     */
    public function test_api_error_is_json_without_accept_header(): void
    {
        $response = $this->get('/api/missing-route', ['Accept' => 'text/html']);

        $response->assertNotFound()
            ->assertHeader('Content-Type', 'application/json')
            ->assertJsonStructure(['message']);
    }
}
