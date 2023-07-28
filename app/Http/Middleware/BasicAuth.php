<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class BasicAuth
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->path() === 'api/token') {
            Log::info('Basic Auth');

            try {
                Auth::guard('user')->onceBasic();
            } catch (UnauthorizedHttpException $exception) {
                Log::error($exception);

                throw $exception;
            }

            Log::info('User authenticated.');

            return $next($request);
        }

        return $next($request);
    }
}
