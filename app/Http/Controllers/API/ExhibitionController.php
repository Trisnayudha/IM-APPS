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
        $sponsorType = $request->sponsorsType; // All, Platinum, Gold, Silver
        $sorts = $request->sorts; // Default , A-Z
        $filter = $request->filter;
        $page = $request->page ?? 1000; // untuk paginasi
        $perPage = $request->per_page ?? 10; // jumlah entri per halaman
        $sponsor_types = ['Platinum', 'Gold', 'Silver'];

        if ($id) {
            $response['status'] = 200;
            $response['message'] = 'Successfully show data';
            $response['payload'] = [];

            foreach ($sponsor_types as $type) {
                // Mendapatkan jumlah total perusahaan untuk tipe sponsor saat ini
                $total = $this->exhibitionService->getTotalByType($events_id->id, $type, $search, $category, $special_tags);

                // Mendapatkan data perusahaan terpaginasi untuk tipe sponsor saat ini
                $companies = $this->exhibitionService->listAllByType($events_id->id, $search, $category, $special_tags, $filter, $id, $type, $perPage, $page, $sponsorType, $sorts);

                if ($sponsorType === 'All' || $sponsorType === $type) {
                    $response['payload'][$type] = [
                        'total' => $total,
                        'companies' => $companies,
                        'current_page' => $page,
                        'per_page' => $perPage,
                        'last_page' => ceil($total / $perPage), // Menentukan halaman terakhir berdasarkan jumlah total
                    ];
                }
            }
        } else {
            $response['status'] = 401;
            $response['message'] = 'Unauthorized';
            $response['payload'] = null;
        }

        return response()->json($response);
    }
}
