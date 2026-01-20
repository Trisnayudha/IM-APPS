<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\Events\EventService;
use App\Services\Networking\NetworkingService;
use App\Traits\Events;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class NetworkingController extends Controller
{
    use Events;
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
            self::countVisitNetworking($events_id->id, $id, null);
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
        $authUser = auth('sanctum')->user();

        // cek login
        if (!$authUser) {
            return response()->json([
                'status' => 401,
                'message' => 'Unauthorized',
                'payload' => null
            ], 401);
        }

        // validasi users_id wajib
        $validator = Validator::make($request->all(), [
            'users_id' => 'required|integer'
        ], [
            'users_id.required' => 'users_id wajib di isi',
            'users_id.integer' => 'users_id harus berupa angka'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'message' => 'Validation error',
                'payload' => $validator->errors()
            ], 422);
        }

        $users_id = $request->users_id;
        $id = $authUser->id;

        $profile = $this->networkingService->detailDelegate($users_id);

        if (!$profile) {
            return response()->json([
                'status' => 404,
                'message' => 'Detail Not Found',
                'payload' => null
            ], 404);
        }

        self::countVisitNetworking(14, $id, $users_id);

        return response()->json([
            'status' => 200,
            'message' => 'Show detail delegate',
            'payload' => $profile
        ], 200);
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
            DB::table('users_chat_users')->insert([
                'created_at' => Carbon::now(),
                'users_chat_id' => $create_id,
                'users_id' => $target_id,
                'target_id' => $id,
            ]);
        }
        $response['status'] = 200;
        $response['message'] = $room;
        $response['payload'] = $create_id ? $create_id : $users_chat_id;
        return response()->json($response);
    }
}
