<?php


namespace App\Http;


use App\Models\MutationLists;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserService
{

    public function encryptOrHashUserData(&$value, $key) {

        if (in_array($key, MutationLists::ENCRYPT_LIST)) {
            $value = Crypt::encrypt($value);
        } else if(in_array($key, MutationLists::HASH_LIST)) {
            $value = Hash::make($value);
        }

    }

    public function decryptUserData(&$value, $key) {

        if (in_array($key, MutationLists::ENCRYPT_LIST)) {
            $value = Crypt::decrypt($value);
        }

    }

    public function decryptUsersData($users) {

        foreach($users as $user) {
            array_walk_recursive($user->doc, [$this, 'decryptUserData']);
        }

        return $users;
    }
}