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

    public function test_index_filters_by_part_of_the_name(): void
    {
        $lamp = Product::factory()->create(['name' => 'Lampada da tavolo']);
        Product::factory()->create(['name' => 'Sedia']);

        $response = $this->authenticated()->getJson(route('products.index', ['name' => 'DA TAV']));

        $response->assertOk()->assertJsonPath('data.*.id', [$lamp->id]);
    }

    /**
     * @return array<string, array{string, string, string}>
     */
    public static function likeWildcardSearches(): array
    {
        return [
            'percent sign' => ['100%', 'Maglia 100% cotone', 'Lotto 1000 pezzi'],
            'underscore' => ['a_b', 'Codice a_b', 'Codice axb'],
            'escape character' => ['!', 'Offerta!', 'Sconto 50%'],
        ];
    }

    #[DataProvider('likeWildcardSearches')]
    public function test_index_name_filter_treats_like_wildcards_as_plain_characters(string $search, string $matchingName, string $otherName): void
    {
        $matching = Product::factory()->create(['name' => $matchingName]);
        Product::factory()->create(['name' => $otherName]);

        $response = $this->authenticated()->getJson(route('products.index', ['name' => $search]));

        $response->assertOk()->assertJsonPath('data.*.id', [$matching->id]);
    }

    public function test_index_filters_by_status(): void
    {
        Product::factory()->create();
        $inactive = Product::factory()->inactive()->create();

        $response = $this->authenticated()->getJson(route('products.index', ['status' => 'inactive']));

        $response->assertOk()->assertJsonPath('data.*.id', [$inactive->id]);
    }

    public function test_index_applies_name_and_status_filters_together(): void
    {
        Product::factory()->create(['name' => 'Lampada']);
        $inactiveLamp = Product::factory()->inactive()->create(['name' => 'Lampada']);
        Product::factory()->inactive()->create(['name' => 'Sedia']);

        $response = $this->authenticated()->getJson(route('products.index', ['name' => 'Lamp', 'status' => 'inactive']));

        $response->assertOk()->assertJsonPath('data.*.id', [$inactiveLamp->id]);
    }

    public function test_index_ignores_empty_filters(): void
    {
        Product::factory()->create();
        Product::factory()->inactive()->create();

        $response = $this->authenticated()->getJson(route('products.index', ['name' => '', 'status' => '']));

        $response->assertOk()->assertJsonPath('meta.total', 2);
    }

    public function test_index_pagination_links_keep_the_filters(): void
    {
        Product::factory()->count(2)->inactive()->create();

        $response = $this->authenticated()->getJson(route('products.index', ['status' => 'inactive', 'per-page' => 1]));

        $response->assertOk();
        $this->assertStringContainsString('status=inactive', $response->json('links.next'));
    }

    /**
     * @return array<string, array{string, mixed}>
     */
    public static function invalidFilterValues(): array
    {
        return [
            'unknown status' => ['status', 'deleted'],
            'name too long' => ['name', str_repeat('a', 256)],
            'name not a string' => ['name', ['Lampada']],
        ];
    }

    #[DataProvider('invalidFilterValues')]
    public function test_index_rejects_invalid_filters(string $field, mixed $value): void
    {
        $response = $this->authenticated()->getJson(route('products.index', [$field => $value]));

        $response->assertUnprocessable()->assertJsonValidationErrors($field);
    }
}
