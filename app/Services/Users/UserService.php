<?php

namespace App\Services\Users;

use App\Models\Auth\User;
use App\Repositories\UserRepositoryInterface;

class UserService implements UserRepositoryInterface
{
    public function getUserById($id)
    {
        return User::join('ms_phone_code', 'ms_phone_code.ms_country_id', 'users.ms_prefix_call_id')
            ->select('users.*', 'ms_phone_code.code')
            ->where('users.id', $id)
            ->where('is_register', '1')->first();
    }

    public function getUserByEmail($email)
    {
        return User::where('email', $email)->first();
    }

    public function getUserByEmailActive($email)
    {
        return User::where('email', $email)->where('is_register', '1')->select('otp', 'is_register', 'email', 'name', 'created_at', 'updated_at', 'created_at', 'id')->first();
    }

    public function getUserByEmailDeactive($email)
    {
        return User::where('email', $email)->where('is_register', '0')->select('otp', 'is_register', 'email', 'name', 'created_at', 'updated_at', 'created_at', 'id')->first();
    }

    public function createUsers()
    {
        return new User();
    }

    public function getUserByPhone($phone)
    {
        return User::where('phone', $phone)->first();
    }
}
