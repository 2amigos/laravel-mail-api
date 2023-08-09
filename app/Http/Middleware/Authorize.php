<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Providers\AuthorizationProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class Authorize
{
    /**
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        Log::info('Validate token authorization');

        AuthorizationProvider::authorize($request);

        return $next($request);
    }
}
