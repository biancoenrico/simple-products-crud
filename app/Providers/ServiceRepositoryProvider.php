<?php

namespace App\Providers;

use App\Repositories\Contracts\ProductRepository as ProductRepositoryContract;
use App\Repositories\ProductRepository;
use App\Services\Contracts\ProductService as ProductServiceContract;
use App\Services\ProductService;
use Illuminate\Support\ServiceProvider;

/**
 * Lega le interfacce di service e repository alle loro implementazioni.
 */
class ServiceRepositoryProvider extends ServiceProvider
{
    /**
     * @var array<class-string, class-string>
     */
    public array $singletons = [
        ProductRepositoryContract::class => ProductRepository::class,
        ProductServiceContract::class => ProductService::class,
    ];
}
