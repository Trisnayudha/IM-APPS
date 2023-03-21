<?php

namespace App\Repositories;

interface UserRepositoryInterface
{
    public function getUserByEmail($email);
    public function getUserByEmailActive($email);
    public function getUserByEmailDeactive($email);
    public function createUsers();
}
