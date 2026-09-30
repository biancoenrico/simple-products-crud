<?php

namespace App\Http\Controllers\V1;

use App\DTO\ProductDto;
use App\Http\Controllers\Controller;
use App\Http\Requests\IndexProductRequest;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Services\Contracts\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ProductController extends Controller
{
    public function __construct(protected ProductService $productService) {}

    public function index(IndexProductRequest $request): AnonymousResourceCollection
    {
        return ProductResource::collection(
            $this->productService->paginate($request->integer('per_page', 15)),
        );
    }

    public function show(string $productId): ProductResource
    {
        return new ProductResource($this->productService->findById($productId));
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        return (new ProductResource($this->productService->create(ProductDto::from($request->validated()))))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateProductRequest $request, string $productId): ProductResource
    {
        return new ProductResource($this->productService->update($productId, ProductDto::from($request->validated())));
    }

    public function destroy(string $productId): Response
    {
        $this->productService->deleteById($productId);

        return response()->noContent();
    }
}
