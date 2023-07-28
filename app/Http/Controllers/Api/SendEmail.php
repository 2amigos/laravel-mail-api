<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\EmailDispatcher;
use App\Jobs\FilesCleanup;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;

class SendEmail extends Controller
{
    public function __invoke(Request $request)
    {
        $this->validateRequest($request);

        $from = $request->input('from');
        $sender = $request->input('sender', '');
        $to = $request->input('to');
        $receiver = $request->input('receiver', '');
        $subject = $request->input('subject', '');
        $template = $request->input('template', config('mail-api-service.template'));
        $language = $request->input('language', config('mail-api-service.language'));
        $attachments = $this->handleAttachments($request->file()['attachments'] ?? []);

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
        $allowedMimeTypes = config('mail-api-service.attachments-allowed-mimetypes');

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

    private function handleAttachments(array $attachments): array
    {
        if (empty($attachments)) {
            return $attachments;
        }

        Log::info('Handling attachments');

        $files = [];

        /** @var UploadedFile $attachment */
        foreach ($attachments as $attachment) {
            $name = $attachment->getClientOriginalName();
            $path = $attachment->store('/api/files');

            $files[] = [
                'name' => $name,
                'path' => $path,
                'mime' => $attachment->getMimeType(),
            ];
        }

        Log::info('attachments stored', $files);

        return $files;
    }
}
