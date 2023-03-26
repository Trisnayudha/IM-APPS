<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\Company\CompanyService;
use App\Services\MsPrefix\MsService;
use App\Services\Sponsors\SponsorsService;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    protected $msService;
    protected $sponsorsService;
    protected $companyService;
    public function __construct(MsService $msService, SponsorsService $sponsorsService, CompanyService $companyService)
    {
        $this->msService = $msService;
        $this->sponsorsService = $sponsorsService;
        $this->companyService = $companyService;
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
                    'type' => 'landyard',
                    'data' => $landyark
                ]
            ];
        } else {
            $supporting = $this->sponsorsService->getSupporting();
            $media = $this->sponsorsService->getMedia();

            $data = [
                [
                    'name' => 'SUPPORTING ASSOCIATIONS',
                    'type' => 'supporting',
                    'data' => $supporting
                ],
                [
                    'name' => 'MEDIA PARTNERS',
                    'type' => 'media',
                    'data' => $media
                ]
            ];
        }
        $response['status'] = 200;
        $response['message'] = 'Successfully show list sponsors';
        $response['payload'] = $data;
        return response()->json($response);
    }

    public function detail_free(Request $request)
    {
        $id  = $request->id;
        $type = $request->type;
        $data = $this->sponsorsService->getDetailSponsorFree($id, $type);
        $response['status'] = 200;
        $response['message'] = 'Successfully show detail sponsors';
        $response['payload'] = $data;
        return response()->json($response);
    }

    public function detail_premium(Request $request)
    {
        $slug = $request->slug;

        $findCompany = $this->companyService->getCompanyBySlug($slug);
        if ($findCompany) {
            $findContact = $this->companyService->getListContactById($findCompany->id);
            $data = [
                'company' => $findCompany,
                'contact' => $findContact
            ];
            $response['status'] = 200;
            $response['message'] = 'Successfully show detail sponsors';
            $response['payload'] = $data;
        } else {
            $response['status'] = 404;
            $response['message'] = 'Company Not Found';
            $response['payload'] = null;
        }
        return response()->json($response);
    }
}
