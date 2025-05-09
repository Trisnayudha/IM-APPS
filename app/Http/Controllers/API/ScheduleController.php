<?php

namespace App\Http\Controllers\API;

use App\Helpers\Notification;
use App\Http\Controllers\Controller;
use App\Jobs\SendEventReminder;
use App\Models\Events\EventsSchedule;
use App\Models\Events\EventsScheduleReserve;
use App\Services\Events\EventService;
use App\Services\Events\EventsScheduleService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ScheduleController extends Controller
{
    protected $eventService;
    public function __construct(EventService $eventService)
    {
        $this->eventService = $eventService;
    }

    public function index(Request $request)
    {
        $type = $request->type;
        $events_id = $this->eventService->getLastEvent();
        $data =  EventsScheduleService::listSchedule($events_id->id);
        $no = 1;
        $list = [];
        $prev_date = null;

        foreach ($data as $x => $row) {
            $dateToday = (new \DateTime(date('Y-m-d')));
            $dateEvents = (new \DateTime(date('Y-m-d', strtotime($row))));
            $isToday = $dateToday == $dateEvents;

            if ($prev_date && $prev_date == $row) {
                continue; // lewati jika tanggal sudah ditambahkan sebelumnya
            }

            $num = $no++;
            $row_list['id'] = $num;
            $row_list['date'] = $row;
            $row_list['isToday'] = $isToday;
            $row_list['day'] = ($num . " Day");
            $row_list['date_format'] = date('d M Y', strtotime($row));
            $list[] = $row_list;

            $prev_date = $row; // perbarui tanggal terakhir yang ditambahkan
        }

        return $list;
    }

    public function showList(Request $request)
    {
        $date = $request->date;
        $type = $request->type;
        $events_id = $this->eventService->getLastEvent();
        $data = EventsScheduleService::listScheduleByDate($date, $events_id->id, $type);
        $response['status'] = 200;
        $response['message'] = 'Show data ' . $type;
        $response['payload'] = $data;
        return response()->json($response);
    }

    public function detail(Request $request)
    {
        $schedule_id = $request->schedule_id;
        $events_id = $this->eventService->getLastEvent();
        $data = EventsScheduleService::detailSchedule($schedule_id, $events_id);
        $response['status'] = 200;
        $response['message'] = 'Show Detail Schedule';
        $response['payload'] = $data;
        return response()->json($response);
    }
    public function reserve(Request $request)
    {
        $schedule_id = $request->schedule_id;
        $id =  auth('sanctum')->user()->id ?? null;

        $findSchedule = EventsSchedule::where('id', $schedule_id)->first();
        if ($findSchedule) {
            // Menyiapkan data untuk tautan Google Calendar
            $name_schedule = $findSchedule->name . ' - Indonesia Miner 2024';
            $date_schedule = $findSchedule->date_events;
            $location_schedule = $findSchedule->location;
            $time_start = $findSchedule->time_start;
            $time_end = $findSchedule->time_end;

            // Format tanggal sesuai dengan aturan Google Calendar (YYYYMMDDTHHMMSS)
            $startDateTime = Carbon::parse($date_schedule . ' ' . $time_start)->format('Ymd\THis');
            $endDateTime = Carbon::parse($date_schedule . ' ' . $time_end)->format('Ymd\THis');

            // Membuat tautan Google Calendar dengan parameter yang sesuai
            $link = 'https://calendar.google.com/calendar/render?action=TEMPLATE';
            $link .= '&text=' . urlencode($name_schedule);
            $link .= '&dates=' . $startDateTime . '/' . $endDateTime;
            $link .= '&location=' . urlencode($location_schedule);

            $data = [
                'google_calendar' => $link
            ];
            $save = new EventsScheduleReserve();
            $save->users_id = $id;
            $save->events_schedule_id = $schedule_id;
            $save->save();

            $dateTime = Carbon::parse($date_schedule . ' ' . $time_start);

            SendEventReminder::dispatch($findSchedule->id, $id)->delay($dateTime);
            $response['status'] = 200;
            $response['message'] = 'Reserve Successfully';
            $response['payload'] = $data;
        } else {
            $response['status'] = 404;
            $response['message'] = 'Schedule Not Found';
            $response['payload'] = null;
        }
        return response()->json($response);
    }
}
