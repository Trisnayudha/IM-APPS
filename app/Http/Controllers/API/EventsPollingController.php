<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Events\EventsPollsVote;
use App\Services\Events\EventPollingService;
use App\Services\Events\EventService;
use App\Traits\QrCode;
use Illuminate\Http\Request;

class EventsPollingController extends Controller
{
    use QrCode;
    protected $eventService, $eventPolingService;
    public function __construct(EventService $eventService,  EventPollingService $eventPolingService)
    {
        $this->eventService = $eventService;
        $this->eventPolingService = $eventPolingService;
    }

    public function index(Request $request)
    {
        $qrCode = $request->unique_id;
        $check = self::checkIsPolling($qrCode);
        if ($check) {
            $this->eventPolingService->detail($qrCode);
        } else {
            $response['status'] = 404;
            $response['message'] = 'Qr Code Not Found';
            $response['payload'] = null;
            return response()->json($response, 200);
        }
    }

    public function store(Request $request)
    {
        // Iterasi setiap item dalam request
        foreach ($request->all() as $key => $value) {
            if (strpos($key, 'pg_vote_') === 0) {
                // Ini adalah vote PG
                $questionId = str_replace('pg_vote_', '', $key); // Mendapatkan ID pertanyaannya
                $vote = new EventsPollsVote();
                $vote->users_id = auth('sanctum')->user()->id ?? null; // Atau cara lain untuk mendapatkan user ID
                $vote->events_polls_option_id = $value; // ID opsi yang dipilih
                $vote->save();
            } elseif (strpos($key, 'rating_') === 0) {
                // Ini adalah vote rating
                $questionId = str_replace('rating_', '', $key); // Mendapatkan ID pertanyaannya
                $vote = new EventsPollsVote();
                $vote->users_id = auth('sanctum')->user()->id ?? null; // Atau cara lain untuk mendapatkan user ID
                $vote->rating = $value; // Nilai rating yang diberikan
                // $vote->events_polls_option_id = $value; // ID opsi yang dipilih
                $vote->save();
            }
        }

        return response()->json(['message' => 'Votes berhasil disimpan'], 200);
    }
}
