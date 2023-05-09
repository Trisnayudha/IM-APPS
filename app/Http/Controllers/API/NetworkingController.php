<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\Events\EventService;
use App\Services\Networking\NetworkingService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class NetworkingController extends Controller
{
    protected $networkingService;
    protected $eventService;
    public function __construct(NetworkingService $networkingService, EventService $eventService)
    {
        $this->networkingService = $networkingService;
        $this->eventService = $eventService;
    }

    public function index(Request $request)
    {
        $search = $request->search;
        $limit = $request->limit;
        $id =  auth('sanctum')->user()->id ?? null;
        if ($id) {
            $events_id =  $this->eventService->getLastEvent();
            $data = $this->networkingService->listAll($search, $limit, $id, $events_id->id);
            $response['status'] = 200;
            $response['message'] = 'Show data networking successfully';
            $response['payload'] = $data;
        } else {
            $response['status'] = 401;
            $response['message'] = 'Unauthorized';
            $response['payload'] = null;
        }
        return response()->json($response);
    }


    public function detail(Request $request)
    {
        $id =  auth('sanctum')->user()->id ?? null;
        $users_id = $request->users_id;
        if ($id) {
            $profile = $this->networkingService->detailDelegate($users_id);
            if ($profile) {
                $response['status'] = 200;
                $response['message'] = 'Show detail delegate';
                $response['payload'] = $profile;
            } else {
                $response['status'] = 404;
                $response['message'] = 'Detail Not Found';
                $response['payload'] = null;
            }
        } else {
            $response['status'] = 401;
            $response['message'] = 'Unauthorized';
            $response['payload'] = null;
        }
        return response()->json($response);
        //
    }

    public function createRoom(Request $request)
    {
        $target_id = $request->target_id;
        $id =  auth('sanctum')->user()->id ?? null;
        // dd($id);
        $room = DB::table('users_chat_users')->where('target_id', $target_id)->where('users_id', $id)->first();
        $users_chat_id = $room ? $room->users_chat_id : null;
        $create_id = null;
        if ($users_chat_id) {
            $room = 'Already Created';
        } else {
            $room = 'Room Created';

            $create_id = DB::table('users_chat')->insertGetId([
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            DB::table('users_chat_users')->insert([
                'created_at' => Carbon::now(),
                'users_chat_id' => $create_id,
                'users_id' => $id,
                'target_id' => $target_id,
            ]);
        }
        $response['status'] = 200;
        $response['message'] = $room;
        $response['payload'] = $create_id ? $create_id : $users_chat_id;
        return response()->json($response);
    }
}
