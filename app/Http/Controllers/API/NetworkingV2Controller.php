<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\Events\EventService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class NetworkingV2Controller extends Controller
{
    protected $eventService;

    public function __construct(EventService $eventService)
    {
        $this->eventService = $eventService;
    }

    /**
     * GET Swipe Cards
     */
    public function cards(Request $request)
    {
        $userId = auth('sanctum')->id();
        if (!$userId) {
            return response()->json(['status' => 401, 'message' => 'Unauthorized']);
        }

        $event = $this->eventService->getLastEvent();

        // exclude already swiped
        $swipedIds = DB::table('networking_swaps')
            ->where('users_id', $userId)
            ->where('events_id', $event->id)
            ->pluck('target_id');

        $cards = DB::table('users_delegate')
            ->join('users', 'users.id', '=', 'users_delegate.users_id')
            ->where('users_delegate.events_id', $event->id)
            ->where('users.id', '<>', $userId)
            ->whereNotIn('users.id', $swipedIds)
            ->select(
                'users.id',
                'users.name',
                'users.job_title',
                'users.company_name',
                'users.image_users'
            )
            ->limit(10)
            ->get();

        return response()->json([
            'status' => 200,
            'payload' => $cards
        ]);
    }

    /**
     * POST Swipe
     */
    public function swipe(Request $request)
    {
        $request->validate([
            'target_id' => 'required|integer',
            'direction' => 'required|in:left,right'
        ]);

        $userId = auth('sanctum')->id();
        $event = $this->eventService->getLastEvent();

        // quota check (only non-paid)
        $quota = DB::table('networking_quota')
            ->where('users_id', $userId)
            ->where('events_id', $event->id)
            ->first();

        if ($quota && $quota->used_quota >= $quota->total_quota) {
            return response()->json([
                'status' => 403,
                'message' => 'Swap quota exceeded'
            ]);
        }

        DB::table('networking_swaps')->updateOrInsert(
            [
                'users_id' => $userId,
                'target_id' => $request->target_id,
                'events_id' => $event->id,
            ],
            [
                'direction' => $request->direction,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        if ($quota) {
            DB::table('networking_quota')
                ->where('id', $quota->id)
                ->increment('used_quota');
        }

        return response()->json([
            'status' => 200,
            'message' => 'Swiped ' . $request->direction
        ]);
    }

    /**
     * POST Send Request Connection
     */
    public function sendRequest(Request $request)
    {
        $request->validate([
            'target_id' => 'required|integer',
            'message' => 'nullable|string'
        ]);

        $userId = auth('sanctum')->id();
        $event = $this->eventService->getLastEvent();

        DB::table('networking_request')->insertOrIgnore([
            'requester_id' => $userId,
            'target_id' => $request->target_id,
            'events_id' => $event->id,
            'message' => $request->message,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 👉 trigger email / push notif here

        return response()->json([
            'status' => 200,
            'message' => 'Request sent'
        ]);
    }

    /**
     * GET Request Inbox
     */
    public function requestInbox()
    {
        $userId = auth('sanctum')->id();

        $requests = DB::table('networking_request')
            ->join('users', 'users.id', '=', 'networking_request.requester_id')
            ->where('networking_request.target_id', $userId)
            ->where('networking_request.status', 'pending')
            ->select(
                'networking_request.id',
                'users.name',
                'users.company_name',
                'users.image_users',
                'networking_request.message'
            )
            ->get();

        return response()->json([
            'status' => 200,
            'payload' => $requests
        ]);
    }

    /**
     * POST Accept / Decline Request
     */
    public function actionRequest(Request $request, $id)
    {
        $request->validate([
            'action' => 'required|in:accepted,declined'
        ]);

        $userId = auth('sanctum')->id();

        $req = DB::table('networking_request')
            ->where('id', $id)
            ->where('target_id', $userId)
            ->first();

        if (!$req) {
            return response()->json(['status' => 404, 'message' => 'Request not found']);
        }

        DB::table('networking_request')
            ->where('id', $id)
            ->update([
                'status' => $request->action,
                'updated_at' => now()
            ]);

        // auto create chat if accepted
        if ($request->action === 'accepted') {
            $chatId = DB::table('users_chat')->insertGetId([
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('users_chat_users')->insert([
                ['users_chat_id' => $chatId, 'users_id' => $req->requester_id, 'target_id' => $req->target_id, 'created_at' => now()],
                ['users_chat_id' => $chatId, 'users_id' => $req->target_id, 'target_id' => $req->requester_id, 'created_at' => now()],
            ]);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Request ' . $request->action
        ]);
    }

    /**
     * GET Connected Users
     */
    public function connections()
    {
        $userId = auth('sanctum')->id();

        $connections = DB::table('networking_request')
            ->join('users', function ($join) use ($userId) {
                $join->on('users.id', '=', 'networking_request.requester_id')
                    ->orOn('users.id', '=', 'networking_request.target_id');
            })
            ->where('networking_request.status', 'accepted')
            ->where(function ($q) use ($userId) {
                $q->where('networking_request.requester_id', $userId)
                    ->orWhere('networking_request.target_id', $userId);
            })
            ->where('users.id', '<>', $userId)
            ->select('users.id', 'users.name', 'users.company_name', 'users.image_users')
            ->distinct()
            ->get();

        return response()->json([
            'status' => 200,
            'payload' => $connections
        ]);
    }

    /**
     * POST Request Meeting Table
     */
    public function requestMeeting(Request $request)
    {
        $request->validate([
            'target_id' => 'required|integer',
            'schedule_date' => 'required|date'
        ]);

        $userId = auth('sanctum')->id();
        $event = $this->eventService->getLastEvent();

        DB::table('networking_meeting_table')->insert([
            'requester_id' => $userId,
            'target_id' => $request->target_id,
            'events_id' => $event->id,
            'schedule_date' => Carbon::parse($request->schedule_date),
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'Meeting request sent'
        ]);
    }
}
