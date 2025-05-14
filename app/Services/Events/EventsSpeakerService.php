<?php

namespace App\Services\Events;

use Illuminate\Support\Facades\DB;

class EventsSpeakerService
{
    public function listSpeakerSchedule($id)
    {
        return DB::table('events_schedule_speaker')
            ->select(
                'events_schedule_speaker.id',
                'events_schedule_speaker.events_speaker_id as speaker_id',
                'events_speaker.name as name',
                'events_speaker.position as position',
                'events_speaker.company_name as company',
                'events_speaker.company_image as company_image',
                'events_speaker.image as image',
                'events_speaker.bio_desc as desc',
                'events_speaker.linkedin',
                'events_speaker.twitter',
                'events_speaker.instagram'
            )
            ->leftjoin('events_speaker', function ($join) {
                $join->on('events_schedule_speaker.events_speaker_id', '=', 'events_speaker.id');
            })
            ->where('events_schedule_speaker.events_schedule_id', $id)
            ->where(function ($q) {
                $q->orWhereNotNull('events_speaker.id');
            })
            ->orderby('events_schedule_speaker.sort', 'asc')
            ->get();
    }

    public function listSpeakerEvent($event_id, $filters = [])
    {
        $now = now('Asia/Jakarta');

        $query = DB::table('events_speaker as es')
            ->join('events_schedule_speaker as ess', 'ess.events_speaker_id', '=', 'es.id')
            ->join('events_schedule as sched', 'sched.id', '=', 'ess.events_schedule_id')
            ->where('sched.events_id', $event_id)
            ->select(
                'es.id',
                'es.name',
                'es.position',
                'es.company_name',
                'es.image',
                'es.company_image',
                DB::raw("MAX(CASE
                    WHEN sched.date_events = '{$now->toDateString()}'
                         AND '{$now->toTimeString()}' BETWEEN sched.time_start AND sched.time_end
                    THEN 1 ELSE 0 END) as is_live")
            )
            ->groupBy(
                'es.id',
                'es.name',
                'es.position',
                'es.company_name',
                'es.company_image',
                'es.image'
            );

        // Filter by company name
        if (!empty($filters['company'])) {
            $query->where('es.company_name', 'like', '%' . $filters['company'] . '%');
        }

        // Filter live / no live
        if (!empty($filters['status'])) {
            if ($filters['status'] === 'live') {
                $query->having('is_live', 1);
            } elseif ($filters['status'] === 'no_live') {
                $query->having('is_live', 0);
            }
        }

        // Tambahkan filter keyword (cari di name atau position)
        if (!empty($filters['keyword'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('es.name', 'like', '%' . $filters['keyword'] . '%')
                    ->orWhere('es.position', 'like', '%' . $filters['keyword'] . '%');
            });
        }

        // Sorting
        if (!empty($filters['sorting']) && $filters['sorting'] === 'az') {
            $query->orderBy('es.name', 'asc');
        } else {
            $query->orderBy('es.id', 'desc');
        }

        // Pagination
        $perPage = $filters['per_page'] ?? 10;
        return $query->paginate($perPage);
    }


    public function getSpeakerDetailWithSchedules($speaker_id)
    {
        $now = now('Asia/Jakarta');

        // Ambil detail speaker
        $speaker = DB::table('events_speaker')
            ->where('id', $speaker_id)
            ->first();

        if (!$speaker) return null;

        // Ambil semua jadwal yang speaker ini isi
        $schedules = DB::table('events_schedule_speaker as ess')
            ->join('events_schedule as sched', 'sched.id', '=', 'ess.events_schedule_id')
            ->where('ess.events_speaker_id', $speaker_id)
            ->where('sched.events_id', '13')
            ->select(
                'sched.id',
                'sched.name',
                'sched.date_events',
                'sched.time_start',
                'sched.time_end',
                'sched.location',
                DB::raw("CASE
                WHEN sched.date_events = '{$now->toDateString()}'
                     AND '{$now->toTimeString()}' BETWEEN sched.time_start AND sched.time_end
                THEN 1 ELSE 0 END as is_live")
            )
            ->orderBy('sched.date_events', 'asc')
            ->orderBy('sched.time_start', 'asc')
            ->get();
        $data = $speaker;
        foreach ($schedules as $x => $row) {
            $row->sponsor_image = (!empty($row->sponsor_image) ? $row->sponsor_image : '');
            $row->time_start = (!empty($row->time_start) ? date('H:i A', strtotime($row->time_start)) : '');
            $row->time_end = (!empty($row->time_end) ? date('H:i A', strtotime($row->time_end)) : '');
        }
        $data->schedules = $schedules;
        return $data;
    }

    public function listSpeakerCompanies($event_id)
    {
        return DB::table('events_speaker as es')
            ->join('events_schedule_speaker as ess', 'ess.events_speaker_id', '=', 'es.id')
            ->join('events_schedule as sched', 'sched.id', '=', 'ess.events_schedule_id')
            ->where('sched.events_id', $event_id)
            ->whereNotNull('es.company_name')
            ->select('es.company_name')
            ->distinct()
            ->orderBy('es.company_name', 'asc')
            ->pluck('es.company_name');
    }
}
