<?php

namespace App\Repositories;

interface ProfileRepositoryInterface
{

    public function postChangeEmail($email);
    public function getFaq();
}
