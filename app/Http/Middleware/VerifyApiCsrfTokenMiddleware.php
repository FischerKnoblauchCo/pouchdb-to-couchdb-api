<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifyApiCsrfTokenMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {

        // check if X-CSRF-TOKEN exists


        // if no, reject request

        // if yes, check if its valid

        // if its not valid, reject request




        return $next($request);
    }
}
