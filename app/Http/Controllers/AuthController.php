<?php

namespace App\Http\Controllers;

use App\Http\AuthService;
use App\Models\ReturnStatuses;
use Lcobucci\JWT\Builder;
use Illuminate\Http\Request;
use Lcobucci\JWT\Signer;
use Illuminate\Support\Facades\Hash;


class AuthController extends Controller
{
    /**
     * Check user credentials, if they are valid, give token to him
     *
     * @param Request $request
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function login(Request $request) {

        $data = json_decode($request->getContent(), true);
        $username = $data['username'];
        $password = $data['password'];
        $valid = $data['valid']; // TODO using temporary

        // check if credentials are sent
        if (empty($username) or empty($password)) {
            return response()->json(ReturnStatuses::BAD_REQUEST);
        }

        // check if username and password are valid (in CouchDB) // TODO SEND REQUEST TO COUCHDB TO CHECK USER CREDENTIALS
        // TODO for now fake user validation upon temporary value from user request
        if (!$valid) {
            return response()->json(ReturnStatuses::GENERAL_BAD_REQUEST);
        }

        // user passed credentials check, generate JWT for him/her (token will be valid for 1 hour - after that token will be invalid and user will need to log in again)
        $authService = new AuthService();
        $token = $authService->getToken(null);

        return [
            'status' => 200,
            'token' => $token
        ];

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
