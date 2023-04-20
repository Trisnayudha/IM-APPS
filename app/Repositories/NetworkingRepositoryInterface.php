<?php

namespace App\Repositories;

interface NetworkingRepositoryInterface
{
    public function listAll($search, $limit, $users_id, $events_id);
    public function detailDelegate($users_id);
    public function scanUsers($codePayment);
}
