<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

/**
 * Errore di dominio atteso, che si rende da sé come `{message}` con il proprio status.
 * Non finisce nei log perché non indica un guasto del servizio.
 */
abstract class CustomException extends Exception
{
    protected int $httpStatusCode = 400;

    public function render(): JsonResponse
    {
        return response()->json(['message' => $this->getMessage()], $this->httpStatusCode);
    }
}
