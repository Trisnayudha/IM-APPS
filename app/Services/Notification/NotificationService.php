<?php

namespace App\Services\Notification;

use App\Models\Notification\UsersNotification;
use App\Repositories\NotificationInterfaceRepository;

class NotificationService implements NotificationInterfaceRepository
{
    public function getListbyUser($users_id, $limit = 10)
    {
        return UsersNotification::where('users_id', $users_id)->paginate($limit);
    }

    public function saveNotif($data)
    {
        //
        $save = new UsersNotification();
        $save->users_id = $data->users_id;
        $save->message = $data->message;
        $save->title = $data->title;
        $save->is_read = 1;
        $save->save();
    }

    public function readNotif($users_id)
    {
        $notifications = UsersNotification::where('users_id', $users_id)->where('is_read', '1')->get();

        if ($notifications->isNotEmpty()) {
            foreach ($notifications as $notification) {
                $notification->is_read = 0;
            }

            UsersNotification::whereIn('id', $notifications->pluck('id'))->update(['is_read' => 0]);
        }
    }
}
