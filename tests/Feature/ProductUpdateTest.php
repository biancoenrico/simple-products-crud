<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\InteractsWithJwt;
use Tests\TestCase;

class ProductUpdateTest extends TestCase
{
    use InteractsWithJwt;
    use RefreshDatabase;

    public function test_update_replaces_product_and_returns_it(): void
    {
        $product = Product::factory()->create(['name' => 'Lampada', 'description' => 'Da tavolo']);

        $response = $this->authenticated()->putJson(route('products.update', $product->id), [
            'name' => 'Lampada LED',
            'description' => 'Da parete',
            'status' => 'inactive',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.id', $product->id)
            ->assertJsonPath('data.name', 'Lampada LED')
            ->assertJsonPath('data.description', 'Da parete')
            ->assertJsonPath('data.status', 'inactive');
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Lampada LED',
            'description' => 'Da parete',
            'status' => 'inactive',
        ]);
    }

    public function test_update_without_status_or_description_resets_them(): void
    {
        $product = Product::factory()->inactive()->create(['description' => 'Da tavolo']);

        $this->authenticated()
            ->putJson(route('products.update', $product->id), ['name' => 'Lampada'])
            ->assertOk()
            ->assertJsonPath('data.status', 'active')
            ->assertJsonPath('data.description', null);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'status' => 'active', 'description' => null]);
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
    public function test_update_rejects_invalid_payload(array $payload, string $invalidField): void
    {
        $product = Product::factory()->create(['name' => 'Lampada']);

        $response = $this->authenticated()->putJson(route('products.update', $product->id), $payload);

        $response->assertUnprocessable()->assertOnlyJsonValidationErrors($invalidField);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'name' => 'Lampada']);
    }

    public function test_update_missing_product_is_not_found_with_italian_message(): void
    {
        $productId = (string) Str::uuid7();

        $this->authenticated()
            ->putJson(route('products.update', $productId), ['name' => 'Lampada'])
            ->assertNotFound()
            ->assertExactJson(['message' => "Prodotto {$productId} non trovato."]);
    }

    public function test_update_non_uuid_id_is_not_found(): void
    {
        $this->authenticated()
            ->putJson(route('products.update', 'not-a-uuid'), ['name' => 'Lampada'])
            ->assertNotFound()
            ->assertJsonPath('message', 'Risorsa non trovata.');
    }

    public function test_update_deleted_product_is_not_found(): void
    {
        $product = Product::factory()->create(['name' => 'Lampada']);
        $product->delete();

        $this->authenticated()
            ->putJson(route('products.update', $product->id), ['name' => 'Lampada LED'])
            ->assertNotFound();
        $this->assertSoftDeleted('products', ['id' => $product->id, 'name' => 'Lampada']);
    }

    public function test_update_requires_bearer_token(): void
    {
        $product = Product::factory()->create(['name' => 'Lampada']);

        $this->putJson(route('products.update', $product->id), ['name' => 'Lampada LED'])->assertUnauthorized();
        $this->assertDatabaseHas('products', ['id' => $product->id, 'name' => 'Lampada']);
    }
}
