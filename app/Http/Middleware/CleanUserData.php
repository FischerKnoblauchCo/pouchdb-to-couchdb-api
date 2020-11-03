<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CleanUserData
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
        $inputs = $request->all();

        array_walk_recursive($inputs, [$this, 'cleanInput']);

        return $next($request);
    }

    private function cleanInput(&$value, &$key) {
        $key = clean($key);
        $value = clean($value);
    }
}
