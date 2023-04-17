<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\Bookmark\BookmarkService;
use App\Services\Events\EventService;
use Illuminate\Http\Request;

class BookmarkController extends Controller
{
    protected $bookmarkService;
    protected $eventService;
    public function __construct(BookmarkService $bookmarkService, EventService $eventService)
    {
        $this->bookmarkService = $bookmarkService;
        $this->eventService = $eventService;
    }

    public function index(Request $request)
    {
        $id =  auth('sanctum')->user()->id ?? null;
        $limit = $request->limit;
        $type = $request->type;
        $data = [];
        $events_id = $this->eventService->getLastEvent();
        if ($type == 'events') {
            $data['schedule'] = $this->bookmarkService->listAll($limit, 'schedule', $id, $events_id->id);
            $data['program'] = $this->bookmarkService->listAll($limit, 'program', $id, $events_id->id);
            //return Schedule, Replay Program
        } elseif ($type == 'exhibition') {
            //return company
            $data['company'] = $this->bookmarkService->listAll($limit, 'company', $id, $events_id->id);
        } elseif ($type == 'networking') {
            //return networking
            $data['networking'] = $this->bookmarkService->listAll($limit, 'networking', $id, $events_id->id);
        } elseif ($type == 'directory') {
            $data['produk'] = $this->bookmarkService->listAll($limit, 'product', $id, $events_id->id);
            $data['news'] = $this->bookmarkService->listAll($limit, 'news', $id, $events_id->id);
            $data['project'] = $this->bookmarkService->listAll($limit, 'project', $id, $events_id->id);
            $data['media'] = $this->bookmarkService->listAll($limit, 'media', $id, $events_id->id);
            $data['news'] = $this->bookmarkService->listAll($limit, 'news', $id, $events_id->id);
            // $data['video'] = $this->bookmarkService->listAll($limit, 'video', $id, $events_id->id);
            //return produk,news,project,media,news
        }
        $response['status'] = 200;
        $response['message'] = 'Success show data';
        $response['payload'] = $data;

        return response()->json($response, 200);
    }

    public function listAll(Request $request)
    {
        $id =  auth('sanctum')->user()->id ?? null;
        $type = $request->type;
        $limit = $request->limit;
        $events_id = $this->eventService->getLastEvent();
        $data = $this->bookmarkService->listAll($limit, $type, $id, $events_id->id);
        $response['status'] = 200;
        $response['message'] = 'Success show data';
        $response['payload'] = $data;
        return response()->json($response, 200);
    }
}
