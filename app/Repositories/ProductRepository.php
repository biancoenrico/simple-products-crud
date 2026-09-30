<?php

namespace App\Repositories;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepository as ProductRepositoryContract;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Override;

class ProductRepository implements ProductRepositoryContract
{
    /**
     * {@inheritdoc}
     */
    #[Override]
    public function paginate(int $perPage): LengthAwarePaginator
    {
        return Product::query()
            ->latest()
            ->latest('id')
            ->paginate($perPage);
    }
}
