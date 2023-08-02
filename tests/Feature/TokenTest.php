<?php

namespace Tests\Feature;

use App\Models\User;
use App\Providers\ApiAuthProvider;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TokenTest extends TestCase
{
    use RefreshDatabase;

    public function test_should_issue_token()
    {
        $user = User::factory()->create();

        try {
            $token = ApiAuthProvider::createToken($user);

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
            $token = ApiAuthProvider::createToken($user);

            $storedToken = ApiAuthProvider::findToken($token['token']);

            $this->assertNotNull($storedToken);

        } catch (Exception $exception) {
            $this->fail($exception->getMessage());
        }
    }
}
