<?php

namespace App\Repositories;

interface SponsorRepositoryInterface
{
    public function getSponsorsType($type);
    public function getLandyark();
    public function getSupporting();
    public function getMedia();

    //Get Detail
    public function getDetailSponsorPremium($slug);
    public function getDetailSponsorFree($id, $type);
}
