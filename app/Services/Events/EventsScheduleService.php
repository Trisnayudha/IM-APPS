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

    public function listReservedScheduleByDate($date, $events_id = null, $type, $userId)
    {
        $data = DB::table('events_schedule_reserve as esr')
            ->join('events_schedule as es', 'es.id', '=', 'esr.events_schedule_id')
            ->leftJoin('md_sponsor as ms', 'ms.id', '=', 'es.md_sponsor_id')
            ->select(
                'es.id',
                'es.name',
                'es.time_start',
                'es.time_end',
                'es.timezone as status',
                'es.location',
                'ms.image as sponsor_image',
                'es.desc'
            )
            ->where('esr.users_id', $userId)
            // 🔁 REFLECT BODY REQUEST
            ->when($date, function ($q) use ($date) {
                $q->whereDate('es.date_events', $date);
            })
            ->when($events_id, function ($q) use ($events_id) {
                $q->where('es.events_id', $events_id);
            })
            ->when($type, function ($q) use ($type) {
                // samakan dengan logic lama
                $q->where('es.status', $type);
                // kalau realnya pakai timezone, ganti ke:
                // $q->where('es.timezone', $type);
            })

            ->orderBy('es.time_start', 'asc')
            ->get();

        foreach ($data as $row) {
            $row->sponsor_image = $row->sponsor_image ?? '';
            $row->time_start = $row->time_start
                ? date('H:i A', strtotime($row->time_start))
                : '';
            $row->time_end = $row->time_end
                ? date('H:i A', strtotime($row->time_end))
                : '';
            $row->isBookmark = self::isBookmark('Conference Agenda', $row->id, $events_id);
            $row->speaker = EventsSpeakerService::listSpeakerSchedule($row->id);
        }

        return $data;
    }


    public function detailSchedule($schedule_id, $events_id)
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
            ->where('events_schedule.id', '=', $schedule_id)
            ->first();

        $data->sponsor_image = (!empty($data->sponsor_image) ? $data->sponsor_image : '');
        $data->time_start = (!empty($data->time_start) ? date('H:i A', strtotime($data->time_start)) : '');
        $data->time_end = (!empty($data->time_end) ? date('H:i A', strtotime($data->time_end)) : '');
        $data->isBookmark = self::isBookmark('Conference Agenda', $data->id, $events_id->id);
        $data->speaker = EventsSpeakerService::listSpeakerSchedule($data->id);

        return $data;
    }
}
