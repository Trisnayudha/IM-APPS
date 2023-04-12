<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\Events\EventService;
use App\Services\MiningDirectory\MiningDirectoryService;
use Illuminate\Http\Request;

class MiningDirectoryController extends Controller
{
    protected $miningDirectoryService;
    protected $eventService;
    public function __construct(MiningDirectoryService $miningDirectoryService, EventService $eventService)
    {
        $this->miningDirectoryService = $miningDirectoryService;
        $this->eventService = $eventService;
    }

    public function index(Request $request)
    {
        $type = $request->type;
        $category = $request->category;
        $search = $request->search;
        $tags = $request->tags;
        $filter = $request->filter;
        $events_id = $this->eventService->getLastEvent();
        $data = $this->miningDirectoryService->getListAllTimeline($type, $category, $search, $tags, $filter, $events_id->id);
        $response['status'] = 200;
        $response['message'] = 'Successfully show data';
        $response['payload'] = $data;
        return response()->json($response);
    }
}
