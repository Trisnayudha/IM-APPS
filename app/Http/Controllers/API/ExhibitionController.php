<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\Events\EventService;
use App\Services\Exhibition\ExhibitionService;
use Illuminate\Http\Request;

class ExhibitionController extends Controller
{
    protected $exhibitionService;
    protected $eventService;
    public function __construct(ExhibitionService $exhibitionService, EventService $eventService)
    {
        $this->exhibitionService = $exhibitionService;
        $this->eventService = $eventService;
    }

    public function index(Request $request)
    {
        $id =  auth('sanctum')->user()->id ?? null;
        $search = $request->search;
        $category = $request->category;
        $events_id = $this->eventService->getLastEvent();
        $special_tags = $request->special_tags;
        $filter = $request->filter;
        $data = $this->exhibitionService->listAll($events_id->id, $search, $category, $special_tags, $filter, $id);
        $response['status'] = 200;
        $response['message'] = 'Successfully show data';
        $response['payload'] = $data;
        return response()->json($response);
    }
}
