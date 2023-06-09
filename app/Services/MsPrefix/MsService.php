<?php

namespace App\Services\MsPrefix;

use App\Repositories\MsRepositoryInterface;
use Illuminate\Support\Facades\DB;

class MsService implements MsRepositoryInterface
{
    public function getMsPrefixPhone()
    {
        return DB::table('ms_phone_code')
            ->select('ms_phone_code.id', 'ms_phone_code.code')
            ->orderby('ms_phone_code.code', 'asc')
            ->get();
    }
    public function getMsPrefixPhoneDetail($code)
    {
        return DB::table('ms_phone_code')
            ->where('code', $code)->first();
    }

    public function getBannerHome()
    {
        return DB::table('md_banner')
            ->select(
                'md_banner.id',
                'md_banner.title',
                'md_banner.location',
                'md_banner.url',
                'md_banner.date_held',
                'md_banner.image'
            )
            ->orderBy('md_banner.sort', 'asc')
            ->get();
    }

    public function getMdCategorySuggest()
    {
        return DB::table('md_category_suggest_meet')
            ->select(
                'md_category_suggest_meet.id',
                'md_category_suggest_meet.name'
            )
            ->orderBy('md_category_suggest_meet.id', 'desc')
            ->get();
    }

    public function getMsCompanyCategory()
    {
        return DB::table('ms_company_category')
            ->orderBy('id', 'desc')
            ->get();
    }
}
