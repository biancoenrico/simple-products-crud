<?php

namespace Tests\Concerns;

use Firebase\JWT\JWT;
use OpenSSLAsymmetricKey;

/**
 * Firma token come quelli di AG con una coppia RSA generata una volta per esecuzione;
 * la chiave pubblica finisce in un file temporaneo a cui punta la configurazione.
 */
trait InteractsWithJwt
{
    private static ?OpenSSLAsymmetricKey $jwtPrivateKey = null;

    private static ?string $jwtPublicKeyPath = null;

    protected function setUpInteractsWithJwt(): void
    {
        if (self::$jwtPrivateKey === null) {
            self::$jwtPrivateKey = $this->generateRsaKey();
            self::$jwtPublicKeyPath = tempnam(sys_get_temp_dir(), 'jwt');
            file_put_contents(self::$jwtPublicKeyPath, openssl_pkey_get_details(self::$jwtPrivateKey)['key']);
        }

        config(['jwt.public_key_path' => self::$jwtPublicKeyPath]);
    }

    /**
     * @param  array<string, mixed>  $userData
     */
    protected function authenticated(array $userData = []): static
    {
        return $this->withToken($this->validJwt($userData));
    }

    /**
     * @param  array<string, mixed>  $userData
     */
    protected function validJwt(array $userData = []): string
    {
        return $this->signJwt(['data' => base64_encode(json_encode($userData))]);
    }

    protected function expiredJwt(): string
    {
        return $this->signJwt(['data' => base64_encode('{}'), 'exp' => time() - 60]);
    }

    protected function jwtSignedByAnotherKey(): string
    {
        return $this->signJwt(['data' => base64_encode('{}')], $this->generateRsaKey());
    }

    protected function jwtWithInvalidData(?string $data): string
    {
        return $this->signJwt($data === null ? [] : ['data' => $data]);
    }

    /**
     * @param  array<string, mixed>  $claims
     */
    private function signJwt(array $claims, ?OpenSSLAsymmetricKey $privateKey = null): string
    {
        $now = time();

        return JWT::encode([
            'iss' => 'http://ag-backend',
            'aud' => 'http://ag-backend',
            'iat' => $now,
            'exp' => $now + 3600,
            ...$claims,
        ], $privateKey ?? self::$jwtPrivateKey, 'RS256');
    }

    private function generateRsaKey(): OpenSSLAsymmetricKey
    {
        return openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
    }
}
