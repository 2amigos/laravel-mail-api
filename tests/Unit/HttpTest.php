<?php


use App\Jobs\EmailDispatcher;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\Fluent\AssertableJson;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;
use App\Providers\AuthorizationProvider;
use Carbon\Carbon;

class HttpTest extends TestCase
{
    private string $accessKey = 'tests';
    private string $signature;
    private string $timeStamp;

    protected function setUp(): void
    {
        parent::setUp();

        $this->timeStamp = Carbon::now()->utc()->toIso8601String();

        try {
            $accessKeyProperties = AuthorizationProvider::getTokenProperties($this->accessKey);

            $this->signature = AuthorizationProvider::signToken(
                appKey: $accessKeyProperties['appKey'],
                appSecret: $accessKeyProperties['appSecret'],
                timeStamp: $this->timeStamp,
            );
        } catch (Exception $exception) {
            $this->fail($exception->getMessage());
        }
    }

    #[TestWith(["xxsdYusjdhgq1"])]
    #[TestWith(["uuYyioTwerdsDs"])]
    public function test_invalid_token(string $token)
    {
        $this->withHeaders([
                'accessKey' => $this->accessKey,
                'ts' => $this->timeStamp,
            ])
            ->withToken($token)
            ->postJson('/api/email/send')
            ->assertStatus(500)
            ->assertJson(
                fn (AssertableJson $json) =>
                $json->has(
                    'error',
                    fn (AssertableJson $error) =>
                    $error->whereType('message', 'string')
                        ->where('message', 'Invalid token.')
                        ->etc()
                )
                ->etc()
            );
    }

    #[TestWith(["tests-x-invalid"])]
    #[TestWith(["tests-x-invalid-xy"])]
    public function test_invalid_access_key(string $accessKey)
    {
        $this->withHeaders([
            'accessKey' => $accessKey,
            'ts' => $this->timeStamp,
        ])
            ->withToken('x')
            ->postJson('/api/email/send')
            ->assertStatus(500)
            ->assertJson(
                fn (AssertableJson $json) =>
            $json->has(
                'error',
                fn (AssertableJson $error) =>
            $error->whereType('message', 'string')
                ->where('message', 'Invalid access token.')
                ->etc()
            )
                ->etc()
            );
    }

    public function test_expired_token()
    {
        $tokenTime = config('laravel-mail-api.tokenTime');

        $this->travel($tokenTime + 1)->minutes();

        $this->withHeaders([
            'accessKey' => $this->accessKey,
            'ts' => $this->timeStamp,
        ])
            ->withToken($this->signature)
            ->postJson('/api/email/send')
            ->assertStatus(500)
            ->assertJson(
                fn (AssertableJson $json) =>
            $json->has(
                'error',
                fn (AssertableJson $error) =>
            $error->whereType('message', 'string')
                ->where('message', 'Token expired.')
                ->etc()
            )
                ->etc()
            );
    }

    public function test_send_email()
    {
        Queue::fake([
            EmailDispatcher::class,
        ]);

        Mail::fake();

        Storage::fake('/api/files');
        $filePath = UploadedFile::fake()->image('photo1.jpg')->path();

        try {
            $this->withToken($this->signature)
                ->withHeaders([
                    'accessKey' => $this->accessKey,
                    'ts' => $this->timeStamp,
                ])
                ->post('/api/email/send', [
                    'from' => fake()->email,
                    'to' => fake()->email,
                    'sender' => fake()->name,
                    'receiver' => fake()->name,
                    'subject' => fake()->jobTitle,
                    'language' => 'en',
                    'template' => 'password',
                    'attachments[]' => $filePath,
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
}
