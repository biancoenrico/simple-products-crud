<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    description: 'Microservizio operativo per la gestione prodotti',
    title: 'MSP Prodotto',
)]
#[OA\Server(url: \L5_SWAGGER_CONST_HOST)]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    description: 'JWT emesso da AG, firmato RS256',
    bearerFormat: 'JWT',
    scheme: 'bearer',
)]
#[OA\Tag(name: 'Products', description: 'Gestione prodotti')]
abstract class Controller
{
    //
}
