<?php

namespace App\Repositories;

interface InboxRepositoryInterface
{
    public function getAll($users_id);
    public function detailUsers($chat_id, $users_id);
    public function arrayDate($chat_id);
    public function getMessage($date, $chat_id);
}
