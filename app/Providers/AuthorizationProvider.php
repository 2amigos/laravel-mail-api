<?php

declare(strict_types=1);

namespace App\Providers;

use Carbon\Carbon;
use Exception;

use Illuminate\Http\Request;

class AuthorizationProvider
{
    /**
     * @throws Exception
     */
    public static function authorize(Request $request)
    {
        $accessKey = $request->headers->get('accessKey') ?? '';
        $signedKey = $request->bearerToken();
        $timeStamp = $request->headers->get('ts') ?? '';
        $timeZone  = $request->headers->get('tz') ?? '';

        self::checkTokenExpired($timeStamp, $timeZone);
        self::checkSignature($accessKey, $signedKey, $timeStamp);
    }

    /**
     * @param string $accessKey
     * @param string $signedKey
     * @param string $timeStamp
     * @return void
     * @throws Exception
     */
    public static function checkSignature(string $accessKey, string $signedKey, string $timeStamp): void
    {
        $accessProperties = self::getTokenProperties($accessKey);

        $hashToken = self::signToken(
            appKey: $accessProperties['appKey'],
            appSecret: $accessProperties['appSecret'],
            timeStamp: $timeStamp,
        );

        if (! hash_equals($hashToken, $signedKey)) {
            throw new Exception('Invalid token.');
        }
    }

    /**
     * @param string $token
     * @param string $timeStamp
     * @param string $secret
     * @return string
     */
    public static function signToken(string $appKey, string $appSecret, string $timeStamp): string
    {
        return hash_hmac(config('laravel-mail-api-token.hashSignature'), $appKey . $timeStamp, $appSecret);
    }

    public static function checkTokenExpired(string $timeStamp, string $timeZone): void
    {

    }

    public static function getTokenProperties(string $accessKey)
    {
        return config('laravel-mail-api.accessTokens')[$accessKey]
            ?? [
                'appKey' => '',
                'appSecret' => ''
            ];
    }
}
