<?php

namespace App\Http\Middleware;

use App\Http\AuthService;
use App\Models\ReturnStatuses;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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

        try {

            $authService = new AuthService();

            // get user JWT token from request cookies, and check if its valid (not malicious, not expired)
            $token = $request->cookie('access_token');
            $couchDBSession = $request->cookie('session_token');

            // check if tokens are sent
            if (!isset($token) or empty($token) or !isset($couchDBSession) or empty($couchDBSession)) {
                return response()->json(ReturnStatuses::UNAUTHORIZED, ReturnStatuses::_401);
            }

            $info = $authService->checkIfTokenIsValid($token);

            if (isset($info['valid']) && $info['valid'] == false) { // token isnt valid
                return response()->json($info['response'], ReturnStatuses::_401);
            }

            // check if csrf token is valid
            $csrfToken = $request->header('X-CSRF-TOKEN') ?? '';
            $info = $authService->checkUserCsrfToken($token, $csrfToken);

            if (isset($info['valid']) && $info['valid'] == false) { // token isnt valid
                return response()->json($info['response'], ReturnStatuses::_401);
            }

        } catch (\Exception $e) {

            $status = [
                'status' => 401,
                'message' => 'Bad request',
                'reason' => 'Code exception ' // . $e->getMessage()
            ];

            return response()->json($status, ReturnStatuses::_401);
        }

        return $next($request);
    }
}
