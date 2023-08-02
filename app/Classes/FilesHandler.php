<?php

namespace App\Classes;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class FilesHandler
{
    /**
     * @param array<UploadedFile> $attachments
     * @return array
     */
    public static function handleAttachments(array $attachments): array
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
