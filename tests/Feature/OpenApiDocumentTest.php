<?php

namespace Tests\Feature;

use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class OpenApiDocumentTest extends TestCase
{
    /**
     * Genera il documento dagli attributi, come al deploy, e lo legge senza token.
     * Il percorso è scritto per esteso perché è quello che AG chiama.
     */
    private function fetchDocument(): TestResponse
    {
        config(['l5-swagger.defaults.paths.docs' => sys_get_temp_dir()]);
        $this->artisan('l5-swagger:generate')->assertSuccessful();

        return $this->getJson('/docs/api-docs.json');
    }

    public function test_document_is_public_and_declares_openapi_3_0_0(): void
    {
        $this->fetchDocument()
            ->assertOk()
            ->assertJsonPath('openapi', '3.0.0');
    }

    public function test_document_describes_every_product_operation(): void
    {
        $paths = $this->fetchDocument()->json('paths');

        $this->assertEqualsCanonicalizing(['get', 'post'], array_keys($paths['/api/v1/products']));
        $this->assertEqualsCanonicalizing(['get', 'put', 'delete'], array_keys($paths['/api/v1/products/{productId}']));
    }

    public function test_document_declares_bearer_security_on_every_operation(): void
    {
        $response = $this->fetchDocument()
            ->assertJsonPath('components.securitySchemes.bearerAuth.type', 'http')
            ->assertJsonPath('components.securitySchemes.bearerAuth.scheme', 'bearer')
            ->assertJsonPath('components.securitySchemes.bearerAuth.bearerFormat', 'JWT');

        foreach ($response->json('paths') as $path => $operations) {
            foreach ($operations as $method => $operation) {
                $this->assertSame([['bearerAuth' => []]], $operation['security'] ?? null, "{$method} {$path}");
            }
        }
    }

    public function test_document_describes_product_schema(): void
    {
        $this->fetchDocument()->assertJsonStructure([
            'components' => [
                'schemas' => [
                    'Product' => [
                        'properties' => ['id', 'name', 'description', 'status', 'created_at', 'updated_at'],
                    ],
                ],
            ],
        ]);
    }
}
