<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * Check user credentials, if they are valid, give token to him
     *
     * @param Request $request
     */
    public function login(Request $request) {

        // IMPORTANT: on each login we will re-generate token

        // check if username matches given password

        // if yes, generate token for the user, which will be valid for 1 hour - after that token will be invalid and user will need to log in again

        // return generated token
    }


}
