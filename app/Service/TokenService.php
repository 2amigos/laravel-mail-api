<?php

declare(strict_types=1);

namespace App\Service;

use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class TokenService
{
    /**
     * @throws Exception
     */
    public static function create(Authenticatable $user): array
    {
        try {
            Log::info('Issuing token for user ', collect($user->toArray())->only('id', 'email')->toArray());

            $user->tokens()->delete();
            $expiresAt = Carbon::now()->addMinutes(config('mail-api-service.token-time'));
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
        return app()->get(config('mail-api-service.token-finder'))->findToken($token);
    }

    public static function tokenAuth(Request $request): Authenticatable|null
    {
        if ($request->path() !== 'api/token') {
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

        return null;
    }
}
