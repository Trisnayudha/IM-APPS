<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\MsPrefix\MsService;
use App\Services\Sponsors\SponsorsService;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    protected $msService;
    protected $sponsorsService;
    public function __construct(MsService $msService, SponsorsService $sponsorsService)
    {
        $this->msService = $msService;
        $this->sponsorsService = $sponsorsService;
    }
    public function banner()
    {
        $banner = $this->msService->getBannerHome();
        $response['status'] = 200;
        $response['message'] = 'Successfully show Image Banner';
        $response['payload'] = $banner;
        return response()->json($response);
    }

    public function sponsors(Request $request)
    {
        $type = $request->type;

        if ($type == 'sponsors') {
            $platinum = $this->sponsorsService->getSponsorsType('Platinum');
            $gold = $this->sponsorsService->getSponsorsType('Gold');
            $silver = $this->sponsorsService->getSponsorsType('silver');
            $landyark = $this->sponsorsService->getLandyark();
            $data = [
                [
                    'name' => 'PLATINUM SPONSORS',
                    'type' => 'platinum',
                    'data' => $platinum,
                ],
                [
                    'name' => 'GOLD SPONSORS',
                    'type' => 'gold',
                    'data' => $gold,
                ],
                [
                    'name' => 'SILVER SPONSORS',
                    'type' => 'silver',
                    'data' => $silver,
                ],
                [
                    'name' => 'LANDYARD & BADGES SPONSOR',
                    'type' => 'free',
                    'data' => $landyark
                ]
            ];
        } else {
            $supporting = $this->sponsorsService->getSupporting();
            $media = $this->sponsorsService->getMedia();

            $data = [
                [
                    'name' => 'SUPPORTING ASSOCIATIONS',
                    'type' => 'free',
                    'data' => $supporting
                ],
                [
                    'name' => 'MEDIA PARTNERS',
                    'type' => 'free',
                    'data' => $media
                ]
            ];
        }
        $response['status'] = 200;
        $response['message'] = 'Successfully show list sponsors';
        $response['payload'] = $data;
        return response()->json($response);
    }
}
