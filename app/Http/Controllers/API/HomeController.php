<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\Company\CompanyService;
use App\Services\Events\EventService;
use App\Services\MsPrefix\MsService;
use App\Services\Sponsors\SponsorsService;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    protected $msService;
    protected $sponsorsService;
    protected $companyService;
    protected $eventService;
    public function __construct(MsService $msService, SponsorsService $sponsorsService, CompanyService $companyService, EventService $eventService)
    {
        $this->msService = $msService;
        $this->sponsorsService = $sponsorsService;
        $this->companyService = $companyService;
        $this->eventService = $eventService;
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


    public function checkEvent(Request $request)
    {
        $id =  auth('sanctum')->user()->id ?? null;
        if ($id) {
            //ada id
            $event = $this->eventService->getLastEvent();
            $checkPayment = $this->eventService->getCheckPayment($id, $event->id);
            if ($checkPayment) {

                $data = [
                    'type' => $checkPayment->package,
                    'show_restriction' => $event->status_event == 'on' ? true : false,

                ];
                $response['status'] = 200;
                $response['message'] = 'Successfully check data users to event';
                $response['payload'] = $data;
            } else {
                $response['status'] = 404;
                $response['message'] = 'Register Event Not Found / Waiting list';
                $response['payload'] = null;
            }
        } else {
            $response['status'] = 401;
            $response['message'] = 'Unauthorized';
            $response['payload'] = null;
        }
        return response()->json($response);
    }
}
