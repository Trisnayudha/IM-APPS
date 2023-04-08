<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\Events\EventService;
use App\Services\Networking\NetworkingService;
use Illuminate\Http\Request;

class NetworkingController extends Controller
{
    protected $networkingService;
    protected $eventService;
    public function __construct(NetworkingService $networkingService, EventService $eventService)
    {
        $this->networkingService = $networkingService;
        $this->eventService = $eventService;
    }

    public function index(Request $request)
    {
        $search = $request->search;
        $limit = $request->limit;
        $id =  auth('sanctum')->user()->id ?? null;
        if ($id) {
            $events_id =  $this->eventService->getLastEvent();
            $data = $this->networkingService->listAll($search, $limit, $id, $events_id->id);
            $response['status'] = 200;
            $response['message'] = 'Show data networking successfully';
            $response['payload'] = $data;
        } else {
            $response['status'] = 401;
            $response['message'] = 'Unauthorized';
            $response['payload'] = null;
        }
        return response()->json($response);
    }


    public function detail(Request $request)
    {
        $id =  auth('sanctum')->user()->id ?? null;
        $users_id = $request->users_id;
        if ($id) {
            $profile = $this->networkingService->detailDelegate($users_id);
            if ($profile) {
                $response['status'] = 200;
                $response['message'] = 'Show detail delegate';
                $response['payload'] = $profile;
            } else {
                $response['status'] = 404;
                $response['message'] = 'Detail Not Found';
                $response['payload'] = null;
            }
        } else {
            $response['status'] = 401;
            $response['message'] = 'Unauthorized';
            $response['payload'] = null;
        }
        return response()->json($response);
        //
    }
}
