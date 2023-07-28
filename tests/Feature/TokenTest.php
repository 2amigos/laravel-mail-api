<?php

namespace Tests\Feature;

use App\Models\User;
use App\Service\TokenService;
use Exception;
use Tests\TestCase;

class TokenTest extends TestCase
{
    public function test_should_issue_token()
    {
        $user = User::factory()->create();

        try {
            $token = TokenService::create($user);

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
            $token = TokenService::create($user);

            $storedToken = TokenService::findToken($token['token']);

            $this->assertNotNull($storedToken);

        } catch (Exception $exception) {
            $this->fail($exception->getMessage());
        }
    }
}
