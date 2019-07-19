<?php

namespace App\Http\Middleware;

use Closure;

class AuthenticateToken
{
    public function handle($request, Closure $next, $guard = null)
    {
        if (config("verisure.auth.active") && $request->auth_token !== config("verisure.auth.token")) {
            sleep(0.3);
            abort(401);
        }
        return $next($request);
    }
}
