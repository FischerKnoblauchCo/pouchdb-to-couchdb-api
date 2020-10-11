<?php

namespace App\Http\Middleware;

use App\Http\AuthService;
use App\Models\ReturnStatuses;
use Closure;
use Illuminate\Http\Request;

class ValidateJWT
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
        $authService = new AuthService();

        // get user JWT token from request header, and check if its valid (not malicious, not expired)
        $headers = $request->headers;

        $token = $authService->getBearerToken($headers);

        // check if token is no sent
        if (!isset($token) or empty($token)) {
            return response()->json(ReturnStatuses::UNAUTHORIZED);
        }

        $info = $authService->checkIfTokenIsValid($token);
        //die(print_r($info));

        if (isset($info['valid']) && $info['valid'] == false) { // token isnt valid
            return response()->json($info['response']);
        }

        return $next($request);
    }
}
