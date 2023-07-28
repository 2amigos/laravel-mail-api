<?php

use Laravel\Sanctum\PersonalAccessToken;

return [
    'token-time' => env('TOKEN_TIME', 120),
    'language' => env('LANGUAGE', 'en'),
    'template' => env('DEFAULT_TEMPLATE', 'hello-world'),
    'token-finder' => PersonalAccessToken::class,
    'attachments-allowed-mimetypes' => env('ATTACHMENT_MIMETYPES', ['application/pdf', 'image/*']),
];
