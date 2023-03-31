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

    public function postBookmark($users_id, $bookmark_id, $type);
    public function getDetailFormSuggest($id);
    public function postSendMeet($company_id, $delegation_id, $category_suggest_id, $message, $users_id);
}
