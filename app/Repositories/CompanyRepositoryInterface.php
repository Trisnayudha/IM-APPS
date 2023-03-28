<?php

namespace App\Repositories;

interface CompanyRepositoryInterface
{
    //Company
    public function getCompanyById($id);
    public function getCompanyBySlug($slug);

    //Contact
    public function getListContactById($id);
    public function getDetailContactById($id);

    //timeline
    public function getListTimeline($type, $id, $category, $search, $tags, $filter);
}
