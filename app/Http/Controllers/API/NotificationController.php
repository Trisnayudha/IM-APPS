<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\Notification\NotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    protected $notifServerice;
    public function __construct(NotificationService $notifServerice)
    {
        $this->notifServerice = $notifServerice;
    }

    public function index($limit = 10)
    {
        $id =  auth('sanctum')->user()->id ?? null;
        if ($id) {
            $data = $this->notifServerice->getListbyUser($id, $limit);
            $read = $this->notifServerice->readNotif($id);
            $response['status'] = 200;
            $response['message'] = 'Show data List Notification';
            $response['payload'] = $data;
        } else {
            $response['status'] = 401;
            $response['message'] = 'Unauthorized';
            $response['payload'] = null;
        }
        return response()->json($response);
        //
    }
}
