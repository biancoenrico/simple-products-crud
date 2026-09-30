<?php

namespace App\Exceptions;

class ProductException extends CustomException
{
    public static function productNotFound(string $productId): static
    {
        $exception = new static(__('exceptions.product_not_found', ['productId' => $productId]));
        $exception->httpStatusCode = 404;

        return $exception;
    }
}
