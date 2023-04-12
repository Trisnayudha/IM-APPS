<?php

namespace App\Services\MiningDirectory;

use App\Models\Events\EventsCompany;
use App\Repositories\MiningDirectoryRepositoryInterface;
use Illuminate\Support\Facades\DB;

class MiningDirectoryService implements MiningDirectoryRepositoryInterface
{
    public function getListAllTimeline($type, $category, $search, $tags, $filter, $events_id, $users_id)
    {
        if ($type == 'Company') {
            return $this->companyList($type, $category, $search, $tags, $filter, $events_id, $users_id);
        } elseif ($type == 'Product') {
            return $this->productList($type, $category, $search, $tags, $filter, $events_id, $is_mining_directory = true, $users_id);
        } elseif ($type == 'Media') {
            return $this->mediaList($type, $category, $search, $tags, $filter, $events_id, $is_mining_directory = true, $users_id);
        } elseif ($type == 'Project') {
            return $this->projectList($type, $category, $search, $tags, $filter, $events_id, $is_mining_directory = true, $users_id);
        } elseif ($type == 'News') {
            return $this->newsList($type, $category, $search, $tags, $filter, $events_id, $is_mining_directory = true, $users_id);
        } elseif ($type == 'Videos') {
            return $this->videoList($type, $category, $search, $tags, $filter, $events_id, $is_mining_directory = true, $users_id);
        }
    }

