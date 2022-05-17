<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class UserHasNoRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  ...$guards
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $roleGroup)
    {
	    if (Gate::denies("$roleGroup-user-group")) {
		    abort(403, 'Access denied');
	    }

        return $next($request);
    }
}
