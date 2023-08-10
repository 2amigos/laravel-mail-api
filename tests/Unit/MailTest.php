<?php


use App\Mail\Message;
use Illuminate\Mail\Attachment;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;
use Illuminate\Support\Facades\Storage;
use App\Classes\FilesHandler;
use Illuminate\Http\UploadedFile;

class MailTest extends TestCase
{
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

    public function test_mailable_content()
    {
        Storage::fake('api/files');

        $fromEmail = fake()->email;
        $subject = 'testing mail';
        $attachments = FilesHandler::handleAttachments([
            UploadedFile::fake()->image('test.png')
        ]);

        $mail = new Message(
            sender: ['address' => $fromEmail, 'name' => fake()->name],
            subject: $subject,
            template: 'templates.hello-world',
            attachments: $attachments,
            receiver: fake()->name,
        );

        $mail->assertFrom($fromEmail);
        $mail->assertHasSubject($subject);

        $mail->assertSeeInText('Hello World');

        $attachedFile = Attachment::fromStorage($attachments[0]['path'])
            ->as($attachments[0]['name'])
            ->withMime($attachments[0]['mime']);

        $mail->assertHasAttachment(
            $attachedFile
        );
    }
}
