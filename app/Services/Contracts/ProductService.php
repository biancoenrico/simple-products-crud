<?php

namespace App\Services\Contracts;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProductService
{
    /**
     * @return LengthAwarePaginator<int, Product>
     */
    public function paginate(int $perPage): LengthAwarePaginator;
}
