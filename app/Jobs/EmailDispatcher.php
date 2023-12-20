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

    private string $toEmail;

    private ?string $receiver;

    private ?string $language;

    public $tries = 3;

    public $backoff = 3;

    public function __construct(string $fromEmail, string $toEmail, ?string $sender = '', ?string $receiver = '', ?string $subject = '', ?string $template = '', ?string $language = '', ?array $attachments = [])
    {
        Log::info('Queueing new massage', [
            'to' => $toEmail,
            'from' => $fromEmail,
            'template' => $template,
        ]);

        $this->toEmail = $toEmail;
        $this->receiver = $receiver;
        $this->language = $language;

        $this->message = new Message(
            sender: ['address' => $fromEmail, 'name' => $sender],
            subject: $subject,
            template: $this->getTemplate($template),
            attachments: $attachments,
            receiver: $receiver,
        );

        Log::info('Email Queued');
    }

    public function handle()
    {
        Mail::to($this->toEmail, $this->receiver)
            ->locale($this->getLanguage($this->language))
            ->send($this->message);
        Log::info('Email sent');
    }

    /**
     * returns the given template in case template file exists
     * returns the default app template in case template file does not exists
     *
     * @param string $template
     * @return string
     */
    private function getTemplate(string $template): string
    {
        try {
            view('templates.'.$template);

            return 'templates.'.$template;
        } catch (Exception $e) {
            return 'templates.'.config('laravel-mail-api.template');
        }
    }

    /**
     * returns the given language in case language path exists
     * returns the default language in case language path does not exists
     *
     * @param string $language
     * @return string
     */
    public function getLanguage(string $language): string
    {
        return file_exists(lang_path($language))
            ? $language
            : config('laravel-mail-api.language');
    }
}
