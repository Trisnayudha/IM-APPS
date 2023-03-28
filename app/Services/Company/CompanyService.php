<?php

namespace App\Services\Company;

use App\Repositories\CompanyRepositoryInterface;
use Illuminate\Support\Facades\DB;

class CompanyService implements CompanyRepositoryInterface
{
    public function getCompanyById($id)
    {
        //
    }

    public function getCompanyBySlug($slug)
    {
        return DB::table('company')->where('slug', '=', $slug)->first();
    }

    public function getListContactById($id)
    {
        return DB::table('company_representative')
            ->select(
                'company_representative.id',
                'company_representative.name',
                'company_representative.position',
                'company_representative.image',
                'company_representative.bio as biografi',
                'company_representative.linkedin',
                'company_representative.facebook',
                'company_representative.twitter'
            )
            ->where(function ($q) use ($id) {
                if ($id) {
                    $q->where('company_representative.company_id', $id);
                }
            })
            ->orderby('company_representative.id')
            ->get();
    }
    public function getDetailContactById($id)
    {
        return DB::table('company_representative')
            ->select(
                'company_representative.id',
                'company_representative.name',
                'company_representative.position',
                'company_representative.image',
                'company_representative.bio as biografi',
                'company_representative.linkedin',
                'company_representative.facebook',
                'company_representative.twitter'
            )
            ->where(function ($q) use ($id) {
                if ($id) {
                    $q->where('company_representative.id', $id);
                }
            })
            ->first();
    }

    public function getListTimeline($type, $company, $category, $search, $tags, $filter)
    {

        if ($type == 'Product') {
            return $this->productList($company, $category, $search, $tags);
        } elseif ($type == 'Media') {
            return $this->mediaList($company, $category, $search, $tags);
        } elseif ($type == 'Project') {
            return $this->projectList($company, $category, $search, $tags);
        } elseif ($type == 'News') {
            return $this->newsList($company, $category, $search, $tags);
        } elseif ($type == 'Videos') {
            return $this->videoList($company, $category, $search, $tags);
        }
    }







    private function productList($company, $category, $search, $tags)
    {
        return DB::table('product')
            ->select(
                'product.id',
                'product.title',
                'product.slug',
                'product.image',
                'product.location',
                'product.created_at',
                'product.views',
                'company.name as company_name',
                'product_events.id as product_events_id',
                'company_sort_priority.sort as sort',
                'product.document_name',
                'product.last_update',
                'product.share'
            )
            ->leftJoin("company", function ($join) {
                $join->on('company.id', '=', 'product.company_id');
                $join->whereNotNull('product.company_id');
            })
            ->leftJoin('product_categories', function ($join) use ($category) {
                $join->on('product_categories.product_id', '=', 'product.id');
                if (!empty($category)) {
                    $join->whereIn('product_categories.product_category_id', $category);
                }
            })
            ->leftJoin('company_sort_priority', function ($join) {
                $join->on('company_sort_priority.company_type', '=', 'company.type');
            })
            ->leftJoin('product_tags_list', function ($join) {
                $join->on('product_tags_list.product_id', '=', 'product.id');
                $join->whereNotNull('product_tags_list.product_id');
            })
            ->leftJoin('product_events', function ($join) {
                $join->on('product_events.product_id', '=', 'product.id');
            })
            ->where(function ($q) use ($search, $tags, $company) {
                if (!empty($search)) {
                    $q->where('product.title', 'LIKE', '%' . $search . '%');
                }
                if (!empty($tags)) {
                    $q->whereIn('product_tags_list.product_tags_id', $tags);
                }
                if (!empty($company)) {
                    $q->where('product.company_id', $company);
                }
            })
            ->orderby('product.id', 'desc')
            ->paginate(10);
    }

    private function mediaList($company, $category, $search, $tags)
    {
        return DB::table('media_resource')
            ->select(
                'media_resource.id',
                'media_resource.title',
                'media_resource.created_at',
                'media_resource.slug',
                'media_resource.image',
                'media_resource.desc',
                'media_resource.views',
                'media_resource.location',
                'company.name as company_name',
                'media_resource.download',
                'media_resource_events.id as media_resource_events_id',
                'media_resource.document_name',
                'media_resource.last_update',
                'media_resource.share'
            )
            ->leftJoin("company", function ($join) {
                $join->on('company.id', '=', 'media_resource.company_id');
                $join->whereNotNull('company.id');
            })
            ->leftJoin('media_category_list', function ($join) use ($category) {
                $join->on('media_category_list.media_resource_id', '=', 'media_resource.id');
                if (!empty($category)) {
                    $join->whereIn('media_category_list.media_category_id', $category);
                }
            })
            ->leftJoin('media_tags_list', function ($join) {
                $join->on('media_tags_list.media_resource_id', '=', 'media_resource.id');
                $join->whereNotNull('media_tags_list.media_tags_id');
            })
            ->leftJoin('media_resource_events', function ($join) {
                $join->on('media_resource_events.media_resource_id', '=', 'media_resource.id');
            })
            ->where(
                function ($q) use ($search, $tags, $category, $company) {
                    if (!empty($search)) {
                        $q->where('media_resource.title', 'LIKE', '%' . $search . '%')
                            ->orWhere('media_resource.desc', 'LIKE', '%' . $search . '%');
                    }
                    if (!empty($tags)) {
                        $q->whereIn('media_tags_list.media_tags_id', $tags);
                    }
                    if (!empty($category)) {
                        $q->whereIn('media_resource.media_category_id', $category);
                    }
                    if (!empty($company)) {
                        $q->where('media_resource.company_id', $company);
                    }
                }
            )
            ->orderby('media_resource.id', 'desc')
            ->paginate(10);
    }

