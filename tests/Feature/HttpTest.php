<?php

namespace Tests\Feature;

use App\Jobs\EmailDispatcher;
use App\Jobs\FilesCleanup;
use App\Models\User;
use App\Providers\ApiAuthProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Mail;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;
use Illuminate\Support\Facades\Storage;
use Exception;

class HttpTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_token()
    {
        $pwd = '123456';
        $user = User::factory()->withPassword($pwd)->create();

        $this->withBasicAuth($user->email, $pwd)
            ->postJson('/api/token')
            ->assertStatus(201)
            ->assertJson(fn (AssertableJson $json) =>
                $json->has('token')
                    ->whereType('token', 'string')
                    ->etc()
            );
    }

    public function test_invalid_credentials()
    {
        $pwd = '123456';
        $user = User::factory()->withPassword($pwd)->create();

        $this->withBasicAuth($user->email, 'prone')
            ->postJson('/api/token')
            ->assertStatus(500)
            ->assertJson(fn (AssertableJson $json) =>
            $json->has('error', fn (AssertableJson $json) =>
                $json->where('message', 'Invalid credentials.')
                ->etc()
            )->etc());
    }

    public function test_send_email()
    {
        Queue::fake([
            EmailDispatcher::class,
            FilesCleanup::class,
        ]);

        Mail::fake();

        $user = User::factory()->create();

        Storage::fake('/api/files');

        try {
            $token = ApiAuthProvider::createToken($user);

            $this->withToken($token['token'])
                ->post('/api/send-message', [
                    'from' => fake()->email,
                    'to' => fake()->email,
                    'sender' => fake()->name,
                    'receiver' => fake()->name,
                    'subject' => fake()->jobTitle,
                    'language' => 'en',
                    'template' => 'password',
                    'attachments[]' => UploadedFile::fake()->image('photo1.jpg'),
                ])
                ->assertStatus(200)
                ->assertJson(fn (AssertableJson $json) =>
                    $json->has('data', fn (AssertableJson $data) =>
                            $data->whereType('message', 'string')
                            ->where('message', 'the message will be sent!')
                            ->etc()
                        )
                        ->etc()
                );

        } catch (Exception $exception) {
            $this->fail($exception);
        }
    }

    public function test_fail_token_expired()
    {
        Queue::fake([
            EmailDispatcher::class,
            FilesCleanup::class,
        ]);

        Mail::fake();

        $user = User::factory()->create();

        try {
            $token = ApiAuthProvider::createToken($user);
            $tokenTime = (int) config('laravel-mail-api.token-time');

            $this->travel($tokenTime + 1)->minutes();

            $this->makeRequest(endpoint: '/api/send-message', token: $token['token'])
                ->assertStatus(500)
                ->assertJson(fn (AssertableJson $json) =>
                    $json->has('error', fn (AssertableJson $json) =>
                        $json->where('message', 'Token expired.')
                        ->etc()
                    )->etc()
                );

        } catch (Exception $exception) {
            $this->fail($exception->getMessage());
        }
    }

    public function test_fail_token_invalid()
    {
        Queue::fake([
            EmailDispatcher::class,
            FilesCleanup::class,
        ]);

        Mail::fake();

        $user = User::factory()->create();

        try {
            $token = ApiAuthProvider::createToken($user);

            // regenerates token, so stored gets invalid
            ApiAuthProvider::createToken($user);

            $this->makeRequest('/api/send-message', $token['token'])
                ->assertStatus(500)
                ->assertJson(fn (AssertableJson $json) =>
                    $json->has('error', fn (AssertableJson $json) =>
                        $json->where('message', 'Invalid token.')
                        ->etc()
                    )->etc()
                );

        } catch (Exception $exception) {
            $this->fail($exception->getMessage());
        }
    }

    private function makeRequest($endpoint, $token)
    {
        return $this->withToken($token)
            ->post($endpoint, [
                'from' => fake()->email,
                'to' => fake()->email,
            ]);
    }
}
