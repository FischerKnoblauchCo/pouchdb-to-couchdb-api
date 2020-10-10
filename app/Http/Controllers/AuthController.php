<?php

namespace App\Http\Controllers;

use App\Http\AuthService;
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
     */
    public function login(Request $request) {

        // IMPORTANT: on each login we will re-generate token

        // check if username and password are valid

        // if yes, generate token for the user, which will be valid for 1 hour - after that token will be invalid and user will need to log in again

        // return generated token
    }

    public function getToken(Request $request) {

        // TODO check if user exists, and get user object
        $authService = new AuthService();

        $token = $authService->getToken();

        return $token;
    }

}
