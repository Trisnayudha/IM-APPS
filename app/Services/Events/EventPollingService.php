<?php

namespace App\Services\Events;

use App\Models\Events\EventsConferen;
use App\Models\Events\EventsPolls;
use App\Models\Events\EventsPollsQuestion;
use App\Models\Events\EventsQuestion;
use App\Models\Payment\Payment;
use App\Repositories\EventPollingInterface;
use App\Repositories\EventQuestionInterface;
use Illuminate\Support\Facades\DB;

class EventPollingService implements EventPollingInterface
{
    public function detail($uniqueId)
    {
        // Mencari EventPoll berdasarkan unique_id
        $eventPoll = EventsPolls::where('unique_id', $uniqueId)->first();

        if ($eventPoll) {
            // Mengambil semua EventPollQuestion yang terkait dengan EventPoll, termasuk options
            $pollQuestions = EventsPollsQuestion::with(['options'])
                ->where('events_poll_id', $eventPoll->id)
                ->get()
                ->map(function ($question) {
                    return [
                        'id' => $question->id,
                        'type' => $question->type,
                        'question' => $question->question,
                        'answer' => $question->type === 'pg' ? $question->options->map(function ($option) {
                            return ['id' => $option->id, 'pg' => $option->option_pg, 'answer' => $option->option_text];
                        }) : null // Untuk type 'rating', 'answer' bisa di-set null atau sesuai kebutuhan
                    ];
                });

            $response['status'] = 200;
            $response['message'] = 'Success show data';
            $response['payload'] = $pollQuestions;
            return response()->json($response, 200);
        } else {
            $response['status'] = 404;
            $response['message'] = 'Poll not Found';
            $response['payload'] = null;
            return response()->json($response, 200);
        }
    }
}
