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
        $signedKey = $request->bearerToken() ?? '';
        $timeStamp = $request->headers->get('ts') ?? '';

        self::checkTokenExpired($timeStamp);
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
     * @param string $appKey
     * @param string $appSecret
     * @param string $timeStamp
     * @return string
     * @throws Exception
     */
    public static function signToken(string $appKey, string $appSecret, string $timeStamp): string
    {
        return hash_hmac(config('laravel-mail-api.hashSignature'), $appKey . $timeStamp, $appSecret);
    }

    /**
     * @param string $timeStamp
     * @return void
     * @throws Exception
     */
    public static function checkTokenExpired(string $timeStamp): void
    {
        $tokenLifeTime = config('laravel-mail-api.tokenTime');

        if (Carbon::parse($timeStamp)->addMinutes($tokenLifeTime)->lessThan(Carbon::now()->utc())) {
            throw new Exception('Token expired.');
        }
    }

    /**
     * @param string $accessKey
     * @return array
     * @throws Exception
     */
    public static function getTokenProperties(string $accessKey): array
    {
        $keys = collect(config('laravel-mail-api.accessTokens'));

        if (is_null($accessTokenProperties = $keys->get($accessKey))) {
            throw new Exception('Invalid access token.');
        }

        return $accessTokenProperties;
    }
}
