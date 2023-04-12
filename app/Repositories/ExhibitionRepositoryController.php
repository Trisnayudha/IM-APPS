<?php

namespace App\Repositories;

interface ExhibitionRepositoryController
{
    public function listAll($events_id, $search, $category, $special_tags, $filter = null, $users_id);
}
