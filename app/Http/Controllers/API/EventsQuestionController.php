<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Events\EventsQuestion;
use App\Models\Events\EventsQuestionAnswer;
use App\Services\Events\EventQuestionService;
use App\Services\Events\EventService;
use App\Traits\QrCode;
use Illuminate\Http\Request;

class EventsQuestionController extends Controller
{
    use QrCode;
    protected $eventService, $eventQuestionService;
    public function __construct(EventService $eventService,  EventQuestionService $eventQuestionService)
    {
        $this->eventService = $eventService;
        $this->eventQuestionService = $eventQuestionService;
    }

    public function index(Request $request)
    {
        $qrCode = $request->unique_id;
        $check = self::checkIsQuestioner($qrCode);
        if ($check) {
            $find = $this->eventQuestionService->detail($qrCode);
            $response['status'] = 200;
            $response['message'] = 'Success show data';
            $response['payload'] = $find;
        } else {
            $response['status'] = 404;
            $response['message'] = 'Qr Code Not Found';
            $response['payload'] = null;
        }
        return response()->json($response, 200);
    }

    public function store(Request $request)
    {
        $anon = $request->anon;
        $unique_id = $request->unique_id;
        $text = $request->text;

        $save = new EventsQuestionAnswer();
        if ($anon == 'true') {
            $users_id = auth('sanctum')->user()->id ?? null;
            $save->users_id = $users_id;
        }
        $find = EventsQuestion::where('unique_id', $unique_id)->first();
        $save->events_question_id = $find->events_schedule_id;
        $save->text = $text;
        $save->save();
        $response['status'] = 200;
        $response['message'] = 'Success send question';
        $response['payload'] = null;
        return response()->json($response, 200);
    }
}
