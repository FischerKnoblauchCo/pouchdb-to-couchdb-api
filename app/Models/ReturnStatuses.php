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
}
