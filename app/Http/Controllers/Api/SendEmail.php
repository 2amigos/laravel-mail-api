<?php

namespace App\Http\Controllers\Api;

use App\Classes\FilesHandler;
use App\Http\Controllers\Controller;
use App\Jobs\EmailDispatcher;
use App\Jobs\FilesCleanup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;

class SendEmail extends Controller
{
    public function send(Request $request)
    {
        $this->validateRequest($request);

        $from = $request->input('from');
        $sender = $request->input('sender', '');
        $to = $request->input('to');
        $receiver = $request->input('receiver', '');
        $subject = $request->input('subject', '');
        $template = $request->input('template', config('laravel-mail-api.template'));
        $language = $request->input('language', config('laravel-mail-api.language'));
        $attachments = FilesHandler::handleAttachments($request->file()['attachments'] ?? []);

        Bus::chain([
            new EmailDispatcher(
                from: $from,
                to: $to,
                sender: $sender,
                receiver: $receiver,
                subject: $subject,
                template: $template,
                language: $language,
                attachments: $attachments
            ),
            new FilesCleanup($attachments),
        ])->catch(
            fn () => dispatch(new FilesCleanup($attachments))
        )->dispatch();

        return response()
            ->json(['data' => [
                'message' => 'the message will be sent!',
            ]])
            ->setStatusCode(200);
    }

    /**
     * validates incoming request
     */
    private function validateRequest(Request $request): void
    {
        $allowedMimeTypes = config('laravel-mail-api.attachments-allowed-mimetypes');

        $rules = [
            'from' => 'required|email',
            'sender' => 'string|nullable',
            'to' => 'required|email',
            'receiver' => 'nullable|string',
            'subject' => 'nullable|string',
            'attachments' => 'nullable|array',
            'language' => 'nullable|string|min:2|max:2',
            'template' => 'nullable|string',
        ];

        if ($allowedMimeTypes !== '*') {
            $rules['attachments.*'] = 'mimetypes:'.implode(',', $allowedMimeTypes);
        }

        $request->validate($rules);
    }
}
