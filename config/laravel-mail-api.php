<?php

return [
    'token-time' => env('TOKEN_TIME', 120),
    'language' => env('LANGUAGE', 'en'),
    'template' => env('DEFAULT_TEMPLATE', 'hello-world'),
    'attachments-allowed-mimetypes' => env('ATTACHMENT_MIMETYPES', ['application/pdf', 'image/*']),
];
