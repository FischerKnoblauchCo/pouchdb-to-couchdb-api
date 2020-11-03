<?php


namespace App\Http;


use App\Models\MutationLists;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class UserService
{

    public function encryptUserData(&$value, $key) {

        if (in_array($key, MutationLists::ENCRYPT_LIST)) {
            $value = Crypt::encrypt($value);
        }

    }

    public function decryptUserData(&$value, $key) {

        Log::info("user iteration: " . $key);
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