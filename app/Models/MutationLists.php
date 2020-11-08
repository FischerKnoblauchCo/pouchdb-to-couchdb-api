<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MutationLists extends Model
{
    use HasFactory;

    const USER_ENCRYPT_LIST = [
        'first_name',
        'last_name',
        'company',
        'partner_id',
        'land'
    ];

    const USER_HASH_LIST = [
        'password_x'
    ];

}
