<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Concerns\InteractsWithJwt;
use Tests\TestCase;

class ProductDestroyTest extends TestCase
{
    use InteractsWithJwt;
    use RefreshDatabase;

    public function test_destroy_soft_deletes_product_with_empty_response(): void
    {
        $product = Product::factory()->create();
        $untouched = Product::factory()->create();

        $response = $this->authenticated()->deleteJson(route('products.destroy', $product->id));

        $response->assertNoContent();
        $this->assertSoftDeleted($product);
        $this->assertNotSoftDeleted($untouched);
    }

    public function test_destroy_hides_product_from_list_and_detail(): void
    {
        $product = Product::factory()->create();
        $remaining = Product::factory()->create();

        $this->authenticated()->deleteJson(route('products.destroy', $product->id))->assertNoContent();

        $this->authenticated()->getJson(route('products.index'))
            ->assertOk()
            ->assertJsonPath('data.*.id', [$remaining->id]);
        $this->authenticated()->getJson(route('products.show', $product->id))->assertNotFound();
    }

    public function test_destroy_twice_is_not_found_the_second_time(): void
    {
        $product = Product::factory()->create();

        $this->authenticated()->deleteJson(route('products.destroy', $product->id))->assertNoContent();

        $this->authenticated()->deleteJson(route('products.destroy', $product->id))
            ->assertNotFound()
            ->assertExactJson(['message' => "Prodotto {$product->id} non trovato."]);
    }

    public function test_destroy_missing_product_is_not_found(): void
    {
        $this->authenticated()->deleteJson(route('products.destroy', (string) Str::uuid7()))->assertNotFound();
    }

    public function test_destroy_non_uuid_id_is_not_found(): void
    {
        $this->authenticated()
            ->deleteJson(route('products.destroy', 'not-a-uuid'))
            ->assertNotFound()
            ->assertJsonPath('message', 'Risorsa non trovata.');
    }

    public function test_destroy_requires_bearer_token(): void
    {
        $product = Product::factory()->create();

        $this->deleteJson(route('products.destroy', $product->id))->assertUnauthorized();
        $this->assertNotSoftDeleted($product);
    }
}
