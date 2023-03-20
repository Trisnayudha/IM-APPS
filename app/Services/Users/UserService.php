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
}
