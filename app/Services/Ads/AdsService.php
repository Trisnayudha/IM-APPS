<?php

namespace App\Services\Ads;

use App\Repositories\AdsRepositoryInterface;

use Illuminate\Support\Facades\DB;

class AdsService implements AdsRepositoryInterface
{

    public function getAdsScreen()
    {
        return DB::table('md_ads_screen')->first();
    }

    public function getAdsBanner()
    {
        return DB::table('md_ads_banner_v2')->inRandomOrder()->limit(5)->get();
    }
}
