<?php

namespace App\Services\Events;

use App\Models\Auth\User;
use App\Models\Events\EventsConferen;
use App\Models\Payment\Payment;
use App\Repositories\EventRepositoryInterface;
use Illuminate\Support\Facades\DB;
use App\Traits\Events;

class EventService implements EventRepositoryInterface
{
    use Events;
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
        return DB::table('payment')->join('events_tickets', 'events_tickets.id', 'payment.package_id')
            ->where('users_id', $users_id)->where('events_id', $events_id)
            ->where('aproval_quota_users', 1)->select('payment.qr_code', 'events_tickets.type as package')->first();
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
    public function saveData($find, $events_id, $type)
    {
        $findPayment = Payment::where('users_id', $find->id)->first();
        if ($findPayment) {
            $findPayment = new Payment();
        }
        $findPayment->users_id = $find->id;
        $findPayment->events_id = $events_id;
        $findPayment->package = $type;
        $findPayment->status = 'Waiting';
        $findPayment->aproval_quota_users = 0;
        $findPayment->save();
    }

    public function detailEvent($slug, $id)
    {

        $find = EventsConferen::where('slug', $slug)->first();
        //masukin log event conference
        self::countVisitConference($find->events_id, $id, $find->id);
        if ($find) {
            $bookmark = DB::table('conference_bookmark')
                ->join('events_conferen', 'events_conferen.id', 'conference_bookmark.events_conferen_id')
                ->where('conference_bookmark.users_id', $id)
                ->where('conference_bookmark.events_conferen_id', $find->id)->first();
            $find->isBookmark = $bookmark ? 1 : 0;
            $find->speakers = DB::table('events_conferen_speaker')
                ->select(
                    'events_conferen_speaker.id as table_id',
                    'events_conferen_speaker.events_speaker_id as id',
                    'events_speaker.name as name',
                    'events_speaker.position as position',
                    'events_speaker.company_name as company_name',
                    'events_speaker.image as image',
                    'events_speaker.bio_desc as bio_desc'
                )
                ->leftjoin('events_speaker', function ($join) {
                    $join->on('events_conferen_speaker.events_speaker_id', '=', 'events_speaker.id');
                })
                ->where('events_conferen_speaker.events_conferen_id', $find->id)
                ->where(function ($q) {
                    $q->orWhereNotNull('events_speaker.id');
                })
                ->orderby('events_speaker.id', 'asc')
                ->get();

            $find->file = DB::table('events_conferen_file')
                ->select(
                    'events_conferen_file.id as table_id',
                    'events_conferen_file.file as file',
                    'events_conferen_file.namefile as namefile',
                    'events_conferen_file.extension as extension'
                )
                ->where('events_conferen_file.events_conferen_id', $find->id)
                ->orderby('events_conferen_file.id', 'asc')
                ->get();
        }

        return $find;
    }

    public function findUser($qrcode)
    {
        $find = Payment::where('code_payment', $qrcode)->where('events_id', '12')->first();
        if ($find) {
            $findUser = User::where('id', $find->users_id)->first();

            return $findUser;
        }
        return false;
    }
}
