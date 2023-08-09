<?php


use App\Jobs\EmailDispatcher;
use App\Jobs\FilesCleanup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class HttpTest extends TestCase
{
    use RefreshDatabase;

    public $token;

    protected function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub

        $pwd = '123456';
        $user = User::factory()->withPassword($pwd)->create();

        $this->token = \auth()->attempt([
            'email' => $user->email,
            'password' => $pwd,
        ]);
    }

    public function test_create_token()
    {
        $pwd = '123456';
        $user = User::factory()->withPassword($pwd)->create();

        $this->withBasicAuth($user->email, $pwd)
            ->postJson('/api/token/login')
            ->assertStatus(200)
            ->assertJson(
                fn (AssertableJson $json) =>
                $json->has('access_token')
                    ->whereAllType([
                        'access_token' => 'string',
                        'token_type' => 'string',
                        'expires_in'=> 'integer',
                    ])
                    ->etc()
            );
    }

    public function test_invalid_credentials()
    {
        $pwd = '123456';
        $user = User::factory()->withPassword($pwd)->create();

        $this->withBasicAuth($user->email, 'prone')
            ->postJson('/api/token/login')
            ->assertStatus(401)
            ->assertJson(fn (AssertableJson $json) =>
                $json->has('error')
                ->whereType('error', 'string')
                ->where('error', 'Unauthorized')
                ->etc()
            );
    }

    public function test_send_email()
    {
        Queue::fake([
            EmailDispatcher::class,
            FilesCleanup::class,
        ]);

        Mail::fake();

        Storage::fake('/api/files');

        try {
            $this->withToken($this->token)
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
                ->assertJson(
                    fn (AssertableJson $json) =>
                    $json->has(
                        'data',
                        fn (AssertableJson $data) =>
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
        $this->markTestSkipped('Skipping, unfinished implementation');

        Queue::fake([
            EmailDispatcher::class,
            FilesCleanup::class,
        ]);
        Mail::fake();

        try {
            $this->travel(\auth()->factory()->getTTL() * 120 * 120)->minutes();

            $this->makeRequest(endpoint: '/api/send-message', token: $this->token)
                ->assertStatus(401)
                ->assertJson(
                    fn (AssertableJson $json) =>
                    $json->has('error')
                        ->whereType('error', 'string')
                        ->where('error', 'Unauthorized')
                        ->etc()
                    );

        } catch (Exception $exception) {
            $this->fail($exception->getMessage());
        }
    }

    public function test_fail_token_invalid()
    {
        $this->markTestSkipped('Skipping, unfinished implementation');

        Queue::fake([
            EmailDispatcher::class,
            FilesCleanup::class,
        ]);

        Mail::fake();

        try {
            $this->makeRequest('/api/send-message', $this->token)
                ->assertStatus(401)
                ->assertJson(
                    fn (AssertableJson $json) =>
                    $json->has('error')
                        ->whereType('error', 'string')
                        ->where('error', 'Unauthorized')
                        ->etc()
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