<?php

namespace App\Services\Inbox;

use App\Repositories\InboxRepositoryInterface;
use Illuminate\Support\Facades\DB;

class InboxService implements InboxRepositoryInterface
{
    public function getAll($users_id)
    {
        return DB::table('users_chat')
            ->select(
                'users_chat.id',
                'users_chat.updated_at as date_chat',
                'users_chat_users.users_chat_id',
                'users.id as users_id',
                'users.name as users_name',
                'users_chat.last_messages as message'
            )
            ->join('users_chat_users', function ($join) use ($users_id) {
                $join->on('users_chat.id', '=', 'users_chat_users.users_chat_id');
                if ($users_id) {
                    $join->where('users_chat_users.users_id', $users_id);
                }
            })
            ->join('users', function ($join) {
                $join->on('users.id', '=', 'users_chat_users.target_id');
            })
            ->get();
    }

    public function detailUsers($chat_id, $users_id)
    {
        return DB::table('users_chat')
            ->select(
                'users_chat.id',
                'users_chat.updated_at as date_chat',
                'users_chat_users.users_chat_id',
                'users.id as users_id',
                'users.name as users_name',
                'users_chat.last_messages as message'
            )
            ->join('users_chat_users', function ($join) use ($chat_id, $users_id) {
                $join->on('users_chat.id', '=', 'users_chat_users.users_chat_id');
                if ($chat_id) {
                    $join->where('users_chat_users.users_chat_id', $chat_id);
                }
                if ($users_id) {
                    $join->where('users_chat_users.target_id', $users_id);
                }
            })
            ->join('users', function ($join) {
                $join->on('users.id', '=', 'users_chat_users.target_id');
            })
            ->first();
    }

    public function arrayDate($chat_id)
    {
        return DB::table('users_chat_msg')
            ->select('date')
            ->where('users_chat_id', $chat_id)
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->pluck('date')
            ->toArray();
    }

    public function getMessage($date, $chat_id)
    {
        return DB::table('users_chat_msg')
            ->select(
                'users_chat_msg.id',
                'users_chat_msg.created_at as date',
                'users_chat_msg.users_id',
                'users_chat_msg.messages as message'
            )
            ->where(function ($q) use ($date, $chat_id) {
                if ($date) {
                    $q->whereDate('users_chat_msg.date', $date);
                }
                if ($chat_id) {
                    $q->where('users_chat_msg.users_chat_id', $chat_id);
                }
            })
            ->orderBy('users_chat_msg.id', 'asc')
            ->get();
    }
}
