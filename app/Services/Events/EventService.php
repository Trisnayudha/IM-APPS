<?php

namespace App\Services\Events;

use App\Repositories\EventRepositoryInterface;
use Illuminate\Support\Facades\DB;

class EventService implements EventRepositoryInterface
{
    public function getEventId($id)
    {
        //
        return DB::table('events')->where('id', $id)
            ->first();
    }

    public function getEventSlug($slug)
    {
        return DB::table('events')->where('id', $slug)
            ->first();
    }

    public function getLastEvent()
    {
        return DB::table('events')->orderByDesc('id')->first();
    }
    public function getCheckPayment($users_id, $events_id)
    {
        return DB::table('payment')->where('users_id', $users_id)->where('events_id', $events_id)->where('aproval_quota_users', 1)->first();
    }

    public function listAll($events_id, $date, $limit = 3, $status)
    {
        return DB::table('events_conferen')
            ->select(
                'events_conferen.id',
                'events_conferen.name',
                'events_conferen.slug',
                'events_conferen.image',
                'events_conferen.desc'
            )
            ->where(function ($q) use ($events_id, $date) {
                if ($events_id) {
                    $q->where('events_conferen.events_id', $events_id);
                }
                if ($date) {
                    $q->whereDate('events_conferen.date', $date);
                }
            })
            ->where('events_conferen.player_type', 'youtube')
            ->where('events_conferen.status', $status)
            ->orderBy('events_conferen.id', 'asc')
            ->paginate($limit);
    }
}
