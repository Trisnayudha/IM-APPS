<?php

namespace App\Repositories;

interface UserRepositoryInterface
{
    public function getUserByEmail($email);
}
