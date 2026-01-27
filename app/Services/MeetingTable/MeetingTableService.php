<?php

namespace App\Services\MeetingTable;

use Illuminate\Support\Facades\DB;

class MeetingTableService
{
    /**
     * List approved meeting table for logged-in user
     */
    public static function listApprovedMeetingTableByUser($userId)
    {
        return DB::table('networking_meeting_tables as nmt')
            ->join('users as u', function ($join) use ($userId) {
                // ambil lawan meeting (bukan user login)
                $join->on('u.id', '=', DB::raw("
                    CASE
                        WHEN nmt.requester_id = {$userId}
                        THEN nmt.target_id
                        ELSE nmt.requester_id
                    END
                "));
            })
            ->select(
                'nmt.id',
                'nmt.table_number',
                'nmt.schedule_date',
                'u.name as participant_name',
                'u.job_title',
                'u.company_name',
                'u.image_users'
            )
            ->where('nmt.status', 'accepted')
            ->where(function ($q) use ($userId) {
                $q->where('nmt.requester_id', $userId)
                    ->orWhere('nmt.target_id', $userId);
            })
            ->orderBy('nmt.schedule_date', 'asc')
            ->get()
            ->map(function ($row) {
                return [
                    'table_name' => 'Table ' . str_pad($row->table_number, 2, '0', STR_PAD_LEFT),
                    'date' => date('d M Y h:i A', strtotime($row->schedule_date)),
                    'participant' => [
                        'name' => $row->participant_name,
                        'job_title' => $row->job_title,
                        'company' => $row->company_name,
                        'photo' => $row->photo ?? ''
                    ]
                ];
            });
    }

    public static function listPendingRequestByUser($userId)
    {
        return DB::table('networking_meeting_tables as nmt')
            ->join('users as u', 'u.id', '=', 'nmt.requester_id')
            ->select(
                'nmt.id',
                'nmt.table_number',
                'nmt.schedule_date',
                'u.name as requester_name',
                'u.job_title',
                'u.company_name',
                'u.image_users' // ✅ ganti di sini
            )
            ->where('nmt.status', 'pending')
            ->where('nmt.target_id', $userId)
            ->orderBy('nmt.schedule_date', 'asc')
            ->get()
            ->map(function ($row) {
                return [
                    'meeting_id' => $row->id,
                    'table_name' => 'Table ' . str_pad($row->table_number, 2, '0', STR_PAD_LEFT),
                    'date' => date('d M Y h:i A', strtotime($row->schedule_date)),
                    'requester' => [
                        'name' => $row->requester_name,
                        'job_title' => $row->job_title,
                        'company' => $row->company_name,
                        'image_users' => $row->image_users ?? '' // ✅ final output
                    ]
                ];
            });
    }

    public static function actionMeeting($meetingId, $userId, $action)
    {
        $meeting = DB::table('networking_meeting_tables')
            ->where('id', $meetingId)
            ->where('target_id', $userId)
            ->where('status', 'pending')
            ->first();

        if (!$meeting) {
            return false;
        }

        DB::table('networking_meeting_tables')
            ->where('id', $meetingId)
            ->update([
                'status' => $action,
                'updated_at' => now()
            ]);

        return true;
    }
}
