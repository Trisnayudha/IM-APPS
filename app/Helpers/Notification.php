<?php

namespace App\Helpers;

use App\Models\Notification\UsersNotification;
use Exception;
use Ladumor\OneSignal\OneSignal;

class Notification
{
    public $id;
    public $message;
    public $res;
    public $date;
    public function NotifApp()
    {
        try {
            $id = $this->id;
            $message = $this->message;
            $fields['include_external_user_ids'] = ['external_user_id_' . $id];

            $notif = OneSignal::sendPush($fields, $message);
            $save = new UsersNotification();
            $save->message = $message;
            $save->users_id = $id;
            $save->save();
            return $this->res = $notif;
        } catch (\Exception $th) {
            return $this->res = $th->getMessage();
        }
    }

    public function NotifAppTimer()
    {
        try {
            $id = $this->id;
            $message = $this->message;
            $fields['include_external_user_ids'] = ['external_user_id_' . $id];
            $fields['send_after'] = $this->date;
            $notif = OneSignal::sendPush($fields, $message);
            $save = new UsersNotification();
            $save->message = $message;
            $save->users_id = $id;
            $save->save();
            return $this->res = $notif;
        } catch (\Exception $e) {
            return $this->res = $e->getMessage();
        }
    }
}
