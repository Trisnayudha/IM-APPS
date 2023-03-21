<?php

namespace App\Services\Users;

use App\Models\Auth\User;
use App\Repositories\UserRepositoryInterface;

class UserService implements UserRepositoryInterface
{
    public function getUserByEmail($email)
    {
        return User::where('email', $email)->first();
    }

    public function getUserByEmailActive($email)
    {
        return User::where('email', $email)->where('is_register', '1')->select('otp', 'is_register', 'email', 'created_at', 'updated_at', 'created_at', 'id')->first();
    }

    public function getUserByEmailDeactive($email)
    {
        return User::where('email', $email)->where('is_register', '0')->select('otp', 'is_register', 'email', 'created_at', 'updated_at', 'created_at', 'id')->first();
    }

    public function createUsers()
    {
        return new User();
    }
}
