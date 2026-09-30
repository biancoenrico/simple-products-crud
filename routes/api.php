<?php

use App\Http\Controllers\V1\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('health', fn () => ['status' => 'ok'])->name('health');

Route::prefix('v1')->middleware('jwt')->group(function () {
    Route::prefix('products')->name('products.')->controller(ProductController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::get('{productId}', 'show')->name('show')->whereUuid('productId');
    });
});
