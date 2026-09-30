<?php

namespace App\Services\Contracts;

use App\DTO\ProductDto;
use App\Exceptions\ProductException;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProductService
{
    /**
     * @return LengthAwarePaginator<int, Product>
     */
    public function paginate(int $perPage): LengthAwarePaginator;

    /**
     * @throws ProductException
     */
    public function findById(string $productId): Product;

    public function create(ProductDto $dto): Product;
}
