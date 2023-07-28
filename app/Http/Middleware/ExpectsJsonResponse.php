<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ExpectsJsonResponse
{
    public function handle(Request $request, Closure $next)
    {
        $request->expectsJson();

        return $next($request);
    }
}
