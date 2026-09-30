<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\InteractsWithJwt;
use Tests\TestCase;

class JwtMiddlewareTest extends TestCase
{
    use InteractsWithJwt;
    use RefreshDatabase;

    public function test_request_without_bearer_token_is_unauthorized(): void
    {
        $response = $this->getJson(route('products.index'));

        $response->assertUnauthorized()->assertJsonStructure(['message']);
    }

    public function test_token_signed_by_another_key_is_unauthorized(): void
    {
        $response = $this->withToken($this->jwtSignedByAnotherKey())->getJson(route('products.index'));

        $response->assertUnauthorized()->assertJsonStructure(['message']);
    }

    public function test_expired_token_is_unauthorized(): void
    {
        $response = $this->withToken($this->expiredJwt())->getJson(route('products.index'));

        $response->assertUnauthorized()->assertJsonStructure(['message']);
    }

    /**
     * @return array<string, array{?string}>
     */
    public static function invalidDataClaims(): array
    {
        return [
            'missing' => [null],
            'not base64' => ['%%%'],
            'base64 of non-JSON' => [base64_encode('not json')],
            'base64 of JSON scalar' => [base64_encode('42')],
        ];
    }

    #[DataProvider('invalidDataClaims')]
    public function test_token_with_invalid_data_claim_is_unauthorized(?string $data): void
    {
        $response = $this->withToken($this->jwtWithInvalidData($data))->getJson(route('products.index'));

        $response->assertUnauthorized()->assertJsonStructure(['message']);
    }

    public function test_valid_token_exposes_decoded_data_claim_as_user_data(): void
    {
        $userData = [
            'user_id' => 1,
            'account_id' => 5,
            'org_id' => 2,
            'permissions' => ['MSF-file-index'],
            'source' => null,
        ];

        $response = $this->withToken($this->validJwt($userData))->getJson(route('products.index'));

        $response->assertOk();
        $this->assertSame($userData, config('user_data'));
    }

    public function test_missing_public_key_is_a_server_error_with_explicit_message(): void
    {
        config(['jwt.public_key_path' => sys_get_temp_dir().'/missing-oauth-public.key']);

        $response = $this->withToken($this->validJwt())->getJson(route('products.index'));

        $response->assertInternalServerError()
            ->assertJsonPath('message', 'Chiave pubblica JWT non trovata sul server.');
    }
}
