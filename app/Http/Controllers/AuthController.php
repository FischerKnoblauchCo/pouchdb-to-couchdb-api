<?php

namespace App\Http\Controllers;

use App\Http\AuthService;
use App\Models\ReturnStatuses;
use Lcobucci\JWT\Builder;
use Illuminate\Http\Request;
use Lcobucci\JWT\Signer;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Cookie;


class AuthController extends Controller
{
    /**
     * Check user credentials, if they are valid, give token to him
     *
     * @param Request $request
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function login(Request $request) {

        $authService = new AuthService();

        // first check if user already has active token
        $token = $request->cookie('access_token');

        if (isset($token)) {
            // check if its valid
            $info = $authService->checkIfTokenIsValid($token);

            if (isset($info['valid']) && $info['valid'] == true) {
                return response()->json(ReturnStatuses::VALID_TOKEN_EXISTS);
            }
        }

        $data = json_decode($request->getContent(), true);
        $username = $data['username'];
        $password = $data['password'];

        // check if credentials are sent
        if (empty($username) or empty($password)) {
            return response()->json(ReturnStatuses::BAD_REQUEST);
        }

        // check if username and password are valid (in CouchDB) // TODO SEND REQUEST TO COUCHDB TO CHECK USER CREDENTIALS
        // TODO for now fake user validation upon temporary value from user request
        $cookie = $authService->checkIfCredentialsAreValid($username, $password);
        if (empty($cookie) or !isset($cookie)) {
            return response()->json(ReturnStatuses::BAD_CREDENTIALS);
        }

        // user passed credentials check, generate JWT for him/her (token will be valid for 1 hour - after that token will be invalid and user will need to log in again)
        $authService = new AuthService();
        $token = $authService->getToken(null);

        $response = response()->json(ReturnStatuses::LOGIN_SUCCESSFULL);
        $response = $authService->setTokenInCookie($response, $token, 'LOGIN');

        $csrfToken = $authService->getUserCsrfToken($token);

        return $response
                    ->header('Access-Control-Expose-Headers', [
                        'X-CSRF-TOKEN'
                    ])
                    ->header('X-CSRF-TOKEN', $csrfToken);

    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    public function logout(Request $request) {

        $authService = new AuthService();

        // cookie is valid, because request couldnt move through ValidateJWT if it isnt
        $token = '';
        $response = response()->json(ReturnStatuses::LOGOUT_SUCCESSFULL);
        $response = $authService->setTokenInCookie($response, $token, 'LOGOUT');

        return $response;
    }

    /**
     * TODO this is only for testing, will be removed later (whole route as well)
     *
     * @param Request $request
     * @return string
     */
    public function getToken(Request $request) {

        // TODO check if user exists, and get user object
        $authService = new AuthService();

        $token = $authService->getToken(null);

        return $token;
    }

}
