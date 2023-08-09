<?php


use App\Classes\FilesHandler;
use App\Jobs\EmailDispatcher;
use App\Jobs\FilesCleanup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class JobsTest extends TestCase
{
    use RefreshDatabase;

    public $files = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->files = FilesHandler::handleAttachments([UploadedFile::fake()->image('test1.png')]);
    }

    public function test_dispatch_email()
    {
        Queue::fake();
        Mail::fake();

        dispatch_sync(
            new EmailDispatcher(
                from: fake()->email,
                to: fake()->email,
                sender: fake()->name,
                receiver: fake()->name,
                subject: 'testing jobs',
                template: 'hello-world',
                language: 'en',
                attachments: $this->files
            )
        );

        dispatch_sync(
            new EmailDispatcher(
                from: fake()->email,
                to: fake()->email,
            )
        );

        dispatch_sync(
            new EmailDispatcher(
                from: fake()->email,
                to: fake()->email,
                sender: fake()->name,
                receiver: fake()->name,
                subject: 'testing jobs',
                template: 'password',
                language: 'es',
            )
        );

        Queue::assertPushed(EmailDispatcher::class, 3);
    }

    public function test_files_cleanup()
    {
        Queue::fake();

        dispatch_sync(
            new FilesCleanup($this->files)
        );

        dispatch_sync(
            new FilesCleanup([])
        );

        Queue::assertPushed(FilesCleanup::class, 2);
    }
}
