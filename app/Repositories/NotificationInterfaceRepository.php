<?php

namespace App\Repositories;

interface NotificationInterfaceRepository
{
    public function getListbyUser($users_id, $limit);
    public function saveNotif($data);
    public function readNotif($users_id);
}
