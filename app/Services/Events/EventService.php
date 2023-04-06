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
        return DB::table('payment')->where('users_id', $users_id)->where('events_id', $events_id)->first();
    }
}
