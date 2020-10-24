<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function login(Request $request) {

        $creds = $request->only(['username', 'password']);

        $token = 'dgfdhghfg'; //auth()->attempt($creds); //'325z6trh56j6u6'; //auth()->attempt($creds);

        return response()->json([
            'status' => 200,
            'message' => 'Login successful'
            //'token' => $token
        ])->cookie('access_token', $token, config('app.jwt_token_duration'));
    }

    private function attempt() {
        die("test");
    }
}
