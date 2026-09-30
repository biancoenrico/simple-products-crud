<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Chiave pubblica di AG
    |--------------------------------------------------------------------------
    |
    | Percorso della chiave pubblica RSA con cui AG firma i JWT (RS256).
    | Va copiata dall'istanza AG: il servizio non ne genera una propria.
    |
    */

    'public_key_path' => env('JWT_PUBLIC_KEY_PATH', storage_path('oauth-public.key')),

];
