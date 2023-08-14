<?php

use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;
use App\Providers\AuthorizationProvider;
use Carbon\Carbon;

class TokenTest extends TestCase
{
    private array $accessKeyProperties;
    private string $timeStamp;

    protected function setUp(): void
    {
        parent::setUp();

        $this->timeStamp = Carbon::now()->utc()->toIso8601String();

        try {
            $this->accessKeyProperties = AuthorizationProvider::getTokenProperties('tests');
        } catch (Exception $exception) {
            $this->fail($exception->getMessage());
        }
    }

    public function test_sign_token(): string
    {
        try {
            $timeStamp = Carbon::now()->utc()->toIso8601String();

            $signature = AuthorizationProvider::signToken(
                appKey: $this->accessKeyProperties['appKey'],
                appSecret: $this->accessKeyProperties['appKey'],
                timeStamp: $timeStamp,
            );

            $this->assertIsString($signature);

            return $signature;
        } catch (Exception $exception) {
            $this->fail($exception->getMessage());
        }
    }

    public function test_sign_token_invalid_hash_alg()
    {
        $this->expectException('ValueError');

        config(['laravel-mail-api.hashSignature' => 'sha-256']);

        $timeStamp = Carbon::now()->utc()->toIso8601String();

        AuthorizationProvider::signToken(
            appKey: $this->accessKeyProperties['appKey'],
            appSecret: $this->accessKeyProperties['appKey'],
            timeStamp: $timeStamp,
        );
    }

    public function test_token_time_is_valid()
    {
        try {
            AuthorizationProvider::checkTokenExpired($this->timeStamp);

            $this->expectNotToPerformAssertions();
        } catch (Exception $exception) {
            $this->fail($exception->getMessage());
        }
    }

    public function test_token_time_is_expired()
    {
        $this->expectExceptionMessage('Token expired.');

        $tokenTime = config('laravel-mail-api.tokenTime');
        $this->travel($tokenTime + 1)->minutes();

        AuthorizationProvider::checkTokenExpired($this->timeStamp);
    }

    #[TestWith(["tests"])]
    #[TestWith(["tests-x-not-exists"])]
    public function test_get_token_properties(string $accessKey)
    {
        try {
            $tokenProperties = AuthorizationProvider::getTokenProperties($accessKey);

            $this->assertArrayHasKey('appKey', $tokenProperties);
            $this->assertArrayHasKey('appSecret', $tokenProperties);
        } catch (Exception $exception) {
            $this->assertEquals('Invalid access token.', $exception->getMessage());
        }
    }

    #[Testwith(["tests", "2023-08-09T04:37:04.153Z", "2023-08-09T04:37:04.153Z"])]
    #[Testwith(["tests", "2023-08-09T04:37:04.153Z"])]
    public function test_check_signature(string $accessKey, string $timeStampCheck, ?string $timeStamp = null)
    {
        $timeStamp = ! is_null($timeStamp)
            ? $timeStamp
            : $this->timeStamp;

        $signature = $this->createSignedToken($accessKey, $timeStamp);

        try {
            AuthorizationProvider::checkSignature($accessKey, $signature, $timeStampCheck);

            $this->expectNotToPerformAssertions();
        } catch (Exception $exception) {
            $this->assertEquals('Invalid token.', $exception->getMessage());
        }
    }

    private function createSignedToken(string $accessKey, string $timeStamp): string
    {
        try {
            $accessToken = AuthorizationProvider::getTokenProperties($accessKey);

            return AuthorizationProvider::signToken(
                appKey: $accessToken['appKey'],
                appSecret: $accessToken['appSecret'],
                timeStamp: $timeStamp,
            );
        } catch (Exception $exception) {
            $this->fail($exception->getMessage());
        }
    }
}
