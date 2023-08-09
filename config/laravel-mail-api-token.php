<?php
return [
    'hashSignature' => env('HASH_SIGNATURE', 'sha256'),

    #access tokens for signature
    'accessTokens' => [
        'access-key-user-1' => [
            'appKey' => 'jwYitJJOop2v',
            'appSecret' => 'token',
        ],
        // unit tests purpose
        'tests' => [
            'appKey' => 'test-token-string',
            'appSecret' => 'token',
        ],
    ],
];
