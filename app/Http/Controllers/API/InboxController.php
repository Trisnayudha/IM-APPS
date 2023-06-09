<?php

namespace App\Http\Controllers\API;

use App\Helpers\Notification;
use App\Http\Controllers\Controller;
use App\Services\Inbox\InboxService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class InboxController extends Controller
{
    protected $inboxService;
    public function __construct(InboxService $inboxService)
    {
        $this->inboxService = $inboxService;
    }

    public function index()
    {
        $id =  auth('sanctum')->user()->id ?? null;
        if ($id) {

            $data = $this->inboxService->getAll($id);
            $response['status'] = 200;
            $response['message'] = 'Show data inbox successfully';
            $response['payload'] = $data;
        } else {
            $response['status'] = 401;
            $response['message'] = 'Unauthorized';
            $response['payload'] = null;
        }
        return response()->json($response);
    }

    public function showChatRoom(Request $request)
    {
        $chat_id = $request->chat_id;
        $user_id = auth('sanctum')->user()->id ?? null;
        $limit = (int) $request->limit ?? 5;
        $paginator = DB::table('users_chat_msg AS ucm')
            ->join('users AS u', 'ucm.users_id', '=', 'u.id')
            ->joinSub(function ($query) use ($chat_id) {
                $query->from('users_chat_users AS ucu')
                    ->select('ucu.users_chat_id', 'ucu.users_id', 'ucu.target_id')
                    ->where('ucu.users_chat_id', $chat_id);
            }, 'ucu', function ($join) {
                $join->on('ucu.users_chat_id', '=', 'ucm.users_chat_id')
                    ->whereColumn('ucu.users_id', 'ucm.users_id');
            })
            ->select(
                'ucm.id AS message_id',
                'ucm.created_at',
                'ucm.messages AS message_content',
                'u.id AS sender_id',
                'u.name AS sender_name',
                'u.job_title AS sender_job_title',
                'u.company_name AS sender_company',
                'ucu.target_id'
            )
            ->orderBy('ucm.created_at', 'desc')
            ->paginate($limit);


        $messages = $paginator->items();

        $formattedMessages = collect($messages)->map(function ($message) use ($user_id) {
            return [
                'message_id' => $message->message_id,
                'created_at' => $message->created_at,
                'message_content' => $message->message_content,
                'sender' => [
                    'id' => $message->sender_id,
                    'name' => $message->sender_name,
                    'job_title' => $message->sender_job_title,
                    'company_name' => $message->sender_company,
                ],
                'position' => $message->sender_id == $user_id ? 'right' : 'left'
            ];
        });

        $firstPageUrl = $paginator->url(1);
        $from = $paginator->firstItem();
        $lastPage = $paginator->lastPage();
        $lastPageUrl = $paginator->url($lastPage);
        $nextPageUrl = $paginator->nextPageUrl();
        $path = $paginator->url($paginator->currentPage());
        $perPage = $paginator->perPage();
        $prevPageUrl = $paginator->previousPageUrl();
        $to = $paginator->lastItem();
        $total = $paginator->total();

        $links = collect([
            [
                'url' => $prevPageUrl,
                'label' => '&laquo; Previous',
                'active' => $paginator->currentPage() > 1
            ],
            [
                'url' => $nextPageUrl,
                'label' => 'Next &raquo;',
                'active' => $paginator->hasMorePages()
            ]
        ]);

        $pages = collect(range(1, $lastPage))->map(function ($page) use ($paginator) {
            return [
                'url' => $paginator->url($page),
                'label' => $page,
                'active' => $paginator->currentPage() === $page
            ];
        });

        $links = $links->merge($pages)->toArray();

        $response['status'] = 200;
        $response['message'] = 'Show data inbox successfully';
        $response['payload'] = [
            'data' => $formattedMessages,
            'pagination' => [
                'first_page_url' => $firstPageUrl,
                'from' => $from,
                'last_page' => $lastPage,
                'last_page_url' => $lastPageUrl,
                'links' => $links,
                'next_page_url' => $nextPageUrl,
                'path' => $path,
                'per_page' => $perPage,
                'prev_page_url' => $prevPageUrl,
                'to' => $to,
                'total' => $total
            ]
        ];
        return response()->json($response);
    }


    public function sendChat(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'chat_id' => 'required|integer',
            'message' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'message' => 'Bad Request',
                'errors' => $validator->errors()
            ], 400);
        }

        $user_id = auth('sanctum')->user()->id ?? null;

        // Mengecek apakah pengguna merupakan bagian dari chat room
        $chatUser = DB::table('users_chat_users')
            ->where('users_chat_id', $request->chat_id)
            // ->where('users_chat_users.users_id', $user_id)
            // ->orWhere('users_chat_users.target_id', $user_id)
            ->first();

        $tampung = [
            'users_id' => $chatUser->users_id,
            'target_id' => $chatUser->target_id
        ];

        if ($tampung['users_id'] == $user_id) {
            $target_fix_id = $tampung['target_id'];
        } else {
            $target_fix_id = $tampung['users_id'];
        }

        if (!$chatUser) {
            return response()->json([
                'status' => 403,
                'message' => 'Forbidden'
            ], 403);
        }

        // Menyimpan pesan baru
        $messageId = DB::table('users_chat_msg')->insertGetId([
            'date' => Carbon::now(),
            'created_at' => Carbon::now(),
            'users_chat_id' => $request->chat_id,
            'users_id' => $user_id,
            'messages' => $request->message
        ]);

        // Memperbarui last_messages pada users_chat
        DB::table('users_chat')
            ->where('id', $request->chat_id)
            ->update([
                'last_messages' => $request->message,
                'updated_at' => Carbon::now()
            ]);
        $notif = new Notification();
        $notif->id = $target_fix_id;
        $message = 'You have new notification: ' . $request->message;
        $short_message = substr($message, 0, 100); // Memotong teks menjadi 100 huruf
        $notif->message = $short_message;
        $notif->NotifApp();

        $response['status'] = 200;
        $response['message'] = 'send message inbox successfully';
        $response['payload'] = $messageId;
        return response()->json($response);
    }
}
