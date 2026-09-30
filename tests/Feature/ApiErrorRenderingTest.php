<?php

namespace Tests\Feature;

use Tests\TestCase;

class ApiErrorRenderingTest extends TestCase
{
    /**
     * Sotto /api gli errori escono in JSON anche se il client non chiede JSON.
     */
    public function test_unknown_api_route_returns_404_json_for_html_client(): void
    {
        $response = $this->get('/api/missing-route', ['Accept' => 'text/html']);

        $response->assertNotFound()
            ->assertHeader('Content-Type', 'application/json')
            ->assertJsonStructure(['message']);
    }
}
