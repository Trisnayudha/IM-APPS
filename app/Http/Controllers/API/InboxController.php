<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\Inbox\InboxService;
use Illuminate\Http\Request;

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
        foreach ($data as $x => $row) {
            $row->date_chat = (!empty($row->date_chat) ? date('d M Y', strtotime($row->date_chat)) : '');
        }

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
    public static function showMessage($date, $chat_id, $users_id)
    {
    }
}
