<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class SroreUrl
{
    /**
     * Store url of the last visited page with method GET and not AJAX
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     * @param string|null              $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        $response = $next($request);

        if (!$request->ajax() && $request->isMethod('get')) {
            session()->put('backUrl', $request->fullUrl());
        }

        return $response;
    }
}
