<?php

namespace App\Http\Controllers\API;

use App\Helpers\Notification;
use App\Http\Controllers\Controller;
use App\Services\Company\CompanyService;
use App\Services\Email\EmailService;
use App\Services\Events\EventService;
use App\Services\MsPrefix\MsService;
use App\Services\Networking\NetworkingService;
use App\Services\Sponsors\SponsorsService;
use App\Services\Users\UserService;
use Illuminate\Http\Request;
use App\Traits\Directory;

class HomeController extends Controller
{
    use Directory;
    protected $msService;
    protected $sponsorsService;
    protected $companyService;
    protected $eventService;
    protected $userService;
    protected $emailService;
    protected $networkingService;
    public function __construct(
        MsService $msService,
        SponsorsService $sponsorsService,
        CompanyService $companyService,
        EventService $eventService,
        UserService $userService,
        EmailService $emailService,
        NetworkingService $networkingService
    ) {
        $this->msService = $msService;
        $this->sponsorsService = $sponsorsService;
        $this->companyService = $companyService;
        $this->eventService = $eventService;
        $this->userService = $userService;
        $this->emailService = $emailService;
        $this->networkingService = $networkingService;
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
        $id =  auth('sanctum')->user()->id ?? null;
        $findCompany = $this->companyService->getCompanyBySlug($slug);
        if ($findCompany) {
            $findContact = $this->companyService->getListContactById($findCompany->id);
            self::countVisitPage('Company', 'In Apps', null, $findCompany->id, 11, $id);
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
            $data = [
                'type' => $checkPayment ? $checkPayment->package : 'guest',
                'show_restriction' => $event->status_event == 'on' ? true : false,
                'qr_code' => $checkPayment ? $checkPayment->qr_code : null,
                'networking' => true,
                'inbox' => true,
                'floor_plan' => true,
                'schedule' => true,
                'exhibition' => true,
                'event_booklet' => true,
                'replay' => true,
                'mining_directory' => true,
                'bookmark' => true,

            ];
            $response['status'] = 200;
            $response['message'] = 'Successfully check data users to event';
            $response['payload'] = $data;
        } else {
            $response['status'] = 401;
            $response['message'] = 'Unauthorized';
            $response['payload'] = null;
        }
        return response()->json($response);
    }

    public function benefit()
    {
        $array = collect([ // membuat array of object
            [
                'type' => 'Platinum',
                'name' => 'Delegate',
                'benefit' => [
                    '3 day delegate and exhibition access (Including Luncheon, Coffee Break and Networking Function)',
                    '40+ Live speeches, panels & Q&As',
                    'Access to presentation materials',
                    'Identified as a company',
                    'Company name, logo and job title on Indonesia Miner platform profile',
                    'Live chat with attendees via the Indonesia Miner platform'
                ]
            ],
            [
                'type' => 'Silver',
                'name' => 'Visitor',
                'benefit' => [
                    '3 day delegate and exhibition access',
                    'Live chat with attendees via the Indonesia Miner platform',
                ]
            ],
        ])->map(function ($item) { // menambahkan objek ke array
            return (object) $item;
        });
        $response['status'] = 200;
        $response['message'] = 'Successfully';
        $response['payload'] = $array;
        return response()->json($response);
    }

    public function sendRequest(Request $request)
    {
        $id =  auth('sanctum')->user()->id ?? null;
        $type = $request->type;
        $find = $this->userService->getUserById($id);
        $type = $type == 'Platinum' ? 'Delegate' : 'Visitor';
        if ($id) {
            $events_id = $this->eventService->getLastEvent();
            // $save = $this->eventService->saveData($find, $events_id->id, $request->type);
            $notif = new Notification();
            $notif->id = $id;
            $notif->message = 'Thank you for request access this event';
            $notif->NotifApp();
            $send = $this->emailService->sendBenefit($type, $find);
            $receive = $this->emailService->receiveBenefit($type, $find);
            $response['status'] = 200;
            $response['message'] = 'Successfully send request event';
            $response['payload'] = $notif;
        } else {
            $response['status'] = 401;
            $response['message'] = 'Unauthorized';
            $response['payload'] = null;
        }
        return response()->json($response);
    }

    public function scan(Request $request)
    {
        $id =  auth('sanctum')->user()->id ?? null;
        $codePayment = $request->codePayment;

        if ($id) {
            $scan = $this->networkingService->scanUsers($codePayment);
            if ($scan) {

                $response['status'] = 200;
                $response['message'] = 'Successfully show data';
                $response['payload'] = $scan;
            } else {
                $response['status'] = 404;
                $response['message'] = 'Failed show data';
                $response['payload'] = null;
            }
        } else {
            $response['status'] = 401;
            $response['message'] = 'Unauthorized';
            $response['payload'] = null;
        }
        return response()->json($response);
    }

    public function eventBooklet()
    {
        $link = 'https://indonesiaminer.com/event-booklet';
        $response['status'] = 200;
        $response['message'] = 'Successfully show data';
        $response['payload'] = $link;
        return response()->json($response);
    }
}