    private function projectList($company, $category, $search, $tags)
    {
        return DB::table('project')
            ->select(
                'project.id',
                'project.created_at',
                'project.views',
                'project.title',
                'project.slug',
                'project.image',
                'project.location',
                'project.desc',
                'company.name as company_name',
                'project_events.id as project_events_id',
                'project.date_project',
                'project.share'
            )
            ->leftJoin("company", function ($join) {
                $join->on('company.id', '=', 'project.company_id');
                $join->whereNotNull('project.company_id');
            })
            ->LeftJoin('project_category_list', function ($join) use ($category) {
                $join->on('project_category_list.project_id', '=', 'project.id');
                if (!empty($category)) {
                    $join->whereIn('project_category_list.project_category_id', $category);
                }
            })
            ->leftJoin('project_tags_list', function ($join) {
                $join->on('project_tags_list.project_id', '=', 'project.id');
                $join->whereNotNull('project_tags_list.project_tags_id');
            })
            ->leftJoin('project_events', function ($join) {
                $join->on('project_events.project_id', '=', 'project.id');
            })
            ->where(function ($q) use ($search, $tags, $category, $company) {
                if (!empty($search)) {
                    $q->where('project.title', 'LIKE', '%' . $search . '%')
                        ->orWhere('project.desc', 'LIKE', '%' . $search . '%');
                }
                if (!empty($tags)) {
                    $q->whereIn('project_tags_list.project_tags_id', $tags);
                }
                if (!empty($category)) {
                    $q->whereIn('project.project_category_id', $category);
                }
                if (!empty($company)) {
                    $q->where('project.company_id', $company);
                }
            })
            ->orderby('project.id', 'desc')
            ->paginate(10);
    }

    private function newsList($company, $category, $search, $tags)
    {
        return DB::table('news')
            ->select(
                'news.id',
                'news.title',
                'news.slug',
                'news.image',
                'news.location',
                'news.date_news',
                'news.views',
                'news.desc',
                'news.share',
                'news.last_update',
                'news.created_at',
                'company.name as company_name',
                'news_events.id as news_events_id'
            )
            ->leftJoin("company", function ($join) {
                $join->on('company.id', '=', 'news.company_id');
                $join->whereNotNull('company.id');
            })
            ->leftJoin('news_category_list', function ($join) use ($category) {
                $join->on('news_category_list.news_id', '=', 'news.id');
                if (!empty($category)) {
                    $join->whereIn('news_category_list.news_category_id', $category);
                }
            })
            ->leftJoin('news_tag_list', function ($join) {
                $join->on('news_tag_list.news_id', '=', 'news.id');
                $join->whereNotNull('news_tag_list.news_tag_id');
            })
            ->leftJoin('news_events', function ($join) {
                $join->on('news_events.news_id', '=', 'news.id');
            })
            ->where(function ($q) use ($search, $tags, $category, $company) {
                if (!empty($search)) {
                    $q->where('news.title', 'LIKE', '%' . $search . '%')
                        ->orWhere('news.desc', 'LIKE', '%' . $search . '%');
                }
                if (!empty($tags)) {
                    $q->whereIn('news_tag_list.news_tag_id', $tags);
                }
                if (!empty($category)) {
                    $q->whereIn('news.news_category_id', $category);
                }
                if (!empty($company)) {
                    $q->where('news.company_id', $company);
                }
                $q->where('news.flag', 'Company');
                if (!empty($company)) {
                    $q->where('news.highlight', 'Yes');
                }
            })
            ->orderby('news.id', 'desc')
            ->paginate(10);
    }

    private function videoList($company, $category, $search, $tags)
    {
        return DB::table('company_video')
            ->select(
                'company_video.id',
                'company_video.title',
                'company_video.thumbnail as image',
                'company_video.created_at',
                'company_video.is_main',
                'company_video.url',
                'company_video.views',
                'company_video.last_update',
                'company.name as company_name',
                'company_video_events.id as company_video_events_id'
            )
            ->leftJoin("company", function ($join) {
                $join->on('company.id', '=', 'company_video.company_id');
                $join->whereNotNull('company_video.company_id');
            })
            ->where(function ($q) use ($search, $company) {
                if ($search) {
                    $q->where('company_video.title', 'LIKE', '%' . $search . '%');
                }
                if ($company) {
                    $q->where('company_video.company_id', $company);
                }
                // $q->where('company_video.is_main', '<>', 1);
            })
            ->leftJoin('company_video_events', function ($join) {
                $join->on('company_video_events.company_video_id', '=', 'company_video.id');
            })
            ->orderby('company_video.id', 'desc')
            ->paginate(12);
    }
}
