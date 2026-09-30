<?php

namespace App\Services;

use App\DTO\ProductDto;
use App\Exceptions\ProductException;
use App\Models\Product;
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

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function findById(string $productId): Product
    {
        return $this->repository->findById($productId)
            ?? throw ProductException::productNotFound($productId);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function create(ProductDto $dto): Product
    {
        return $this->repository->create($dto);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function updateById(string $productId, ProductDto $dto): Product
    {
        return $this->repository->update($this->findById($productId), $dto);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function deleteById(string $productId): void
    {
        $this->repository->delete($this->findById($productId));
    }
}
