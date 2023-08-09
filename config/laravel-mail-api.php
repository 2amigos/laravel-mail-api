<?php

return [
    'tokenTime' => env('TOKEN_TIME', 120),
    'language' => env('LANGUAGE', 'en'),
    'template' => env('DEFAULT_TEMPLATE', 'hello-world'),
    'attachmentsAllowedMimetypes' => env('ATTACHMENT_MIMETYPES', ['application/pdf', 'image/*']),
];
