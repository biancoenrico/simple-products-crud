<?php

namespace App\Repositories\Contracts;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProductRepository
{
    /**
     * @return LengthAwarePaginator<int, Product>
     */
    public function paginate(int $perPage): LengthAwarePaginator;
}
