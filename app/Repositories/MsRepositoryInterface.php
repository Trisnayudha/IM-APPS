<?php

namespace App\Repositories;

interface MsRepositoryInterface
{
    public function getMsPrefixPhone();
    public function getMsPrefixPhoneDetail($code);
    public function getBannerHome();
    public function getMdCategorySuggest();
    public function getMsCompanyCategory();
}
