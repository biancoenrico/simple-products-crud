<?php

namespace App\Repositories\Contracts;

use App\DTO\ProductDto;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProductRepository
{
    /**
     * @return LengthAwarePaginator<int, Product>
     */
    public function paginate(int $perPage): LengthAwarePaginator;

    public function findById(string $productId): ?Product;

    public function create(ProductDto $dto): Product;
}
