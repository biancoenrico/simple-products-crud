<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\InteractsWithJwt;
use Tests\TestCase;

class ProductIndexTest extends TestCase
{
    use InteractsWithJwt;
    use RefreshDatabase;

    public function test_index_returns_products_with_public_fields_only(): void
    {
        $product = Product::factory()->inactive()->create([
            'name' => 'Lampada',
            'description' => 'Da tavolo',
            'created_at' => Carbon::parse('2026-09-01 10:00:00'),
            'updated_at' => Carbon::parse('2026-09-02 11:30:00'),
        ]);

        $response = $this->authenticated()->getJson(route('products.index'));

        $response->assertOk()
            ->assertJsonStructure(['data', 'links', 'meta'])
            ->assertJsonPath('data', [[
                'id' => $product->id,
                'name' => 'Lampada',
                'description' => 'Da tavolo',
                'status' => 'inactive',
                'created_at' => '2026-09-01T10:00:00+00:00',
                'updated_at' => '2026-09-02T11:30:00+00:00',
            ]]);
    }

    public function test_index_lists_newest_products_first(): void
    {
        $middle = Product::factory()->create(['created_at' => now()->subDay()]);
        $oldest = Product::factory()->create(['created_at' => now()->subDays(2)]);
        $newest = Product::factory()->create(['created_at' => now()]);

        $response = $this->authenticated()->getJson(route('products.index'));

        $response->assertOk()
            ->assertJsonPath('data.*.id', [$newest->id, $middle->id, $oldest->id]);
    }

    public function test_index_paginates_fifteen_products_by_default(): void
    {
        Product::factory()->count(16)->create();

        $response = $this->authenticated()->getJson(route('products.index'));

        $response->assertOk()
            ->assertJsonCount(15, 'data')
            ->assertJsonPath('meta.per_page', 15)
            ->assertJsonPath('meta.total', 16);
    }

    public function test_index_honours_per_page(): void
    {
        Product::factory()->count(3)->create();

        $response = $this->authenticated()->getJson(route('products.index', ['per-page' => 2]));

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.per_page', 2);
    }

    /**
     * @return array<string, array{mixed}>
     */
    public static function invalidPerPageValues(): array
    {
        return [
            'zero' => [0],
            'above maximum' => [101],
            'not an integer' => ['abc'],
            'decimal' => ['1.5'],
        ];
    }

    #[DataProvider('invalidPerPageValues')]
    public function test_index_rejects_invalid_per_page(mixed $perPage): void
    {
        $response = $this->authenticated()->getJson(route('products.index', ['per-page' => $perPage]));

        $response->assertUnprocessable()->assertJsonValidationErrors('per-page');
    }

    public function test_index_accepts_per_page_bounds(): void
    {
        $this->authenticated()->getJson(route('products.index', ['per-page' => 1]))->assertOk()->assertJsonPath('meta.per_page', 1);
        $this->authenticated()->getJson(route('products.index', ['per-page' => 100]))->assertOk()->assertJsonPath('meta.per_page', 100);
    }
}
