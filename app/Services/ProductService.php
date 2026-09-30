<?php

namespace App\Services;

use App\Repositories\Contracts\ProductRepository;
use App\Services\Contracts\ProductService as ProductServiceContract;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Override;

class ProductService implements ProductServiceContract
{
    public function __construct(protected ProductRepository $repository) {}

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function paginate(int $perPage): LengthAwarePaginator
    {
        return $this->repository->paginate($perPage);
    }
}
