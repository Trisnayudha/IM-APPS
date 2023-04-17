<?php

namespace App\Services\Events;

use Illuminate\Support\Facades\DB;

class EventsScheduleService
{
    public static function detailForm($id)
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

    public static function listSchedule($id)
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
    public static function listWorkshop($id)
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

    public static function listScheduleByDate($id, $date, $events_id = null)
    {
        return DB::table('events_schedule')
            ->select(
                'events_schedule.id',
                'events_schedule.name',
                'events_schedule.time_start',
                'events_schedule.time_end',
                'events_schedule.timezone',
                'events_schedule.location',
                'md_sponsor.image as sponsor_image',
                'events_schedule.desc'
            )
            ->leftJoin('md_sponsor', function ($join) {
                $join->on('md_sponsor.id', '=', 'events_schedule.md_sponsor_id');
            })
            ->where(function ($q) use ($id, $date, $events_id) {
                if (!empty($id)) {
                    $q->where('events_schedule.events_id', $id);
                }
                if (!empty($date)) {
                    $q->whereDate('events_schedule.date_events', $date);
                }
                if (!empty($events_id)) {
                    $q->where('events_schedule.events_id', $events_id);
                }
            })
            ->where('events_schedule.status', '=', 'schedule')
            ->orderby('events_schedule.time_start', 'asc')
            ->groupBy('events_schedule.id')
            ->get();
    }
    public static function listWorkshopByDate($id, $date, $events_id = null)
    {
        return DB::table('events_schedule')
            ->select(
                'events_schedule.id',
                'events_schedule.name',
                'events_schedule.time_start',
                'events_schedule.time_end',
                'events_schedule.timezone',
                'events_schedule.location',
                'md_sponsor.image as sponsor_image',
                'events_schedule.desc'
            )
            ->leftJoin('md_sponsor', function ($join) {
                $join->on('md_sponsor.id', '=', 'events_schedule.md_sponsor_id');
            })
            ->where(function ($q) use ($id, $date, $events_id) {
                if (!empty($id)) {
                    $q->where('events_schedule.events_id', $id);
                }
                if (!empty($date)) {
                    $q->whereDate('events_schedule.date_events', $date);
                }
                if (!empty($events_id)) {
                    $q->where('events_schedule.events_id', $events_id);
                }
            })
            ->where('events_schedule.status', '=', 'workshop')
            ->orderby('events_schedule.time_start', 'asc')
            ->groupBy('events_schedule.id')
            ->get();
    }
}
