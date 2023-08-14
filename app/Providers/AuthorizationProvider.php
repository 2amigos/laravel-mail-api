<?php

declare(strict_types=1);

namespace App\Providers;

use Carbon\Carbon;
use Exception;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AuthorizationProvider
{
    /**
     * @throws Exception
     */
    public static function authorize(Request $request)
    {
        Log::info('Retrieving authentication signature from request');

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
        Log::info('Checking signature');

        $accessProperties = self::getTokenProperties($accessKey);

        $hashToken = self::signToken(
            appKey: $accessProperties['appKey'],
            appSecret: $accessProperties['appSecret'],
            timeStamp: $timeStamp,
        );

        if (! hash_equals($hashToken, $signedKey)) {
            $errorMessage = 'Invalid token.';

            Log::error($errorMessage);

            throw new Exception($errorMessage);
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
        Log::info('Signing token');

        return hash_hmac(config('laravel-mail-api.hashSignature'), $appKey . $timeStamp, $appSecret);
    }

    /**
     * @param string $timeStamp
     * @return void
     * @throws Exception
     */
    public static function checkTokenExpired(string $timeStamp): void
    {
        Log::info('Checking if token life is valid');

        $tokenLifeTime = config('laravel-mail-api.tokenTime');

        if (Carbon::parse($timeStamp)->addMinutes($tokenLifeTime)->lessThan(Carbon::now()->utc())) {
            $errorMessage = 'Token expired.';

            Log::error($errorMessage);

            throw new Exception($errorMessage);
        }
    }

    /**
     * @param string $accessKey
     * @return array
     * @throws Exception
     */
    public static function getTokenProperties(string $accessKey): array
    {
        Log::info('Retrieving token properties through Access Key');

        $keys = collect(config('laravel-mail-api.accessTokens'));

        if (is_null($accessProperties = $keys->get($accessKey))) {
            $errorMessage = 'Invalid access token.';

            Log::error($errorMessage);

            throw new Exception($errorMessage);
        }

        return $accessProperties;
    }
}
