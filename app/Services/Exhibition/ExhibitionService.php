<?php

namespace App\Services\Exhibition;

use App\Repositories\ExhibitionRepositoryController;
use Illuminate\Support\Facades\DB;

class ExhibitionService implements ExhibitionRepositoryController
{
    public function listAll($events_id, $search, $category, $special_tags, $filter = null, $users_id)
    {
        $column_filter = "events_company.sort";
        $type_filter = "asc";

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
            ->where(function ($q) use ($events_id, $search, $category, $special_tags) {
                if (!empty($events_id)) {
                    $q->where('events_company.events_id', $events_id);
                }
                if (!empty($search)) {
                    $q->where('company.name', 'LIKE', '%' . $search . '%');
                }
                if (!empty($category)) {
                    $q->where('company.ms_company_category_id', $category);
                }
                if (!empty($special_tags)) {
                    $q->whereIn('company_tags_list.company_tags_id', $special_tags);
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

    public function listAllByType($events_id, $search, $category, $special_tags, $filter = null, $users_id, $sponsor_type, $limit = 10)
    {
        $column_filter = "events_company.sort";
        $type_filter = "asc";

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

        // Mulai query
        $query = DB::table('events_company')
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
            ->join('company', 'events_company.company_id', '=', 'company.id')
            ->leftJoin('company_video', function ($join) {
                $join->on('company_video.company_id', '=', 'company.id')
                    ->where('company_video.is_main', '=', 1);
            })
            ->where('events_company.events_id', '=', $events_id)
            ->where('events_company.type', '=', $sponsor_type)
            ->where('events_company.status', '=', 'Active');

        if (!empty($search)) {
            $query->where('company.name', 'LIKE', "%$search%");
        }
        if (!empty($category)) {
            $query->where('company.ms_company_category_id', '=', $category);
        }
        if (!empty($special_tags)) {
            // Pastikan logic untuk filter ini sesuai dengan struktur database Anda
            $query->whereIn('company_tags_list.company_tags_id', $special_tags);
        }

        $query->orderBy($column_filter, $type_filter);

        // Terapkan limit
        $results = $query->limit($limit)->get();

        // Tambahkan informasi bookmark
        foreach ($results as $item) {
            $check_book = DB::table('company_bookmark')
                ->where('company_id', '=', $item->id)
                ->where('users_id', '=', $users_id)
                ->first();
            $item->bookmark = $check_book ? 1 : 0;
        }

        return $results;
    }

    public function getTotalByType($events_id, $sponsor_type, $search, $category, $special_tags)
    {
        $query = DB::table('events_company')
            ->join('company', 'events_company.company_id', '=', 'company.id')
            ->where('events_company.events_id', '=', $events_id)
            ->where('events_company.type', '=', $sponsor_type)
            ->where('events_company.status', '=', 'Active');

        if (!empty($search)) {
            $query->where('company.name', 'LIKE', "%$search%");
        }
        if (!empty($category)) {
            $query->where('company.ms_company_category_id', '=', $category);
        }
        if (!empty($special_tags)) {
            // Asumsikan Anda memiliki tabel company_tags_list untuk relasi
            // Pastikan logic ini sesuai dengan struktur database Anda
            $query->join('company_tags_list', 'company.id', '=', 'company_tags_list.company_id')
                ->whereIn('company_tags_list.company_tags_id', $special_tags);
        }

        return $query->count();
    }
}
