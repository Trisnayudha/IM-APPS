<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\Events\EventService;
use App\Services\Events\EventsSpeakerService;
use Illuminate\Http\Request;

class SpeakerController extends Controller
{
    protected $eventSpeakerService;
    protected $eventService;
    public function __construct(EventsSpeakerService $eventSpeakerService, EventService $eventService)
    {
        $this->eventSpeakerService = $eventSpeakerService;
        $this->eventService = $eventService;
    }

    public function index(Request $request)
    {
        $event_id = $this->eventService->getLastEvent();

        if (!$event_id) {
            return response()->json([
                'status' => 400,
                'message' => 'Missing event_id',
                'payload' => null
            ], 400);
        }

        $filters = [
            'company' => $request->get('company'),
            'status' => $request->get('status'), // live / no_live / all
            'sorting' => $request->get('sorting'), // az / default
            'per_page' => $request->get('per_page', 10),
        ];

        $speakers = $this->eventSpeakerService->listSpeakerEvent($event_id->id, $filters);

        $response['status'] = 200;
        $response['message'] = 'List speaker by event';
        $response['payload'] = $speakers;

        return response()->json($response, 200);
    }


    public function detail(Request $request)
    {
        $speaker_id = $request->get('id'); // atau route param
        if (!$speaker_id) {
            return response()->json([
                'status' => 400,
                'message' => 'Missing speaker ID',
                'payload' => null
            ]);
        }

        $data = $this->eventSpeakerService->getSpeakerDetailWithSchedules($speaker_id);

        if (!$data) {
            return response()->json([
                'status' => 404,
                'message' => 'Speaker not found',
                'payload' => null
            ]);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Speaker detail with schedules',
            'payload' => $data
        ]);
    }
}
