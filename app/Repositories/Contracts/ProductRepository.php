<?php

namespace App\Repositories\Contracts;

use App\DTO\ProductDto;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProductRepository
{
    /**
     * @param  int  $perPage
     * @return LengthAwarePaginator<int, Product>
     */
    public function paginate(int $perPage): LengthAwarePaginator;

    /**
     * @param  string  $productId
     * @return Product|null
     */
    public function findById(string $productId): ?Product;

    /**
     * @param  ProductDto  $dto
     * @return Product
     */
    public function create(ProductDto $dto): Product;

    /**
     * @param  Product  $product
     * @param  ProductDto  $dto
     * @return Product
     */
    public function update(Product $product, ProductDto $dto): Product;

    /**
     * @param  Product  $product
     * @return void
     */
    public function delete(Product $product): void;
}
