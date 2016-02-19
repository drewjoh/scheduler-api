<?php

namespace App\Http\Middleware;

use \Illuminate\Auth\Middleware\Auth;
use \Illuminate\Auth\Middleware\Closure;

class AuthenticateOnceWithBasicAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        return \Auth::onceBasic() ?: $next($request);
    }

}