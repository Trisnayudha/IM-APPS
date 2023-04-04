<?php

namespace App\Services\Profile;

use App\Models\Faq\Faq;
use App\Repositories\ProfileRepositoryInterface;

class ProfileService implements ProfileRepositoryInterface
{
    public function postChangeEmail($email)
    {
        //
    }

    public function getFaq()
    {
        return Faq::paginate(10);
    }
}
