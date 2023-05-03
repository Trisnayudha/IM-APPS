<?php

namespace App\Http\Controllers\API;

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
        // dd($id);
        $data = $this->inboxService->getAll($id);
        // foreach ($data as $x => $row) {
        //     $row->date_chat = (!empty($row->date_chat) ? date('d M Y', strtotime($row->date_chat)) : '');
        // }

        $response['status'] = 200;
        $response['message'] = 'Show data inbox successfully';
        $response['payload'] = $data;
        return response()->json($response);
    }

    public function index2(Request $request)
    {
        $id =  auth('sanctum')->user()->id ?? null;
        $chat_id = $request->chat_id;
        $date = $this->inboxService->arrayDate($chat_id);
        $list = [];
        $no = 0;
        foreach ($date as $row) {
            $dateToday = (new \DateTime(date('Y-m-d')));
            $dateEvents = (new \DateTime(date('Y-m-d', strtotime($row))));
            if ($dateToday >= $dateEvents) {
                $isToday = false;
            } else {
                $isToday = true;
            }

            $list_row['id'] = $no++;
            $list_row['isToday'] = $isToday;
            $list_row['date'] = date('d M Y', strtotime($row));
            $chat = $this->inboxService->getMessage($date, $chat_id);
            foreach ($chat as $x  => $row) {
                $row->date = (!empty($row->date) ? date('H:i', strtotime($row->date)) : '');
                $row->align = ($row->users_id == $id ? 'left' : 'right');
            }
            $list_row['message'] = $chat;
            $list[] = $list_row;
        }
        return $list;
        return response()->json($list);
    }

    public function test()
    {
        $id =  auth('sanctum')->user()->id ?? null;
        $inbox = DB::table('users_chat_users AS ucu')
            ->join('users_chat AS uc', 'ucu.users_chat_id', '=', 'uc.id')
            ->join('users AS u', 'ucu.users_id', '=', 'u.id')
            ->join('users AS target', 'ucu.target_id', '=', 'target.id')
            ->select(
                'uc.id AS chat_id',
                'u.id AS user_id',
                'u.name AS user_name',
                'u.job_title AS user_job_title',
                'u.company_name AS user_company',
                'target.id AS target_id',
                'target.name AS target_name',
                'target.job_title AS target_job_title',
                'target.company_name AS target_company',
                'uc.last_messages AS last_message'
            )
            ->where('ucu.users_id', $id)
            ->orderBy('uc.id', 'desc')
            ->get();
        $response['status'] = 200;
        $response['message'] = 'Show data inbox successfully';
        $response['payload'] = $inbox;
        return response()->json($response);
    }

    public function showChatRoom(Request $request)
    {
        $chat_id = $request->chat_id;
        $user_id = auth('sanctum')->user()->id ?? null;
        $limit = (int) $request->limit ?? 5;
        $paginator = DB::table('users_chat_msg AS ucm')
            ->join('users_chat_users AS ucu', 'ucu.users_chat_id', '=', 'ucm.users_chat_id')
            ->join('users AS u', 'ucm.users_id', '=', 'u.id')
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
            ->where('ucu.users_chat_id', $chat_id)
            ->where('ucu.users_id', $user_id)
            ->orderBy('ucm.created_at', 'asc')
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
            ->where('users_id', $user_id)
            ->first();

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
            ->update(['last_messages' => $request->message]);


        $response['status'] = 200;
        $response['message'] = 'send message inbox successfully';
        $response['payload'] = $messageId;
        return response()->json($response);
    }
}
