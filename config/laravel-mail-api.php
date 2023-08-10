<?php

return [
    /*
    |-----------------------------------------------------------
    | Token Time
    |-----------------------------------------------------------
    |
    | defines for how long a signature will be valid, in minutes
    */
    'tokenTime' => env('TOKEN_TIME', 120),

    /*
    |-----------------------------------------------------------
    | Language
    |-----------------------------------------------------------
    |
    | defines the default language for email templates
    */
    'language' => env('LANGUAGE', 'en'),

    /*
    |-----------------------------------------------------------
    | Template
    |-----------------------------------------------------------
    |
    | defines the default email template
    */
    'template' => env('DEFAULT_TEMPLATE', 'hello-world'),

    /*
    |-----------------------------------------------------------
    | Attachments Mime-types
    |-----------------------------------------------------------
    |
    | defines a list of valid mime-types for attachments
    | set to "*" to allow any mime-type
    */
    'attachmentsAllowedMimetypes' => env('ATTACHMENT_MIMETYPES', ['application/pdf', 'image/*']),

    /*
    |-----------------------------------------------------------
    | Hash Signature
    |-----------------------------------------------------------
    |
    | defines the hash algorithm for Access Key signature
     */
    'hashSignature' => env('HASH_SIGNATURE', 'sha512'),

    /*
    |-----------------------------------------------------------
    | Access Tokens
    |-----------------------------------------------------------
    |
    | define a list of access tokens
     */
    'accessTokens' => [
        'access-key-user-1' => [
            'appKey' => 'jwYitJJOop2v',
            'appSecret' => 'token',
        ],

        /*
        | unit tests access token
         */
        'tests' => [
            'appKey' => 'test-token-string',
            'appSecret' => 'token',
        ],
    ],
];
