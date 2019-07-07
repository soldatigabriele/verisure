<?php

namespace App\Http\Middleware;

use Closure;

class AuthenticateToken
{
    public function handle($request, Closure $next, $guard = null)
    {
        if (! $request->auth_token == config("verisure.auth-token")) {
            abort(401);
        }
        return $next($request);
    }
}
