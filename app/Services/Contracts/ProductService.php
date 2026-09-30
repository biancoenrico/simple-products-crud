<?php

namespace App\Services\Contracts;

use App\DTO\ProductDto;
use App\Exceptions\ProductException;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProductService
{
    /**
     * @param  int  $perPage
     * @return LengthAwarePaginator<int, Product>
     */
    public function paginate(int $perPage): LengthAwarePaginator;

    /**
     * @param  string  $productId
     * @return Product
     *
     * @throws ProductException
     */
    public function findById(string $productId): Product;

    /**
     * @param  ProductDto  $dto
     * @return Product
     */
    public function create(ProductDto $dto): Product;

    /**
     * @param  string  $productId
     * @param  ProductDto  $dto
     * @return Product
     *
     * @throws ProductException
     */
    public function updateById(string $productId, ProductDto $dto): Product;

    /**
     * @param  string  $productId
     * @return void
     *
     * @throws ProductException
     */
    public function deleteById(string $productId): void;
}
