<?php

namespace App\Services\Events;

use App\Models\Events\EventsConferen;
use App\Models\Events\EventsQuestion;
use App\Models\Payment\Payment;
use App\Repositories\EventQuestionInterface;
use Illuminate\Support\Facades\DB;

class EventQuestionService implements EventQuestionInterface
{
    public function detail($uniqueId)
    {
        $find = EventsQuestion::where('unique_id', $uniqueId)->first();
        if ($find) {
            $data = EventsQuestion::join('events_schedule', 'events_schedule.id', 'events_question.events_schedule_id')
                ->where('events_question.unique_id', $uniqueId)->first();
            return $data;
        } else {
            $response['status'] = 404;
            $response['message'] = 'Qr Code Not Found';
            $response['payload'] = null;
            return response()->json($response, 200);
        }
    }
}
