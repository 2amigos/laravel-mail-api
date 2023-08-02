<?php

namespace App\Mail;

use Illuminate\Mail\Attachment;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class Message extends Mailable
{
    use SerializesModels;

    protected Address $sender;

    protected string $template;

    protected ?array $attachmentsList;

    protected ?string $receiver;

    /**
     * Create a new message instance.
     */
    public function __construct(array $sender, string $subject, string $template, ?array $attachments, ?string $receiver = '')
    {
        $this->sender = new Address($sender['address'], $sender['name'] ?? '');
        $this->subject = $subject;
        $this->template = $template;
        $this->attachmentsList = $attachments;
        $this->receiver = $receiver;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: $this->sender,
            subject: $this->subject ?? '',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: $this->template,
            with: [
                'receiver' => $this->receiver,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $attachments = [];

        foreach ($this->attachmentsList as $attachment) {
            $attachments[] = Attachment::fromStorage($attachment['path'])
                ->as($attachment['name'])
                ->withMime($attachment['mime']);
        }

        return $attachments;
    }
}
