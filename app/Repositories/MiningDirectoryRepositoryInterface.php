<?php

namespace App\Repositories;

interface MiningDirectoryRepositoryInterface
{
    public function getListAllTimeline($type, $category, $search, $tags, $filter, $events_id);
}
