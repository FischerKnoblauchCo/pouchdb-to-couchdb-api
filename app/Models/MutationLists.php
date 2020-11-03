<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MutationLists extends Model
{
    use HasFactory;

    const ENCRYPT_LIST = [
        'name'
    ];

    const HASH_LIST = [
        'password'
    ];
}
