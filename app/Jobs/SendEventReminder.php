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
        $notif->NotifApp();
    }
}
