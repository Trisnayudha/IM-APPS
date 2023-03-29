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

    public function getDetailNews($slug);
    public function getDetailProduct($slug);
    public function getDetailProject($slug);
    public function getDetailMedia($slug);

    public function getRelateNews($slug);
    public function getRelateProduct($slug);
    public function getRelateProject($slug);
    public function getRelateMedia($slug);
}
