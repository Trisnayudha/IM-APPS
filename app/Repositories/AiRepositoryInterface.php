<?php

namespace App\Repositories;

interface AiRepositoryInterface
{
    public function getSuggestMeet($id, $category);
    public function getRoomChat();
}
