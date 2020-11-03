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

            Log::info($request->header('X-CSRF-TOKEN'));
//            return json_encode([
//                'csrf' => print_r($request->header('X-CSRF-TOKEN'))
//            ]);


            $authService = new AuthService();

            // get user JWT token from request cookies, and check if its valid (not malicious, not expired)
            $token = $request->cookie('access_token');

            // check if token is no sent
            if (!isset($token) or empty($token)) {
                return response()->json(ReturnStatuses::UNAUTHORIZED);
            }

            $info = $authService->checkIfTokenIsValid($token);

            if (isset($info['valid']) && $info['valid'] == false) { // token isnt valid
                return response()->json($info['response']);
            }

            // check if csrf token is valid
            $csrfToken = $request->header('X-CSRF-TOKEN') ?? '';
            $info = $authService->checkUserCsrfToken($token, $csrfToken);

            if (isset($info['valid']) && $info['valid'] == false) { // token isnt valid
                return response()->json($info['response']);
            }

        } catch (\Exception $e) {
            $status = [
                'status' => 400,
                'message' => 'Bad request',
                'reason' => 'Code exception ' // . $e->getMessage()
            ];

            return response()->json($status);
        }

        return $next($request);
    }
}
