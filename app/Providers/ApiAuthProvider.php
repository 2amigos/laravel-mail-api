<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

use function collect;
use function config;

class ApiAuthProvider
{
    /**
     * @throws Exception
     */
    public static function createToken(Authenticatable $user): array
    {
        try {
            Log::info('Issuing token for user ', collect($user->toArray())->only('id', 'email')->toArray());

            $user->tokens()->delete();
            $expiresAt = Carbon::now()->addMinutes(config('laravel-mail-api.token-time'));
            $token = $user->createToken(name: "{$user->name}:{$user->password}", expiresAt: $expiresAt);

            Log::info('Token issued');

            return [
                'token' => $token->plainTextToken,
                'expires_at' => $expiresAt,
            ];
        } catch (Exception $exception) {
            $exception = new Exception('Unable to generate token. '.$exception->getMessage());

            Log::error($exception->getMessage());

            throw $exception;
        }
    }

    public static function findToken(string $token)
    {
        return PersonalAccessToken::findToken($token);
    }

    public static function auth(Request $request)
    {
        if ($request->path() === 'api/token') {
            return self::basicAuth($request);
        } else {
            return self::tokenAuth($request);
        }
    }

    public static function basicAuth(Request $request): Authenticatable|null
    {
        Log::info('Basic Auth');

        try {
            Auth::guard('user')->onceBasic();
        } catch (UnauthorizedHttpException $exception) {
            Log::error($exception->getMessage());

            throw $exception;
        }

        Log::info('User authenticated.');

        return Auth::guard('user')->user();
    }

    public static function tokenAuth(Request $request): Authenticatable|null
    {
        Log::info('Token validation');

        if (is_null($token = self::findToken($request->bearerToken()))) {
            $exception = new UnauthorizedHttpException('Token', 'Invalid token.');

            Log::error($exception->getMessage());
            throw $exception;
        }

        if (Carbon::parse($token->expires_at)->lessThan(Carbon::now())) {
            $exception = new UnauthorizedHttpException('Token', 'Token expired.');

            Log::error($exception->getMessage());
            throw $exception;
        }

        Log::info('Token Valid');

        return User::whereId($token->tokenable_id)->first();
    }
}
