<?php

namespace App\Jobs;

use App\Mail\Message;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailDispatcher implements ShouldQueue, ShouldBeUnique
{
    use Queueable;

    private Mailable $message;

    private string $to;

    private ?string $receiver;

    private ?string $language;

    public $backoff = 3;

    public function __construct(string $from, string $to, ?string $sender = '', ?string $receiver = '', ?string $subject = '', ?string $template = '', ?string $language = '', ?array $attachments = [])
    {
        Log::info('Queueing new massage', [
            'to' => $to,
            'from' => $from,
            'template' => $template,
        ]);

        $this->to = $to;
        $this->receiver = $receiver;
        $this->language = $language;

        $this->message = new Message(
            sender: ['address' => $from, 'name' => $sender],
            subject: $subject,
            template: $this->getTemplate($template),
            attachments: $attachments,
            receiver: $receiver,
        );

        Log::info('Email Queued');
    }

    public function handle()
    {
        //$this->message->attachments
        Mail::to($this->to, $this->receiver)
            ->locale($this->getLanguage($this->language))
            ->send($this->message);
    }

    /**
     * returns the given template in case template file exists
     * returns the default app template in case template file does not exists
     */
    private function getTemplate(string $template): string
    {
        try {
            view('templates.'.$template);

            return 'templates.'.$template;
        } catch (Exception $e) {
            return 'templates.'.config('mail-api-service.template');
        }
    }

    /**
     * returns the given language in case language path exists
     * returns the default language in case language path does not exists
     */
    private function getLanguage(string $language): string
    {
        return file_exists(lang_path($language))
            ? $language
            : config('mail-api-service.language');
    }
}
