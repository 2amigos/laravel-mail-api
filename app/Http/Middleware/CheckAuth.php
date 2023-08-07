<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\UnauthorizedException;

class CheckAuth
{
    /**
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        Log::info('Check user authentication');

        if ($request->path() !== 'api/token/login') {
            if (! Auth::check()) {
                $exception = new UnauthorizedException('Unauthorized.');

                Log::error($exception->getMessage());
                throw $exception;
            }

            Log::info('Authenticated');
        }
        return $next($request);
    }
}
