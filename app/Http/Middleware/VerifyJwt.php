<?php

namespace App\Http\Middleware;

use Closure;
use DomainException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;
use UnexpectedValueException;

/**
 * Verifica il JWT RS256 emesso da AG e salva il claim `data` decodificato, permessi
 * compresi, in `config('user_data')`, dove li troverà il futuro controllo dei permessi.
 */
class VerifyJwt
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (! $token) {
            abort(401, __('middleware.unauthorized'));
        }

        $publicKeyPath = config('jwt.public_key_path');
        $publicKey = is_readable($publicKeyPath) ? file_get_contents($publicKeyPath) : false;

        if (! $publicKey) {
            abort(500, __('middleware.public_key_missing'));
        }

        try {
            $claims = JWT::decode($token, new Key($publicKey, 'RS256'));
        } catch (DomainException|InvalidArgumentException|UnexpectedValueException) {
            abort(401, __('middleware.unauthorized'));
        }

        $userData = is_string($claims->data ?? null)
            ? json_decode((string) base64_decode($claims->data, true), true)
            : null;

        if (! is_array($userData)) {
            abort(401, __('middleware.unauthorized'));
        }

        config(['user_data' => $userData]);

        return $next($request);
    }
}
