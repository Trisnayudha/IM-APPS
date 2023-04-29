<?php

namespace App\Helpers;

use Exception;
use Ladumor\OneSignal\OneSignal;

class Notification
{
    public $id;
    public $message;
    public $res;
    public function NotifApp()
    {
        try {
            $id = $this->id;
            $message = $this->message;
            $fields['include_external_user_ids'] = ['external_user_id_' . $id];

            $notif = OneSignal::sendPush($fields, $message);

            return $notif;
        } catch (\Exception $th) {
            return $this->res = $th->getMessage();
        }
    }
}
