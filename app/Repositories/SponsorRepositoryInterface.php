<?php

namespace App\Repositories;

interface SponsorRepositoryInterface
{
    public function getSponsorsType($type);
    public function getLandyark();
    public function getSupporting();
    public function getMedia();
}
