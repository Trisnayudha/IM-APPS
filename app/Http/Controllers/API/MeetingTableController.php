<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\MeetingTable\MeetingTableService;

class MeetingTableController extends Controller
{
    /**
     * My Approved Meeting Table
     */
    public function myApproved(Request $request)
    {
        $userId = auth('sanctum')->id();

        if (!$userId) {
            return response()->json([
                'status' => 401,
                'message' => 'Unauthorized',
                'payload' => []
            ], 401);
        }

        $data = MeetingTableService::listApprovedMeetingTableByUser($userId);

        return response()->json([
            'status' => 200,
            'message' => 'My approved meeting table',
            'payload' => $data
        ]);
    }

    public function pending()
    {
        $userId = auth('sanctum')->id();

        if (!$userId) {
            return response()->json([
                'status' => 401,
                'message' => 'Unauthorized',
                'payload' => []
            ], 401);
        }

        $data = MeetingTableService::listPendingRequestByUser($userId);

        return response()->json([
            'status' => 200,
            'message' => 'Pending meeting request',
            'payload' => $data
        ]);
    }

    /**
     * Accept / Decline meeting request
     */
    public function action(Request $request)
    {
        $request->validate([
            'meeting_id' => 'required|integer',
            'action' => 'required|in:accepted,declined'
        ]);

        $userId = auth('sanctum')->id();

        $success = MeetingTableService::actionMeeting(
            $request->meeting_id,
            $userId,
            $request->action
        );

        if (!$success) {
            return response()->json([
                'status' => 403,
                'message' => 'Invalid meeting or not authorized',
                'payload' => []
            ], 403);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Meeting request ' . $request->action,
            'payload' => []
        ]);
    }
}
