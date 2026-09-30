<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\InteractsWithJwt;
use Tests\TestCase;

class ProductStoreTest extends TestCase
{
    use InteractsWithJwt;
    use RefreshDatabase;

    public function test_store_creates_product_with_generated_uuid(): void
    {
        $response = $this->authenticated()->postJson(route('products.store'), [
            'name' => 'Lampada',
            'description' => 'Da tavolo',
            'status' => 'inactive',
        ]);

        $response->assertCreated()
            ->assertJsonStructure(['data' => ['id', 'name', 'description', 'status', 'created_at', 'updated_at']])
            ->assertJsonPath('data.name', 'Lampada')
            ->assertJsonPath('data.description', 'Da tavolo')
            ->assertJsonPath('data.status', 'inactive');

        $productId = $response->json('data.id');
        $this->assertTrue(Str::isUuid($productId));
        $this->assertDatabaseHas('products', [
            'id' => $productId,
            'name' => 'Lampada',
            'description' => 'Da tavolo',
            'status' => 'inactive',
        ]);
    }

    public function test_store_defaults_status_to_active(): void
    {
        $response = $this->authenticated()->postJson(route('products.store'), [
            'name' => 'Lampada',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.status', 'active')
            ->assertJsonPath('data.description', null);
        $this->assertDatabaseHas('products', ['id' => $response->json('data.id'), 'status' => 'active']);
    }

    /**
     * @return array<string, array{array<string, mixed>, string}>
     */
    public static function invalidPayloads(): array
    {
        return [
            'name missing' => [['description' => 'Da tavolo'], 'name'],
            'name too long' => [['name' => str_repeat('a', 256)], 'name'],
            'description not a string' => [['name' => 'Lampada', 'description' => ['a']], 'description'],
            'status out of enum' => [['name' => 'Lampada', 'status' => 'archived'], 'status'],
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    #[DataProvider('invalidPayloads')]
    public function test_store_rejects_invalid_payload(array $payload, string $invalidField): void
    {
        $response = $this->authenticated()->postJson(route('products.store'), $payload);

        $response->assertUnprocessable()->assertOnlyJsonValidationErrors($invalidField);
        $this->assertDatabaseCount('products', 0);
    }

    public function test_store_accepts_name_of_maximum_length(): void
    {
        $this->authenticated()
            ->postJson(route('products.store'), ['name' => str_repeat('a', 255)])
            ->assertCreated();
    }

    public function test_store_keeps_laravel_english_validation_messages(): void
    {
        $this->authenticated()
            ->postJson(route('products.store'), [])
            ->assertJsonValidationErrors(['name' => 'The name field is required.']);
    }

    public function test_store_requires_bearer_token(): void
    {
        $this->postJson(route('products.store'), ['name' => 'Lampada'])->assertUnauthorized();
        $this->assertDatabaseCount('products', 0);
    }
}
