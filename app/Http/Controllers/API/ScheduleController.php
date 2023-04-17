<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\Events\EventService;
use App\Services\Events\EventsScheduleService;
use Illuminate\Http\Request;

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
}
