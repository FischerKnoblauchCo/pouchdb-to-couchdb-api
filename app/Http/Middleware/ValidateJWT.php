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

        if (isset($info['valid']) && $info['valid'] == false) { // token isnt valid
            return response()->json($info['response']);
        }

        // set access token to cookie, so we can use it later on in response middleware
//        $payload = json_decode($info['response']);
//        $_COOKIE[$payload->user_id] = $token; // for each user we assign cookie with his id as a key - to that key is assigned access_token, which will be returned as a cookie in response middleware

        return $next($request);
    }
}
