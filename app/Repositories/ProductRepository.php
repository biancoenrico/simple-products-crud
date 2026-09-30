<?php

namespace App\Repositories;

use App\DTO\ProductDto;
use App\Filters\ByName;
use App\Filters\ByStatus;
use App\Models\Product;
use App\Repositories\Contracts\ProductRepository as ProductRepositoryContract;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Pipeline;
use Override;

class ProductRepository implements ProductRepositoryContract
{
    /**
     * {@inheritdoc}
     */
    #[Override]
    public function paginate(int $perPage): LengthAwarePaginator
    {
        return Pipeline::send(Product::query())
            ->through([
                ByName::class,
                ByStatus::class,
            ])
            ->thenReturn()
            ->latest()
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function findById(string $productId): ?Product
    {
        return Product::query()->find($productId);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function create(ProductDto $dto): Product
    {
        return Product::query()->create($dto->toArray());
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function update(Product $product, ProductDto $dto): Product
    {
        $product->update($dto->toArray());

        return $product;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function delete(Product $product): void
    {
        $product->delete();
    }
}
