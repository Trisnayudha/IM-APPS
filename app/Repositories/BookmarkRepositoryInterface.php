<?php

namespace App\Repositories;

interface BookmarkRepositoryInterface
{
    public function listAll($limit, $type, $users_id, $events_id);
}
