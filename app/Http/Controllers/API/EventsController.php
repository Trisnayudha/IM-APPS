<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Events\EventsConferen;
use App\Services\Events\EventService;
use Illuminate\Http\Request;


class EventsController extends Controller
{

    protected $eventService;
    public function __construct(EventService $eventService)
    {
        $this->eventService = $eventService;
    }

    public function index(Request $request)
    {

        $date = $request->date;
        $limit = $request->limit;
        $status = $request->status;
        $events_id = $this->eventService->getLastEvent();
        $data = $this->eventService->listAll($events_id->id, $date, $limit, $status);
        $response['status'] = 200;
        $response['message'] = 'Success show data';
        $response['payload'] = $data;

        return response()->json($response, 200);
    }

    public function listAll(Request $request)
    {
        $limit = $request->limit;
        $events_id = $this->eventService->getLastEvent();
        $data = [];
        $data['first_day'] = $this->eventService->listAll($events_id->id, '2025-06-10', $limit, 'replay');
        $data['second_day'] = $this->eventService->listAll($events_id->id, '2025-06-11', $limit, 'replay');
        $data['third_day'] = $this->eventService->listAll($events_id->id, '2025-06-12', $limit, 'replay');
        $data['miners_talk'] = $this->eventService->listAll($events_id->id, '2025-06-10', $limit, 'showcase');
        $data['workshop'] = $this->eventService->listAll($events_id->id, '2025-06-10', $limit, 'workshop');
        $response['status'] = 200;
        $response['message'] = 'Success show data';
        $response['payload'] = $data;

        return response()->json($response, 200);
    }

    public function detail(Request $request)
    {
        $id =  auth('sanctum')->user()->id ?? null;
        $slug = $request->s;
        $find = $this->eventService->detailEvent($slug, $id);
        if ($find) {

            $response['status'] = 200;
            $response['message'] = 'Success show data';
            $response['payload'] = $find;
        } else {
            $response['status'] = 404;
            $response['message'] = 'Event Conference Not Found';
            $response['payload'] = null;
        }
        return response()->json($response, 200);
    }
}
