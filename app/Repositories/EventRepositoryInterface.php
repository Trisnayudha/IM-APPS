<?php

namespace App\Repositories;

interface EventRepositoryInterface
{
    //
    public function getEventId($id);
    public function getEventSlug($slug);
    public function getLastEvent();

    public function getCheckPayment($users_id, $events_id);
}