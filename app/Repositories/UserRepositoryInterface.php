<?php

namespace App\Repositories;

interface UserRepositoryInterface
{
    public function getUserByEmail($email);
    public function getUserByEmailActive($email);
    public function getUserByEmailDeactive($email);
    public function createUsers();
    public function getUserById($id);
    public function getUserByPhone($phone);
}
