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
}
