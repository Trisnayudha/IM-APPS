<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\Ads\AdsService;
use Illuminate\Http\Request;

class AdsController extends Controller
{
    protected $adsService;
    public function __construct(AdsService $adsService)
    {
        $this->adsService = $adsService;
    }

    public function screen()
    {
        $data = $this->adsService->getAdsScreen();
        $response['status'] = 200;
        $response['message'] = 'Successfully show ads Screen';
        $response['payload'] = $data;
        return response()->json($response);
    }
    public function banner()
    {
        $data = $this->adsService->getAdsBanner();
        $response['status'] = 200;
        $response['message'] = 'Successfully show ads Banner';
        $response['payload'] = $data;
        return response()->json($response);
    }
}
