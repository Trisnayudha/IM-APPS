<?php

namespace App\Jobs;

use App\Helpers\Notification;
use App\Models\Events\EventsSchedule;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendEventReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $eventId;
    protected $userId; // Tambahkan baris ini

    public function __construct($eventId, $userId) // Tambahkan parameter $userId
    {
        $this->eventId = $eventId;
        $this->userId = $userId; // Simpan userId sebagai property
    }

    public function handle()
    {
        $event = EventsSchedule::find($this->eventId);
        if (!$event) {
            return;
        }

        $notif = new Notification();
        $notif->id = $this->userId; // Gunakan userId yang disimpan
        $message = 'Reminder Session : ' . $event->name . ' - Indonesia Miner 2024';
        $short_message = substr($message, 0, 100);
        $notif->message = $short_message;
        // Gabungkan date_events dan time_start
        $dateTimeString = $event->date_events . ' ' . $event->time_start;

        // Buat objek DateTime dan atur timezone ke GMT
        $dateTime = new \DateTime($dateTimeString, new \DateTimeZone('Asia/Jakarta')); // Sesuaikan timezone jika diperlukan
        $dateTime->setTimezone(new \DateTimeZone('GMT'));

        // Format tanggal sesuai dengan format yang diinginkan
        $formattedDate = $dateTime->format('Y-m-d H:i:s \G\M\T');

        $notif->date = $formattedDate;
        $notif->NotifAppTimer();
    }
}