    private function companyList($type, $category, $search, $tags, $filter, $events_id, $users_id)
    {
        $column_filter = "company.id";
        $type_filter = "desc";

        if ($filter == "sort-name-ascend") {
            $column_filter = "company.name";
            $type_filter = "asc";
        } else if ($filter == "sort-name-descend") {
            $column_filter = "company.name";
            $type_filter = "desc";
        } elseif ($filter == "sort-date-ascend") {
            $column_filter = "events_company.created_at";
            $type_filter = "asc";
        } elseif ($filter == "sort-date-descend") {
            $column_filter = "events_company.created_at";
            $type_filter = "desc";
        }
        $db = DB::table('events_company')
            ->select(
                'company.id',
                'company.name as title',
                'company.slug',
                'company.image as image',
                'company.email',
                'company.phone',
                'company.desc',
                'company.info_one as company_info_one',
                'company.info_two as company_info_second',
                'company.info_three as company_info_third',
                'company.location',
                'company_video.url as video_url',
                'company_video.thumbnail as video_thumbnail',
                'company_video.title as video_title',
                'events_company.type as sponsor_type'
            )
            ->join('company', function ($join) {
                $join->on('events_company.company_id', '=', 'company.id');
            })
            ->leftJoin('company_video', function ($join) {
                $join->on('company_video.company_id', '=', 'company.id');
                $join->where('company_video.is_main', 1);
            })
            ->where(function ($q) use ($events_id, $search, $category, $tags) {
                if (!empty($events_id)) {
                    $q->where('events_company.events_id', $events_id);
                }
                if (!empty($search)) {
                    $q->where('company.name', 'LIKE', '%' . $search . '%');
                }
                if (!empty($category)) {
                    $q->whereIn('company.ms_company_category_id', $category);
                }
                if (!empty($tags)) {
                    $q->whereIn('company_tags_list.company_tags_id', $tags);
                }
                $q->where('events_company.status', 'Active');
            })
            ->orderby($column_filter, $type_filter)
            ->paginate(10);
        foreach ($db as $item) {
            $check_book = DB::table('company_bookmark')
                ->where('company_id', '=', $item->id)
                ->where('users_id', '=', $users_id)
                ->first();
            $item->bookmark = $check_book ? 1 : 0;
        }
        return $db;
    }
    private function productList($type, $category, $search, $tags, $filter, $events_id, $is_mining_directory = true, $users_id)
    {
        $arr = EventsCompany::where('events_id', $events_id)
            ->pluck('company_id')
            ->toArray();
        // dd($users_id);
        $column_filter = "product.id";
        $type_filter = "desc";

        if ($filter == "sort-name-ascend") {
            $column_filter = "product.title";
            $type_filter = "asc";
        } else if ($filter == "sort-name-descend") {
            $column_filter = "product.title";
            $type_filter = "desc";
        } elseif ($filter == "sort-date-ascend") {
            $column_filter = "product.created_at";
            $type_filter = "asc";
        } elseif ($filter == "sort-date-descend") {
            $column_filter = "product.created_at";
            $type_filter = "desc";
        }

        $query = DB::table('product')
            ->select(
                'product.id',
                'product.title',
                'product.slug',
                'product.image',
                'product.location',
                'company.name as company_name'
            )
            ->whereIn('product.company_id', $arr)
            ->leftJoin("company", function ($join) {
                $join->on('company.id', '=', 'product.company_id');
                $join->whereNotNull('product.company_id');
            })
            // ->leftJoin('product_categories', function ($join) use ($category) {
            //     $join->on('product_categories.product_id', '=', 'product.id');
            //     if (!empty($category)) {
            //         $join->where('product_categories.product_category_id', $category);
            //     }
            // })
            // ->leftJoin('product_tags_list', function ($join) {
            //     $join->on('product_tags_list.product_id', '=', 'product.id');
            //     $join->whereNotNull('product_tags_list.product_id');
            // })
            ->leftJoin('events_company', function ($join) use ($events_id) {
                $join->on('events_company.company_id', '=', 'product.company_id');
                $join->where('events_company.events_id', $events_id);
                $join->whereNotNull('events_company.company_id');
            })
            ->where(function ($q) use ($search, $category, $tags) {
                if (!empty($search)) {
                    $q->where('product.title', 'LIKE', '%' . $search . '%');
                }
                if (!empty($tags)) {
                    $q->whereIn('product_tags_list.product_tags_id', $tags);
                }
                //                if (!empty($category)) {
                //    123                $q->whereIn('product.product_category_id', $category);
                //                }
            })
            // ->groupBy('product.id')
            ->orderby($column_filter, $type_filter);
        if ($is_mining_directory) {
            $query->join('product_events', 'product_events.product_id', '=', 'product.id');
        }
        $query = $query->paginate(10);
        foreach ($query as $item) {
            $check_book = DB::table('product_bookmark')
                ->where('product_id', '=', $item->id)
                ->where('users_id', '=', $users_id)
                ->first();
            $item->bookmark = $check_book ? 1 : 0;
        }
        return $query;
    }
    private function mediaList($type, $category, $search, $tags, $filter, $events_id, $is_mining_directory = true, $users_id)
    {
        $column_filter = "media_resource.id";
        $type_filter = "desc";

        if ($filter == "sort-name-ascend") {
            $column_filter = "media_resource.title";
            $type_filter = "asc";
        } else if ($filter == "sort-name-descend") {
            $column_filter = "media_resource.title";
            $type_filter = "desc";
        } elseif ($filter == "sort-date-ascend") {
            $column_filter = "media_resource.created_at";
            $type_filter = "asc";
        } elseif ($filter == "sort-date-descend") {
            $column_filter = "media_resource.created_at";
            $type_filter = "desc";
        }

        $arr = EventsCompany::where('events_id', $events_id)
            ->pluck('company_id')
            ->toArray();

        $query = DB::table('media_resource')
            ->select(
                'media_resource.id',
                'media_resource.title',
                'media_resource.slug',
                'media_resource.image',
                'media_resource.desc',
                'media_resource.views',
                'media_resource.download',
                'events_company.company_id'
            )
            ->whereIn('media_resource.company_id', $arr)
            ->leftJoin("company", function ($join) {
                $join->on('company.id', '=', 'media_resource.company_id');
                $join->whereNotNull('company.id');
            })
            // ->leftJoin('media_category_list', function ($join) use ($category) {
            //     $join->on('media_category_list.media_resource_id', '=', 'media_resource.id');
            //     if (!empty($category)) {
            //         $join->where('media_category_list.media_category_id', $category);
            //     }
            // })
            // ->leftJoin('media_tags_list', function ($join) {
            //     $join->on('media_tags_list.media_resource_id', '=', 'media_resource.id');
            //     $join->whereNotNull('media_tags_list.media_tags_id');
            // })
            ->leftJoin('events_company', function ($join) use ($events_id) {
                $join->on('events_company.company_id', '=', 'media_resource.company_id');
                $join->where('events_company.events_id', $events_id);
                $join->whereNotNull('events_company.company_id');
            })
            ->where(function ($q) use ($search, $tags, $category) {
                if (!empty($search)) {
                    $q->where('media_resource.title', 'LIKE', '%' . $search . '%')
                        ->orWhere('media_resource.desc', 'LIKE', '%' . $search . '%');
                }
                if (!empty($tags)) {
                    $q->whereIn('media_tags_list.media_tags_id', $tags);
                }
                //                if (!empty($category)) {
                //                    $q->whereIn('media_resource.media_category_id', $category);
                //                }
            })
            ->where(function ($q) {
                //                $q->whereNotNull('media_category_list.media_resource_id');
            })
            // ->groupBy('media_resource.id')
            ->orderby($column_filter, $type_filter);
        if ($is_mining_directory) {
            $query->join('media_resource_events', 'media_resource_events.media_resource_id', '=', 'media_resource.id');
        }
        $query = $query->paginate(10);
        foreach ($query as $item) {
            $check_book = DB::table('media_bookmark')
                ->where('media_resource_id', '=', $item->id)
                ->where('users_id', '=', $users_id)
                ->first();
            $item->bookmark = $check_book ? 1 : 0;
        }
        return $query;
    }
    private function projectList($type, $category, $search, $tags, $filter, $events_id, $is_mining_directory = true, $users_id)
    {
        $column_filter = "project.id";
        $type_filter = "desc";

        if ($filter == "sort-name-ascend") {
            $column_filter = "project.title";
            $type_filter = "asc";
        } else if ($filter == "sort-name-descend") {
            $column_filter = "project.title";
            $type_filter = "desc";
        } elseif ($filter == "sort-date-ascend") {
            $column_filter = "project.created_at";
            $type_filter = "asc";
        } elseif ($filter == "sort-date-descend") {
            $column_filter = "project.created_at";
            $type_filter = "desc";
        }
        $arr = EventsCompany::where('events_id', $events_id)
            ->pluck('company_id')
            ->toArray();

        $query = DB::table('project')
            ->select(
                'project.id',
                'project.title',
                'project.slug',
                'project.image',
                'project.location',
                'project.desc',
                'company.name as company_name'
            )
            ->whereIn('project.company_id', $arr)
            ->leftJoin("company", function ($join) {
                $join->on('company.id', '=', 'project.company_id');
                $join->whereNotNull('project.company_id');
            })
            // ->leftJoin('project_category_list', function ($join) use ($category) {
            //     $join->on('project_category_list.project_id', '=', 'project.id');
            //     if (!empty($category)) {
            //         $join->where('project_category_list.project_category_id', $category);
            //     }
            // })
            // ->leftJoin('project_tags_list', function ($join) {
            //     $join->on('project_tags_list.project_id', '=', 'project.id');
            //     $join->whereNotNull('project_tags_list.project_tags_id');
            // })
            ->leftJoin('events_company', function ($join) use ($events_id) {
                $join->on('events_company.company_id', '=', 'project.company_id');
                $join->where('events_company.events_id', $events_id);
                $join->whereNotNull('events_company.company_id');
            })
            ->where(function ($q) use ($search, $category, $tags) {
                if (!empty($search)) {
                    $q->where('project.title', 'LIKE', '%' . $search . '%')
                        ->orWhere('project.desc', 'LIKE', '%' . $search . '%');
                }
                if (!empty($tags)) {
                    $q->whereIn('project_tags_list.project_tags_id', $tags);
                }
                //                if (!empty($category)) {
                //                    $q->whereIn('project.project_category_id', $category);
                //                }
            })
            ->where(function ($q) {
                //                $q->whereNotNull('project_category_list.project_category_id');
            })
            // ->groupBy('project.id')
            ->orderby($column_filter, $type_filter);
        if ($is_mining_directory) {
            $query->join('project_events', 'project_events.project_id', '=', 'project.id');
        }
        $query = $query->paginate(10);
        foreach ($query as $item) {
            $check_book = DB::table('project_bookmark')
                ->where('project_id', '=', $item->id)
                ->where('users_id', '=', $users_id)
                ->first();
            $item->bookmark = $check_book ? 1 : 0;
        }
        return $query;
    }
    private function newsList($type, $category, $search, $tags, $filter, $events_id, $is_mining_directory = true, $users_id)
    {
        $column_filter = "news.id";
        $type_filter = "desc";

        if ($filter == "sort-name-ascend") {
            $column_filter = "news.title";
            $type_filter = "asc";
        } else if ($filter == "sort-name-descend") {
            $column_filter = "news.title";
            $type_filter = "desc";
        } elseif ($filter == "sort-date-ascend") {
            $column_filter = "news.created_at";
            $type_filter = "asc";
        } elseif ($filter == "sort-date-descend") {
            $column_filter = "news.created_at";
            $type_filter = "desc";
        }

        $arr = EventsCompany::where('events_id', $events_id)
            ->pluck('company_id')
            ->toArray();

        $query = DB::table('news')
            ->select(
                'news.id',
                'news.title',
                'news.slug',
                'news.image',
                'news.location',
                'news.date_news',
                'news.desc',
                'company.name as company_name'
            )
            ->whereIn('news.company_id', $arr)
            ->leftJoin("company", function ($join) {
                $join->on('company.id', '=', 'news.company_id');
                $join->whereNotNull('news.company_id');
            })
            // ->leftJoin('news_category_list', function ($join) use ($category) {
            //     $join->on('news_category_list.news_id', '=', 'news.id');
            //     if (!empty($category)) {
            //         $join->where('news_category_list.news_category_id', $category);
            //     }
            // })
            // ->leftJoin('news_tag_list', function ($join) {
            //     $join->on('news_tag_list.news_id', '=', 'news.id');
            //     $join->orWhereNotNull('news_tag_list.news_tag_id');
            // })
            ->leftJoin('events_company', function ($join) use ($events_id) {
                $join->on('events_company.company_id', '=', 'news.company_id');
                $join->where('events_company.events_id', $events_id);
                $join->whereNotNull('events_company.company_id');
            })
            ->where(function ($q) use ($search, $category, $tags) {
                if (!empty($search)) {
                    $q->where('news.title', 'LIKE', '%' . $search . '%')
                        ->orWhere('news.desc', 'LIKE', '%' . $search . '%');
                }
                if (!empty($tags)) {
                    $q->whereIn('news_tag_list.news_tag_id', $tags);
                }
                //                if (!empty($category)) {
                //                    $q->whereIn('news.news_category_id', $category);
                //                }
                // $q->where('news.flag', 'Company');
                // $q->where('news.highlight', 'Yes');
            })
            ->where(function ($q) use ($events_id) {
                //                $q->whereNotNull('news_category_list.news_id');
                $q->where('news_events.events_id', '=', $events_id);
            })
            // ->groupBy('news.id')
            ->orderby($column_filter, $type_filter);
        if ($is_mining_directory) {
            $query->join('news_events', 'news_events.news_id', '=', 'news.id');
        }
        $query = $query->paginate(10);
        foreach ($query as $item) {
            $check_book = DB::table('news_bookmark')
                ->where('news_id', '=', $item->id)
                ->where('users_id', '=', $users_id)
                ->first();
            $item->bookmark = $check_book ? 1 : 0;
        }
        return $query;
    }
    private function videoList($type, $category, $search, $tags, $filter, $events_id, $is_mining_directory = true, $users_id)
    {
        $column_filter = "company_video.id";
        $type_filter = "desc";

        if ($filter == "sort-name-ascend") {
            $column_filter = "company_video.title";
            $type_filter = "asc";
        } else if ($filter == "sort-name-descend") {
            $column_filter = "company_video.title";
            $type_filter = "desc";
        } elseif ($filter == "sort-date-ascend") {
            $column_filter = "company_video.created_at";
            $type_filter = "asc";
        } elseif ($filter == "sort-date-descend") {
            $column_filter = "company_video.created_at";
            $type_filter = "desc";
        }

        $arr = EventsCompany::where('events_id', $events_id)
            ->pluck('company_id')
            ->toArray();

        $query = DB::table('company_video')
            ->select(
                'company_video.id',
                'company_video.title',
                'company_video.thumbnail',
                'company_video.url',
                'events_company.company_id'
            )
            // ->whereIn('company_video.company_id',$arr)
            ->leftJoin("company", function ($join) {
                $join->on('company.id', '=', 'company_video.company_id');
                $join->whereNotNull('company.id');
            })
            // ->leftJoin('media_category_list', function ($join) use ($category) {
            //     $join->on('media_category_list.media_resource_id', '=', 'company_video.id');
            //     if (!empty($category)) {
            //         $join->where('media_category_list.media_category_id', $category);
            //     }
            // })
            ->leftJoin('events_company', function ($join) use ($events_id) {
                $join->on('events_company.company_id', '=', 'company_video.company_id');
                $join->where('events_company.events_id', $events_id);
                $join->whereNotNull('events_company.company_id');
            })
            ->where(function ($q) use ($search, $tags, $category) {
                if (!empty($search)) {
                    $q->where('company_video.title', 'LIKE', '%' . $search . '%');
                }
                if (!empty($tags)) {
                    $q->whereIn('media_tags_list.media_tags_id', $tags);
                }
                //                if (!empty($category)) {
                //                    $q->whereIn('media_resource.media_category_id', $category);
                //                }
            })
            ->where(function ($q) {
                //                $q->whereNotNull('media_category_list.media_resource_id');
            })
            // ->groupBy('company_video.id')
            ->orderby($column_filter, $type_filter);
        if ($is_mining_directory) {
            $query->join('company_video_events', 'company_video_events.company_video_id', '=', 'company_video.id');
        }
        $query = $query->paginate(10);
        foreach ($query as $item) {
            $check_book = DB::table('company_video_bookmark')
                ->where('company_video_id', '=', $item->id)
                ->where('users_id', '=', $users_id)
                ->first();
            $item->bookmark = $check_book ? 1 : 0;
        }
        return $query;
    }
}
