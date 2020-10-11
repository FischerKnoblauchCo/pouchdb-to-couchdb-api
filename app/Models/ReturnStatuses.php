<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturnStatuses extends Model
{
    use HasFactory;

    const UNAUTHORIZED = [
        'status' => 401,
        'message' => 'Unauthorized'
    ];

    const TOKEN_EXPIRED = [
        'status' => 401,
        'message' => 'Token expired'
    ];

    const BAD_TOKEN_FORMAT = [
        'status' => 400,
        'message' => 'Token has bad format'
    ];

    const INVALID_TOKEN_SIGNATURE = [
        'status' => 401,
        'message' => 'Invalid token signature'
    ];

    const BAD_REQUEST = [
        'status' => 404,
        'message' => 'Bad request'
    ];

    const GENERAL_BAD_REQUEST = [
        'status' => 400,
        'message' => 'Bad request'
    ];

    const LOGIN_SUCCESSFULL = [
        'status' => 200,
        'message' => 'Login successfull'
    ];

    const LOGOUT_SUCCESSFULL = [
        'status' => 200,
        'message' => 'Logout successfull'
    ];

    const VALID_TOKEN_EXISTS = [
        'status' => 304,
        'message' => 'User already has valid token'
    ];
}
