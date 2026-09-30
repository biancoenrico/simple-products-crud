<?php

namespace App\Http\Controllers\V1;

use App\DTO\ProductDto;
use App\Enums\ProductStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\IndexProductRequest;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Services\Contracts\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;

class ProductController extends Controller
{
    public function __construct(protected ProductService $productService) {}

    #[OA\Get(
        path: '/api/v1/products',
        operationId: 'getProducts',
        summary: 'Lista prodotti',
        security: [['bearerAuth' => []]],
        tags: ['Products'],
        parameters: [
            new OA\QueryParameter(name: 'page', required: false, schema: new OA\Schema(type: 'integer', minimum: 1)),
            new OA\QueryParameter(name: 'per-page', required: false, schema: new OA\Schema(type: 'integer', default: 15, maximum: 100, minimum: 1)),
            new OA\QueryParameter(name: 'name', description: 'Parte del nome, senza distinzione fra maiuscole e minuscole', required: false, schema: new OA\Schema(type: 'string', maxLength: 255)),
            new OA\QueryParameter(name: 'status', required: false, schema: new OA\Schema(type: 'string', enum: ProductStatus::class)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Pagina di prodotti, dal più recente', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Product')),
                new OA\Property(property: 'links', type: 'object'),
                new OA\Property(property: 'meta', type: 'object'),
            ])),
            new OA\Response(response: 401, description: 'Token assente o non valido'),
            new OA\Response(response: 422, description: 'Dati non validi'),
        ],
    )]
    public function index(IndexProductRequest $request): AnonymousResourceCollection
    {
        return ProductResource::collection(
            $this->productService->paginate($request->integer('per-page', 15)),
        );
    }

    #[OA\Get(
        path: '/api/v1/products/{productId}',
        operationId: 'getProduct',
        summary: 'Dettaglio prodotto',
        security: [['bearerAuth' => []]],
        tags: ['Products'],
        parameters: [new OA\PathParameter(name: 'productId', required: true, schema: new OA\Schema(type: 'string', format: 'uuid'))],
        responses: [
            new OA\Response(response: 200, description: 'Prodotto', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/Product')])),
            new OA\Response(response: 401, description: 'Token assente o non valido'),
            new OA\Response(response: 404, description: 'Prodotto non trovato'),
        ],
    )]
    public function show(string $productId): ProductResource
    {
        return new ProductResource($this->productService->findById($productId));
    }

    #[OA\Post(
        path: '/api/v1/products',
        operationId: 'createProduct',
        summary: 'Crea prodotto',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/ProductInput')),
        tags: ['Products'],
        responses: [
            new OA\Response(response: 201, description: 'Prodotto creato', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/Product')])),
            new OA\Response(response: 401, description: 'Token assente o non valido'),
            new OA\Response(response: 422, description: 'Dati non validi'),
        ],
    )]
    public function store(StoreProductRequest $request): JsonResponse
    {
        return (new ProductResource($this->productService->create(ProductDto::from($request->validated()))))
            ->response()
            ->setStatusCode(201);
    }

    #[OA\Put(
        path: '/api/v1/products/{productId}',
        operationId: 'updateProduct',
        summary: 'Aggiorna prodotto',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/ProductInput')),
        tags: ['Products'],
        parameters: [new OA\PathParameter(name: 'productId', required: true, schema: new OA\Schema(type: 'string', format: 'uuid'))],
        responses: [
            new OA\Response(response: 200, description: 'Prodotto aggiornato', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/Product')])),
            new OA\Response(response: 401, description: 'Token assente o non valido'),
            new OA\Response(response: 404, description: 'Prodotto non trovato'),
            new OA\Response(response: 422, description: 'Dati non validi'),
        ],
    )]
    public function update(UpdateProductRequest $request, string $productId): ProductResource
    {
        return new ProductResource($this->productService->updateById($productId, ProductDto::from($request->validated())));
    }

    #[OA\Delete(
        path: '/api/v1/products/{productId}',
        operationId: 'deleteProduct',
        summary: 'Elimina prodotto',
        security: [['bearerAuth' => []]],
        tags: ['Products'],
        parameters: [new OA\PathParameter(name: 'productId', required: true, schema: new OA\Schema(type: 'string', format: 'uuid'))],
        responses: [
            new OA\Response(response: 204, description: 'Prodotto eliminato'),
            new OA\Response(response: 401, description: 'Token assente o non valido'),
            new OA\Response(response: 404, description: 'Prodotto non trovato'),
        ],
    )]
    public function destroy(string $productId): Response
    {
        $this->productService->deleteById($productId);

        return response()->noContent();
    }
}
