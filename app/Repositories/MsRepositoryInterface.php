<?php

namespace App\Repositories;

interface MsRepositoryInterface
{
    public function getMsPrefixPhone();
    public function getMsPrefixPhoneDetail($code);
}
