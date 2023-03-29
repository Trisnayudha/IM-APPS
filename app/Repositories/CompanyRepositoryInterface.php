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

    public function getDetailNews($slug, $users_id);
    public function getDetailProduct($slug, $users_id);
    public function getDetailProject($slug, $users_id);
    public function getDetailMedia($slug, $users_id);

    public function getRelateNews($slug);
    public function getRelateProduct($slug);
    public function getRelateProject($slug);
    public function getRelateMedia($slug);
}
