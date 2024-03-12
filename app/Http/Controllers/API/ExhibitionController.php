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
        if ($id) {
            $data = $this->exhibitionService->listAll($events_id->id, $search, $category, $special_tags, $filter, $id);
            $response['status'] = 200;
            $response['message'] = 'Successfully show data';
            $response['payload'] = $data;
        } else {
            $response['status'] = 401;
            $response['message'] = 'Unauthorized';
            $response['payload'] = null;
        }
        return response()->json($response);
    }

    public function indexV2(Request $request)
    {
        $id = auth('sanctum')->user()->id ?? null;
        $search = $request->search;
        $category = $request->category;
        $events_id = $this->eventService->getLastEvent();
        $special_tags = $request->special_tags;
        $filter = $request->filter;
        if ($id) {
            $data = $this->exhibitionService->listAll($events_id->id, $search, $category, $special_tags, $filter, $id);

            // Pengelompokan data berdasarkan sponsor_type
            $groupedData = [
                'Platinum' => [],
                'Gold' => [],
                'Silver' => []
            ];
            foreach ($data as $item) {
                if (array_key_exists($item['sponsor_type'], $groupedData)) {
                    $groupedData[$item['sponsor_type']][] = $item;
                }
            }

            $response['status'] = 200;
            $response['message'] = 'Successfully show data';
            // Menetapkan data yang sudah dikelompokkan ke dalam payload
            $response['payload'] = $groupedData;
        } else {
            $response['status'] = 401;
            $response['message'] = 'Unauthorized';
            $response['payload'] = null;
        }
        return response()->json($response);
    }
}
