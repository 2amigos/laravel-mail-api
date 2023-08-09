<?php


use App\Mail\Message;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class MailTest extends TestCase
{
    use RefreshDatabase;

    public function test_send_mail()
    {
        Mail::fake();

        Mail::to(fake()->email, fake()->name)
            ->locale('en')
            ->send(new Message(
                sender: ['address' => fake()->email, 'name' => fake()->name],
                subject: 'testing mail',
                template: 'hello-world',
                attachments: [],
                receiver: fake()->name,
            ));

        Mail::assertSent(Message::class, 1);
    }
}
