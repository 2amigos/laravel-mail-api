<?php


use App\Models\User;
use App\Providers\AuthorizationProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TokenTest extends TestCase
{
    public function test_should_issue_token()
    {
        $user = User::factory()->create();

        try {
            $token = AuthorizationProvider::createToken($user);

            $this->assertIsArray($token);
            $this->assertArrayHasKey('token', $token);

        } catch (Exception $exception) {
            $this->fail($exception->getMessage());
        }
    }

    public function test_should_find_auth_by_token()
    {
        $user = User::factory()->create();

        try {
            $token = AuthorizationProvider::createToken($user);

            $storedToken = AuthorizationProvider::findToken($token['token']);

            $this->assertNotNull($storedToken);

        } catch (Exception $exception) {
            $this->fail($exception->getMessage());
        }
    }
}
