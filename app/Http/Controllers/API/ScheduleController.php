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
        $id = auth('sanctum')->user()->id ?? null;

        if (!$id) {
            return response()->json([
                'status' => 401,
                'message' => 'Unauthorized',
                'payload' => null
            ], 401);
        }

        $findSchedule = EventsSchedule::where('id', $schedule_id)->first();

        if (!$findSchedule) {
            return response()->json([
                'status' => 404,
                'message' => 'Schedule Not Found',
                'payload' => null
            ], 404);
        }

        // ✅ Cek apakah sudah pernah reserve
        $alreadyReserved = EventsScheduleReserve::where('users_id', $id)
            ->where('events_schedule_id', $schedule_id)
            ->exists();

        // Siapkan Google Calendar link (tetap dikirim)
        $name_schedule = $findSchedule->name . ' - Indonesia Miner 2026';
        $date_schedule = $findSchedule->date_events;
        $location_schedule = $findSchedule->location;
        $time_start = $findSchedule->time_start;
        $time_end = $findSchedule->time_end;

        $startDateTime = Carbon::parse($date_schedule . ' ' . $time_start)->format('Ymd\THis');
        $endDateTime = Carbon::parse($date_schedule . ' ' . $time_end)->format('Ymd\THis');

        $link = 'https://calendar.google.com/calendar/render?action=TEMPLATE';
        $link .= '&text=' . urlencode($name_schedule);
        $link .= '&dates=' . $startDateTime . '/' . $endDateTime;
        $link .= '&location=' . urlencode($location_schedule);

        $data = [
            'google_calendar' => $link
        ];

        if (!$alreadyReserved) {
            // ✅ Save hanya kalau belum pernah reserve
            EventsScheduleReserve::create([
                'users_id' => $id,
                'events_schedule_id' => $schedule_id,
            ]);

            $dateTime = Carbon::parse($date_schedule . ' ' . $time_start);

            // Optional: hanya dispatch kalau belum reserve
            SendEventReminder::dispatch($findSchedule->id, $id)->delay($dateTime);
        }

        return response()->json([
            'status' => 200,
            'message' => $alreadyReserved
                ? 'Already Reserved'
                : 'Reserve Successfully',
            'payload' => $data
        ]);
    }

    public function listReserved(Request $request)
    {
        $userId = auth('sanctum')->id();
        if (!$userId) {
            return response()->json([
                'status' => 401,
                'message' => 'Unauthorized',
                'payload' => []
            ], 401);
        }

        $date = $request->date;
        $event = $this->eventService->getLastEvent();

        // ambil schedule_id yang sudah di-reserve user
        $reservedScheduleIds = EventsScheduleReserve::where('users_id', $userId)
            ->pluck('events_schedule_id')
            ->toArray();

        if (empty($reservedScheduleIds)) {
            return response()->json([
                'status' => 200,
                'message' => 'No reserved schedule',
                'payload' => []
            ]);
        }

        $data = EventsScheduleService::listReservedScheduleByDate(
            $date,
            $event->id,
            $userId
        );

        return response()->json([
            'status' => 200,
            'message' => 'Show reserved schedule',
            'payload' => $data
        ]);
    }
}
