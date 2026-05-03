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
        return DB::table('company')
            ->leftJoin('company_video', 'company_video.company_id', 'company.id')
            ->leftJoin('events_company', 'events_company.company_id', 'company.id')
            ->select('company.*', 'company_video.url', 'company.number_booth as no_both')
            ->where('slug', '=', $slug)->first();
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

        if ($type == 'Timeline') {
            return $this->timelineList($company);
        } elseif ($type == 'Product') {
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


    public function getDetailNews($slug, $users_id)
    {
        $db = DB::table('news')->where('slug', '=', $slug)->first();
        $check_book = DB::table('news_bookmark')->where('news_id', '=', $db->id)->where('users_id', '=', $users_id)->first();
        $db->bookmark = $check_book ? 1 : 0;
        return $db;
    }
    public function getDetailProduct($slug, $users_id)
    {
        $db = DB::table('product')->where('slug', '=', $slug)->first();
        $check_book = DB::table('product_bookmark')->where('product_id', '=', $db->id)->where('users_id', '=', $users_id)->first();
        $db->bookmark = $check_book ? 1 : 0;
        return $db;
    }
    public function getDetailProject($slug, $users_id)
    {
        $db = DB::table('project')->where('slug', '=', $slug)->first();
        $check_book = DB::table('project_bookmark')->where('project_id', '=', $db->id)->where('users_id', '=', $users_id)->first();
        $db->bookmark = $check_book ? 1 : 0;
        return $db;
    }
    public function getDetailMedia($slug, $users_id)
    {
        $db = DB::table('media_resource')->where('slug', '=', $slug)->first();
        $check_book = DB::table('media_bookmark')->where('media_resource_id', '=', $db->id)->where('users_id', '=', $users_id)->first();
        $db->bookmark = $check_book ? 1 : 0;
        return $db;
    }


    public function getRelateNews($slug)
    {
        $news = DB::table('news')->where('slug', '=', $slug)->first();
        $news_id = $news->id;
        $company_id = $news->company_id;
        return DB::table('news')
            ->select(
                'news.id',
                'news.title',
                'news.slug',
                'news.image',
                'news.location',
                'news.date_news',
                // 'news.desc',
                'news.views'
            )
            ->where(function ($q) use ($news_id, $company_id) {
                if (!empty($news_id)) {
                    $q->where('news.id', '!=', $news_id);
                }
                if (!empty($company_id)) {
                    $q->where('news.company_id', $company_id);
                    $q->where('news.flag', 'Company');
                    $q->where('news.highlight', 'Yes');
                } else {
                    $q->where('news.flag', 'Portal');
                }
            })
            ->inRandomOrder()
            ->orderby('news.id', 'desc')
            ->limit(4)
            ->get();
    }

    public function getRelateProduct($slug)
    {
        $product = DB::table('product')->where('slug', '=', $slug)->first();
        $id = $product->id;
        $company_id = $product->company_id;

        return DB::table('product')
            ->select(
                'product.id',
                'product.title',
                'product.slug',
                'product.image',
                'product.location',
                // 'company.name as company_name',
                'product.company_id',
                'product.created_at',
                'product.views'
            )

            ->where(function ($q) use ($id, $company_id) {
                if (!empty($id)) {
                    $q->where('product.id', '!=', $id);
                }
                if (!empty($company_id)) {
                    $q->where('product.company_id', $company_id);
                }
            })
            ->inRandomOrder()
            ->orderby('product.id', 'desc')
            ->limit(4)
            ->get();
    }
    public function getRelateProject($slug)
    {

        $project =  DB::table('project')->where('slug', '=', $slug)->first();
        $id = $project->id;
        $company_id = $project->company_id;
        return DB::table('project')
            ->select(
                'project.id',
                'project.title',
                'project.slug',
                'project.image',
                'project.location',
                'project.desc',
                'project.created_at',
                'project.views'
            )
            ->where(function ($q) use ($id, $company_id) {
                if (!empty($id)) {
                    $q->where('project.id', '!=', $id);
                }
                if (!empty($company_id)) {
                    $q->where('project.company_id', '=', $company_id);
                }
            })
            ->inRandomOrder()
            ->orderby('project.id', 'desc')
            ->limit(4)
            ->get();
    }
    public function getRelateMedia($slug)
    {

        $media = DB::table('media_resource')->where('slug', '=', $slug)->first();
        $id = $media->id;
        $company_id = $media->company_id;
        return DB::table('media_resource')
            ->select(
                'media_resource.id',
                'media_resource.title',
                'media_resource.slug',
                'media_resource.image',
                'media_resource.desc',
                'media_resource.views',
                'media_resource.download',
                'media_resource.location',
                'media_resource.created_at'
            )
            ->leftJoin("company", function ($join) {
                $join->on('company.id', '=', 'media_resource.company_id');
                $join->whereNotNull('company.id');
            })
            ->leftJoin('media_tags_list', function ($join) {
                $join->on('media_tags_list.media_resource_id', '=', 'media_resource.id');
                $join->whereNotNull('media_tags_list.media_tags_id');
            })
            ->where(function ($q) use ($id, $company_id) {
                if (!empty($id)) {
                    $q->where('media_resource.id', '!=', $id);
                }
                if (!empty($company_id)) {
                    $q->where('media_resource.company_id', '=', $company_id);
                }
            })
            ->inRandomOrder()
            ->orderby('media_resource.id', 'desc')
            ->limit(4)
            ->get();
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

    private function timelineList($company)
    {
        $productQuery = DB::table('product')
            ->selectRaw("
                id,
                'Product' as flag,
                id as target_id,
                title,
                image,
                COALESCE(date_product, created_at) as date_timeline,
                `desc`,
                views,
                0 as download,
                slug as path_link,
                slug
            ")
            ->where('company_id', $company);

        $mediaQuery = DB::table('media_resource')
            ->selectRaw("
                id,
                'Media Resource' as flag,
                id as target_id,
                title,
                image,
                COALESCE(date_media, created_at) as date_timeline,
                `desc`,
                views,
                COALESCE(download, 0) as download,
                slug as path_link,
                slug
            ")
            ->where('company_id', $company);

        $projectQuery = DB::table('project')
            ->selectRaw("
                id,
                'Project' as flag,
                id as target_id,
                title,
                image,
                COALESCE(date_project, created_at) as date_timeline,
                `desc`,
                views,
                COALESCE(download, 0) as download,
                slug as path_link,
                slug
            ")
            ->where('company_id', $company);

        $newsQuery = DB::table('news')
            ->selectRaw("
                id,
                'News' as flag,
                id as target_id,
                title,
                image,
                COALESCE(date_news, created_at) as date_timeline,
                `desc`,
                views,
                0 as download,
                slug as path_link,
                slug
            ")
            ->where('company_id', $company)
            ->where('flag', 'Company')
            ->where('highlight', 'Yes');

        $query = $productQuery
            ->unionAll($mediaQuery)
            ->unionAll($projectQuery)
            ->unionAll($newsQuery);

        $result = DB::query()
            ->fromSub($query, 'timeline')
            ->orderBy('date_timeline', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(5);

        foreach ($result as $x => $row) {
            $result[$x] = (object) [
                "id" => $row->id,
                "flag" => $row->flag,
                "target_id" => $row->target_id,
                "title" => (strlen($row->title) > 100 ? substr($row->title, 0, 100) . '...' : $row->title),
                "date_timeline" => (!empty($row->date_timeline) ? date('d M Y, H:i A', strtotime($row->date_timeline)) : ''),
                "image" => $row->image,
                "views" => (!empty($row->views) ? $row->views : 0),
                "download" => (!empty($row->download) ? $row->download : 0),
                "desc" => (strlen(strip_tags($row->desc)) > 300 ? substr(strip_tags($row->desc), 0, 300) . '...' : strip_tags($row->desc)),
                "path_link" => $row->path_link,
                'slug' => $row->slug
            ];
        }

        return $result;
    }

    public function postBookmark($users_id, $bookmark_id, $type, $events_id)
    {
        if ($type == 'Project') {
            $check_book = DB::table('project_bookmark')
                ->where('project_id', '=', $bookmark_id)
                ->where('users_id', '=', $users_id)
                ->where('events_id', '=', $events_id)
                ->first();

            if ($check_book) {
                DB::table('project_bookmark')
                    ->where('project_id', '=', $bookmark_id)
                    ->where('users_id', '=', $users_id)
                    ->where('events_id', '=', $events_id)
                    ->delete();
                $message = "Bookmark successfully removed";
            } else {
                DB::table('project_bookmark')->insert([
                    'project_id' => $bookmark_id,
                    'users_id' => $users_id,
                    'events_id' => $events_id
                ]);
                $message = "Bookmark successfully added";
            }
        } elseif ($type == 'Product') {
            $check_book = DB::table('product_bookmark')
                ->where('product_id', '=', $bookmark_id)
                ->where('users_id', '=', $users_id)
                ->where('events_id', '=', $events_id)
                ->first();

            if ($check_book) {
                DB::table('product_bookmark')
                    ->where('product_id', '=', $bookmark_id)
                    ->where('users_id', '=', $users_id)
                    ->where('events_id', '=', $events_id)
                    ->delete();
                $message = "Bookmark successfully removed";
            } else {
                DB::table('product_bookmark')->insert([
                    'product_id' => $bookmark_id,
                    'users_id' => $users_id,
                    'events_id' => $events_id
                ]);
                $message = "Bookmark successfully added";
            }
        } elseif ($type == 'Media') {
            $check_book = DB::table('media_bookmark')
                ->where('media_resource_id', '=', $bookmark_id)
                ->where('users_id', '=', $users_id)
                ->where('events_id', '=', $events_id)
                ->first();

            if ($check_book) {
                DB::table('media_bookmark')
                    ->where('media_resource_id', '=', $bookmark_id)
                    ->where('users_id', '=', $users_id)
                    ->where('events_id', '=', $events_id)
                    ->delete();
                $message = "Bookmark successfully removed";
            } else {
                DB::table('media_bookmark')->insert([
                    'media_resource_id' => $bookmark_id,
                    'users_id' => $users_id,
                    'events_id' => $events_id
                ]);
                $message = "Bookmark successfully added";
            }
        } elseif ($type == 'News') {
            $check_book = DB::table('news_bookmark')
                ->where('news_id', '=', $bookmark_id)
                ->where('users_id', '=', $users_id)
                ->where('events_id', '=', $events_id)
                ->first();

            if ($check_book) {
                DB::table('news_bookmark')
                    ->where('news_id', '=', $bookmark_id)
                    ->where('users_id', '=', $users_id)
                    ->where('events_id', '=', $events_id)
                    ->delete();
                $message = "Bookmark successfully removed";
            } else {
                DB::table('news_bookmark')->insert([
                    'news_id' => $bookmark_id,
                    'users_id' => $users_id,
                    'events_id' => $events_id
                ]);
                $message = "Bookmark successfully added";
            }
        } elseif ($type == 'Company') {
            $check_book = DB::table('company_bookmark')
                ->where('company_id', '=', $bookmark_id)
                ->where('users_id', '=', $users_id)
                ->first();

            if ($check_book) {
                DB::table('company_bookmark')
                    ->where('company_id', '=', $bookmark_id)
                    ->where('users_id', '=', $users_id)
                    ->delete();
                $message = "Bookmark successfully removed";
            } else {
                DB::table('company_bookmark')->insert([
                    'company_id' => $bookmark_id,
                    'users_id' => $users_id,

                ]);
                $message = "Bookmark successfully added";
            }
        } elseif ($type == 'Networking') {
            $check_book = DB::table('networking_bookmark')
                ->where('users_delegate_id', '=', $bookmark_id)
                ->where('users_id', '=', $users_id)
                ->where('events_id', '=', $events_id)
                ->first();

            if ($check_book) {
                DB::table('networking_bookmark')
                    ->where('users_delegate_id', '=', $bookmark_id)
                    ->where('users_id', '=', $users_id)
                    ->where('events_id', '=', $events_id)
                    ->delete();
                $message = "Bookmark successfully removed";
            } else {
                DB::table('networking_bookmark')->insert([
                    'users_delegate_id' => $bookmark_id,
                    'users_id' => $users_id,
                    'events_id' => $events_id
                ]);
                $message = "Bookmark successfully added";
            }
        } elseif ($type == 'Conference') {
            $check_book = DB::table('conference_bookmark')
                ->where('events_conferen_id', '=', $bookmark_id)
                ->where('users_id', '=', $users_id)
                ->where('events_id', '=', $events_id)
                ->first();

            if ($check_book) {
                DB::table('conference_bookmark')
                    ->where('events_conferen_id', '=', $bookmark_id)
                    ->where('users_id', '=', $users_id)
                    ->where('events_id', '=', $events_id)
                    ->delete();
                $message = "Bookmark successfully removed";
            } else {
                DB::table('conference_bookmark')->insert([
                    'events_conferen_id' => $bookmark_id,
                    'users_id' => $users_id,
                    'events_id' => $events_id
                ]);
                $message = "Bookmark successfully added";
            }
        } elseif ($type == 'Conference Agenda') {
            $check_book = DB::table('conference_agenda_bookmark')
                ->where('events_schedule_id', '=', $bookmark_id)
                ->where('users_id', '=', $users_id)
                ->where('events_id', '=', $events_id)
                ->first();

            if ($check_book) {
                DB::table('conference_agenda_bookmark')
                    ->where('events_schedule_id', '=', $bookmark_id)
                    ->where('users_id', '=', $users_id)
                    ->where('events_id', '=', $events_id)
                    ->delete();
                $message = "Bookmark successfully removed";
            } else {
                DB::table('conference_agenda_bookmark')->insert([
                    'events_schedule_id' => $bookmark_id,
                    'users_id' => $users_id,
                    'events_id' => $events_id
                ]);
                $message = "Bookmark successfully added";
            }
        }

        return $message;
    }

    public function postSendMeet($company_id, $delegation_id, $category_suggest_id, $message, $users_id)
    {
        //
    }


    public function getDetailFormSuggest($id)
    {
        return DB::table('company_suggest_meet')
            ->select(
                'company_suggest_meet.id',
                'company_suggest_meet.company_id',
                'company.name as company_name',
                'company.name_pic as company_name_pic',
                'company.email as company_email',
                'company_suggest_meet.users_id',
                'users.name as users_name',
                'users.company_name as users_company_name',
                'users.job_title as users_job_title',
                'company_suggest_meet.md_category_suggest_meet_id',
                'md_category_suggest_meet.name as category_name',
                'company_suggest_meet.company_representative_id',
                'company_representative.name as representative_name',
                'company_representative.email as representative_email',
                'company_suggest_meet.message'
            )
            ->leftJoin('users', function ($join) {
                $join->on('users.id', '=', 'company_suggest_meet.users_id');
                $join->whereNotNull('company_suggest_meet.users_id');
            })
            ->leftJoin('md_category_suggest_meet', function ($join) {
                $join->on('md_category_suggest_meet.id', '=', 'company_suggest_meet.md_category_suggest_meet_id');
                $join->whereNotNull('company_suggest_meet.md_category_suggest_meet_id');
            })
            ->leftJoin('company_representative', function ($join) {
                $join->on('company_representative.id', '=', 'company_suggest_meet.company_representative_id');
            })
            ->leftJoin('company', function ($join) {
                $join->on('company.id', '=', 'company_suggest_meet.company_id');
            })
            ->where(function ($q) use ($id) {
                if ($id) {
                    $q->where('company_suggest_meet.id', $id);
                }
            })
            ->first();
    }

    public function postSendCard($company_id, $users_id)
    {
        $date_expired = date('Y-m-d H:i:s', strtotime('+15 minutes'));
        return DB::table('company_send_card')->insert([
            'flag_visit' => 'In',
            'day' => date('d'),
            'month' => date('m'),
            'year' => date('Y'),
            'company_id' => $company_id,
            'users_id' => $users_id,
            'date_expired_req' => $date_expired
        ]);
    }
}
