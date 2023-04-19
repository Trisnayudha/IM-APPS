<?php

namespace App\Services\Events;

use Illuminate\Support\Facades\DB;
use App\Traits\Directory;

class EventsScheduleService
{
    use Directory;
    public function detailForm($id)
    {
        return new static(DB::table('events_schedule')
            ->select(
                'events_schedule.id',
                'events_schedule.id',
                'events_schedule.name',
                'events_schedule.status',
                'events_schedule.date_events',
                'events_schedule.time_start',
                'events_schedule.time_end',
                'events_schedule.desc',
                'events.name as events_name',
                'md_sponsor.name as sponsor_name'
            )
            ->leftJoin('events', function ($join) {
                $join->on('events.id', '=', 'events_schedule.events_id');
            })
            ->leftJoin('md_sponsor', function ($join) {
                $join->on('md_sponsor.id', '=', 'events_schedule.md_sponsor_id');
            })
            ->where('events_schedule.id', $id)
            ->first());
    }

    public function listSchedule($id)
    {
        return DB::table('events_schedule')
            ->select(
                'events_schedule.events_id',
                'events_schedule.date_events as date'
            )
            ->where(function ($q) use ($id) {
                if (!empty($id)) {
                    $q->where('events_schedule.events_id', $id);
                }
            })
            ->where('events_schedule.status', '=', 'schedule')
            // ->groupBy('events_schedule.date_events')
            ->pluck('date')
            ->toArray();
    }
    public function listWorkshop($id)
    {
        return DB::table('events_schedule')
            ->select(
                'events_schedule.events_id',
                'events_schedule.date_events as date'
            )
            ->where(function ($q) use ($id) {
                if (!empty($id)) {
                    $q->where('events_schedule.events_id', $id);
                }
            })
            ->where('events_schedule.status', '=', 'workshop')
            ->groupBy('events_schedule.date_events')
            ->pluck('date')
            ->toArray();
    }

    public function listScheduleByDate($date, $events_id = null, $type)
    {
        $data = DB::table('events_schedule')
            ->select(
                'events_schedule.id',
                'events_schedule.name',
                'events_schedule.time_start',
                'events_schedule.time_end',
                'events_schedule.timezone as status',
                'events_schedule.location',
                'md_sponsor.image as sponsor_image',
                'events_schedule.desc'
            )
            ->leftJoin('md_sponsor', function ($join) {
                $join->on('md_sponsor.id', '=', 'events_schedule.md_sponsor_id');
            })
            ->where(function ($q) use ($date, $events_id) {
                if (!empty($date)) {
                    $q->whereDate('events_schedule.date_events', $date);
                }
                if (!empty($events_id)) {
                    $q->where('events_schedule.events_id', $events_id);
                }
            })
            ->where('events_schedule.status', '=', $type)
            ->orderby('events_schedule.time_start', 'asc')
            ->get();

        foreach ($data as $x => $row) {
            $row->sponsor_image = (!empty($row->sponsor_image) ? $row->sponsor_image : '');
            $row->time_start = (!empty($row->time_start) ? date('H:i A', strtotime($row->time_start)) : '');
            $row->time_end = (!empty($row->time_end) ? date('H:i A', strtotime($row->time_end)) : '');
            $row->isBookmark = self::isBookmark('Conference Agenda', $row->id, $events_id);
            $row->speaker = EventsSpeakerService::listSpeakerSchedule($row->id);
        }
        return $data;
    }
}
