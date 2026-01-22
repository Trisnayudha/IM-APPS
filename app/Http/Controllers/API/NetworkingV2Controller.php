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
        $response = [];

        $userId = auth('sanctum')->id();
        if (!$userId) {
            $response['status']  = 401;
            $response['message'] = 'Unauthorized';
            $response['payload'] = [];
            return response()->json($response, 401);
        }

        $event = $this->eventService->getLastEvent();

        // exclude already swiped
        $swipedIds = DB::table('networking_swaps')
            ->where('users_id', $userId)
            ->where('events_id', $event->id)
            ->pluck('target_id');

        $perPage = $request->get('per_page', 10);

        $data = DB::table('users_delegate')
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
            ->orderBy('users.id')
            ->paginate($perPage);

        $response['status']  = 200;
        $response['message'] = 'Show data networking successfully';
        $response['payload'] = [
            'data' => $data->items(),
            'pagination' => [
                'current_page' => $data->currentPage(),
                'last_page'    => $data->lastPage(),
                'per_page'     => $data->perPage(),
                'total'        => $data->total(),
            ]
        ];

        return response()->json($response);
    }


    public function swipe(Request $request)
    {
        $request->validate([
            'target_id' => 'required|integer',
            'direction' => 'required|in:left,right',
        ]);

        $userId = auth('sanctum')->id();
        if (!$userId) {
            return response()->json([
                'status'  => 401,
                'message' => 'Unauthorized',
                'payload' => [],
            ], 401);
        }

        return DB::transaction(function () use ($request, $userId) {

            $event = $this->eventService->getLastEvent();

            /**
             * =========================================
             * PAYMENT STATUS (SOURCE OF TRUTH)
             * =========================================
             */
            $payment = $this->eventService->getCheckPayment($userId, $event->id);

            $isFreeUser = (
                $payment &&
                trim(strtolower($payment->status)) === 'free'
            );

            /**
             * =========================================
             * EXISTING SWIPE
             * =========================================
             */
            $existingSwipe = DB::table('networking_swaps')
                ->where('users_id', $userId)
                ->where('target_id', $request->target_id)
                ->where('events_id', $event->id)
                ->first();

            /**
             * =========================================
             * EXISTING REQUEST
             * =========================================
             */
            $existingRequest = DB::table('networking_requests')
                ->where('requester_id', $userId)
                ->where('target_id', $request->target_id)
                ->where('events_id', $event->id)
                ->first();

            /**
             * =========================================
             * QUOTA CHECK (FREE + RIGHT ONLY)
             * =========================================
             */
            $quota = null;
            $shouldConsumeQuota = false;

            if ($isFreeUser && $request->direction === 'right') {

                $quota = DB::table('networking_quotas')
                    ->where('users_id', $userId)
                    ->where('events_id', $event->id)
                    ->lockForUpdate()
                    ->first();

                // init quota
                if (!$quota) {
                    $quotaId = DB::table('networking_quotas')->insertGetId([
                        'users_id'    => $userId,
                        'events_id'   => $event->id,
                        'total_quota' => 5,
                        'used_quota'  => 0,
                        'reset_date'  => now()->toDateString(),
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ]);

                    $quota = DB::table('networking_quotas')->where('id', $quotaId)->first();
                }

                // swipe pertama atau left → right
                if (!$existingSwipe || $existingSwipe->direction === 'left') {
                    $shouldConsumeQuota = true;
                }

                // quota habis
                if ($shouldConsumeQuota && $quota->used_quota >= $quota->total_quota) {
                    return response()->json([
                        'status'  => 403,
                        'message' => 'Swap quota exceeded',
                        'payload' => [],
                    ], 403);
                }
            }

            /**
             * =========================================
             * SAVE / UPDATE SWIPE
             * =========================================
             */
            DB::table('networking_swaps')->updateOrInsert(
                [
                    'users_id'  => $userId,
                    'target_id' => $request->target_id,
                    'events_id' => $event->id,
                ],
                [
                    'direction'  => $request->direction,
                    'created_at' => $existingSwipe ? $existingSwipe->created_at : now(),
                    'updated_at' => now(),
                ]
            );

            /**
             * =========================================
             * HANDLE NETWORKING REQUEST
             * =========================================
             */
            $requestStatusBySwipe = [
                'right' => 'accepted',
                'left'  => 'declined',
            ];

            if ($existingRequest) {

                // OPTIONAL: kalau tidak mau accepted bisa ditimpa
                // if ($existingRequest->status === 'accepted') {
                //     return response()->json([
                //         'status'  => 409,
                //         'message' => 'Request already accepted',
                //         'payload' => [],
                //     ], 409);
                // }

                DB::table('networking_requests')
                    ->where('id', $existingRequest->id)
                    ->update([
                        'status'     => $requestStatusBySwipe[$request->direction],
                        'updated_at' => now(),
                    ]);
            } else {

                // create request hanya kalau swipe RIGHT
                if ($request->direction === 'right') {
                    DB::table('networking_requests')->insert([
                        'requester_id' => $userId,
                        'target_id'    => $request->target_id,
                        'events_id'    => $event->id,
                        'message'      => null,
                        'status'       => 'pending',
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ]);
                }
            }

            /**
             * =========================================
             * CONSUME QUOTA
             * =========================================
             */
            if ($isFreeUser && $shouldConsumeQuota && $quota) {
                DB::table('networking_quotas')
                    ->where('id', $quota->id)
                    ->increment('used_quota');

                $quota->used_quota++;
            }

            /**
             * =========================================
             * RESPONSE
             * =========================================
             */
            $quotaPayload = null;

            if ($isFreeUser && $quota) {
                $quotaPayload = [
                    'total'     => $quota->total_quota,
                    'used'      => $quota->used_quota,
                    'remaining' => max(0, $quota->total_quota - $quota->used_quota),
                ];
            }

            return response()->json([
                'status'  => 200,
                'message' => 'Swiped ' . $request->direction . ' successfully',
                'payload' => [
                    'target_id'      => $request->target_id,
                    'direction'      => $request->direction,
                    'request_status' => $existingRequest
                        ? $requestStatusBySwipe[$request->direction]
                        : ($request->direction === 'right' ? 'pending' : null),
                    'quota'          => $quotaPayload,
                ],
            ]);
        });
    }



    /**
     * POST Send Request Connection
     */
    public function sendRequest(Request $request)
    {
        $response = [];

        $request->validate([
            'target_id' => 'required|integer',
            'message'   => 'nullable|string'
        ]);

        $userId = auth('sanctum')->id();
        if (!$userId) {
            $response['status']  = 401;
            $response['message'] = 'Unauthorized';
            $response['payload'] = [];
            return response()->json($response, 401);
        }

        $event = $this->eventService->getLastEvent();

        DB::table('networking_requests')->insertOrIgnore([
            'requester_id' => $userId,
            'target_id'    => $request->target_id,
            'events_id'    => $event->id,
            'message'      => $request->message,
            'status'       => 'pending',
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        // 👉 trigger email / push notif here

        $response['status']  = 200;
        $response['message'] = 'Request sent successfully';
        $response['payload'] = [
            'target_id' => $request->target_id,
            'status'    => 'pending'
        ];

        return response()->json($response);
    }


    /**
     * GET Request Inbox
     */
    public function requestInbox(Request $request)
    {
        $response = [];

        $userId = auth('sanctum')->id();
        if (!$userId) {
            return response()->json([
                'status'  => 401,
                'message' => 'Unauthorized',
                'payload' => []
            ], 401);
        }

        $perPage = $request->get('per_page', 10);

        $data = DB::table('networking_requests')
            ->join('users', 'users.id', '=', 'networking_requests.requester_id')
            ->where('networking_requests.target_id', $userId)
            ->where('networking_requests.status', 'pending')
            ->select(
                'networking_requests.id',
                'networking_requests.requester_id as users_id',
                'networking_requests.events_id',
                'users.name as users_name',
                'users.job_title as users_job_title',
                'users.company_name as users_company_name',
                'users.image_users'
            )
            ->orderBy('networking_requests.created_at', 'desc')
            ->paginate($perPage);

        $response['status']  = 200;
        $response['message'] = 'Show inbox request successfully';
        $response['payload'] = [
            'data' => $data->items(),
            'pagination' => [
                'current_page' => $data->currentPage(),
                'last_page'    => $data->lastPage(),
                'per_page'     => $data->perPage(),
                'total'        => $data->total(),
            ]
        ];

        return response()->json($response);
    }



    /**
     * POST Accept / Decline Request
     */
    public function actionRequest(Request $request, $id)
    {
        $response = [];

        $request->validate([
            'action' => 'required|in:accepted,declined'
        ]);

        $userId = auth('sanctum')->id();
        if (!$userId) {
            $response['status']  = 401;
            $response['message'] = 'Unauthorized';
            $response['payload'] = [];
            return response()->json($response, 401);
        }

        $req = DB::table('networking_requests')
            ->where('id', $id)
            ->where('target_id', $userId)
            ->first();

        if (!$req) {
            $response['status']  = 404;
            $response['message'] = 'Request not found';
            $response['payload'] = [];
            return response()->json($response, 404);
        }

        DB::table('networking_requests')
            ->where('id', $id)
            ->update([
                'status'     => $request->action,
                'updated_at' => now()
            ]);

        $payload = [];

        // auto create chat if accepted
        if ($request->action === 'accepted') {
            $chatId = DB::table('users_chat')->insertGetId([
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('users_chat_users')->insert([
                [
                    'users_chat_id' => $chatId,
                    'users_id'      => $req->requester_id,
                    'target_id'     => $req->target_id,
                    'created_at'    => now()
                ],
                [
                    'users_chat_id' => $chatId,
                    'users_id'      => $req->target_id,
                    'target_id'     => $req->requester_id,
                    'created_at'    => now()
                ],
            ]);

            $payload = [
                'chat_id' => $chatId,
                'status'  => 'accepted'
            ];
        } else {
            $payload = [
                'status' => 'declined'
            ];
        }

        $response['status']  = 200;
        $response['message'] = 'Request ' . $request->action . ' successfully';
        $response['payload'] = $payload;

        return response()->json($response);
    }


    /**
     * GET Connected Users
     */
    public function connections(Request $request)
    {
        $response = [];

        $userId = auth('sanctum')->id();
        if (!$userId) {
            $response['status']  = 401;
            $response['message'] = 'Unauthorized';
            $response['payload'] = [];
            return response()->json($response, 401);
        }

        $perPage = $request->get('per_page', 10);

        $data = DB::table('networking_requests as nr')
            ->join('users as u', function ($join) use ($userId) {
                $join->on('u.id', '=', 'nr.requester_id')
                    ->orOn('u.id', '=', 'nr.target_id');
            })
            ->where('nr.status', 'accepted')
            ->where(function ($q) use ($userId) {
                $q->where('nr.requester_id', $userId)
                    ->orWhere('nr.target_id', $userId);
            })
            ->where('u.id', '<>', $userId)
            ->select(
                'u.id',
                'u.name',
                'u.company_name',
                'u.image_users'
            )
            ->distinct()
            ->orderBy('u.name')
            ->paginate($perPage);

        $response['status']  = 200;
        $response['message'] = 'Show connections successfully';
        $response['payload'] = [
            'data' => $data->items(),
            'pagination' => [
                'current_page' => $data->currentPage(),
                'last_page'    => $data->lastPage(),
                'per_page'     => $data->perPage(),
                'total'        => $data->total(),
            ]
        ];

        return response()->json($response);
    }


    /**
     * POST Request Meeting Table
     */
    public function requestMeeting(Request $request)
    {
        $response = [];

        $request->validate([
            'target_id'      => 'required|integer',
            'schedule_date'  => 'required|date'
        ]);

        $userId = auth('sanctum')->id();
        if (!$userId) {
            $response['status']  = 401;
            $response['message'] = 'Unauthorized';
            $response['payload'] = [];
            return response()->json($response, 401);
        }

        $event = $this->eventService->getLastEvent();

        DB::table('networking_meeting_table')->insert([
            'requester_id'  => $userId,
            'target_id'     => $request->target_id,
            'events_id'     => $event->id,
            'schedule_date' => Carbon::parse($request->schedule_date),
            'status'        => 'pending',
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        $response['status']  = 200;
        $response['message'] = 'Meeting request sent successfully';
        $response['payload'] = [
            'target_id'     => $request->target_id,
            'schedule_date' => Carbon::parse($request->schedule_date)->toDateTimeString(),
            'status'        => 'pending'
        ];

        return response()->json($response);
    }

    public function quota(Request $request)
    {
        $userId = auth('sanctum')->id();
        if (!$userId) {
            return response()->json([
                'status'  => 401,
                'message' => 'Unauthorized',
                'payload' => null
            ], 401);
        }

        $event = $this->eventService->getLastEvent();

        /**
         * =========================
         * SINGLE SOURCE OF TRUTH
         * (HARUS SAMA DENGAN swipe())
         * =========================
         */
        $payment = $this->eventService->getCheckPayment($userId, $event->id);
        $isFreeUser = (!$payment || in_array($payment->status, ['Free']));


        /**
         * =========================
         * USER PAID → UNLIMITED
         * =========================
         */
        if (!$isFreeUser) {
            return response()->json([
                'status'  => 200,
                'message' => 'User quota info',
                'payload' => [
                    'type'                  => 'PAID',
                    'remaining_connect'     => null,
                    'total_connect_avail'   => null
                ]
            ]);
        }

        /**
         * =========================
         * USER FREE → QUOTA BASED
         * =========================
         */
        $defaultQuota = 5;

        // READ ONLY — TIDAK RESET / TIDAK UPDATE
        $quota = DB::table('networking_quotas')
            ->where('users_id', $userId)
            ->where('events_id', $event->id)
            ->first();

        // kalau belum pernah swipe sama sekali
        if (!$quota) {
            return response()->json([
                'status'  => 200,
                'message' => 'User quota info',
                'payload' => [
                    'type'                => 'GUEST',
                    'remaining_connect'   => $defaultQuota,
                    'total_connect_avail' => $defaultQuota
                ]
            ]);
        }

        $total = (int) $quota->total_quota;
        $used  = (int) $quota->used_quota;

        return response()->json([
            'status'  => 200,
            'message' => 'User quota info',
            'payload' => [
                'type'                => 'GUEST',
                'remaining_connect'   => max(0, $total - $used),
                'total_connect_avail' => $total
            ]
        ]);
    }
}
