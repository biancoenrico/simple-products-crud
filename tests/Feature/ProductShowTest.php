<?php

namespace Tests\Feature;

use App\Exceptions\ProductException;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Str;
use Tests\Concerns\InteractsWithJwt;
use Tests\TestCase;

class ProductShowTest extends TestCase
{
    use InteractsWithJwt;
    use RefreshDatabase;

    public function test_show_returns_requested_product(): void
    {
        Product::factory()->create();
        $product = Product::factory()->inactive()->create([
            'name' => 'Lampada',
            'description' => 'Da tavolo',
            'created_at' => Carbon::parse('2026-09-01 10:00:00'),
            'updated_at' => Carbon::parse('2026-09-02 11:30:00'),
        ]);

        $response = $this->authenticated()->getJson(route('products.show', $product->id));

        $response->assertOk()
            ->assertExactJson(['data' => [
                'id' => $product->id,
                'name' => 'Lampada',
                'description' => 'Da tavolo',
                'status' => 'inactive',
                'created_at' => '2026-09-01T10:00:00+00:00',
                'updated_at' => '2026-09-02T11:30:00+00:00',
            ]]);
    }

    public function test_show_missing_product_is_not_found_with_italian_message(): void
    {
        $productId = (string) Str::uuid7();

        $response = $this->authenticated()->getJson(route('products.show', $productId));

        $response->assertNotFound()
            ->assertExactJson(['message' => "Prodotto {$productId} non trovato."]);
    }

    public function test_show_non_uuid_id_is_not_found(): void
    {
        $response = $this->authenticated()->getJson(route('products.show', 'not-a-uuid'));

        $response->assertNotFound()
            ->assertExactJson(['message' => 'Risorsa non trovata.']);
    }

    public function test_show_missing_product_is_not_reported(): void
    {
        Exceptions::fake();

        $this->authenticated()->getJson(route('products.show', (string) Str::uuid7()))->assertNotFound();

        Exceptions::assertNotReported(ProductException::class);
    }

    public function test_show_requires_bearer_token(): void
    {
        $product = Product::factory()->create();

        $this->getJson(route('products.show', $product->id))->assertUnauthorized();
    }
}
