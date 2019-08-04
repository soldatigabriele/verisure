<?php

namespace App\Http\Middleware;

use Closure;

class AuthenticateToken
{
    /**
     * Handle the authentication with a token
     *
     * @param  \Illuminate\Http\Request  $request
     * @param Closure $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (config("verisure.settings.auth.active") && $request->auth_token !== config("verisure.settings.auth.token")) {
            if (!is_test()) {
                sleep(0.3);
            }
            abort(401);
        }
        return $next($request);
    }
}
