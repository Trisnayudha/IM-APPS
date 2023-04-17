<?php

namespace App\Services\Bookmark;

use App\Repositories\BookmarkRepositoryInterface;
use Illuminate\Support\Facades\DB;

class BookmarkService implements BookmarkRepositoryInterface
{
    public function listAll($limit = 3, $type, $users_id, $events_id)
    {
        if ($type == 'schedule') {
            return DB::table('conference_agenda_bookmark')
                ->join('events_schedule', 'events_schedule.id', 'conference_agenda_bookmark.events_schedule_id')
                ->where('conference_agenda_bookmark.users_id', $users_id)
                ->where('conference_agenda_bookmark.events_id', $events_id)->orderby('conference_agenda_bookmark.id', 'desc')->paginate($limit);
        } elseif ($type == 'program') {
            return DB::table('conference_bookmark')
                ->join('events_conferen', 'events_conferen.id', 'conference_bookmark.events_conferen_id')
                ->where('conference_bookmark.users_id', $users_id)
                ->where('conference_bookmark.events_id', $events_id)->orderby('conference_bookmark.id', 'desc')->paginate($limit);
        } elseif ($type == 'company') {
            return DB::table('company_bookmark')
                ->join('company', 'company.id', 'company_bookmark.company_id')
                ->where('company_bookmark.users_id', $users_id)
                ->orderby('company_bookmark.id', 'desc')->paginate($limit);
        } elseif ($type == 'networking') {
            return DB::table('networking_bookmark')
                ->join('users', 'users.id', 'networking_bookmark.users_delegate_id')
                ->where('networking_bookmark.users_id', $users_id)
                ->where('networking_bookmark.events_id', $events_id)->orderby('networking_bookmark.id', 'desc')->paginate($limit);
        } elseif ($type == 'product') {
            return DB::table('product_bookmark')
                ->join('product', 'product.id', 'product_bookmark.product_id')
                ->where('product_bookmark.users_id', $users_id)
                ->where('product_bookmark.events_id', $events_id)->orderby('product_bookmark.id', 'desc')->paginate($limit);
        } elseif ($type == 'news') {
            return DB::table('news_bookmark')
                ->join('news', 'news.id', 'news_bookmark.news_id')
                ->where('news_bookmark.users_id', $users_id)
                ->where('news_bookmark.events_id', $events_id)->orderby('news_bookmark.id', 'desc')->paginate($limit);
        } elseif ($type == 'project') {
            return DB::table('project_bookmark')
                ->join('project', 'project.id', 'project_bookmark.project_id')
                ->where('project_bookmark.users_id', $users_id)
                ->where('project_bookmark.events_id', $events_id)->orderby('project_bookmark.id', 'desc')->paginate($limit);
        } elseif ($type == 'media') {
            return DB::table('media_bookmark')
                ->join('media_resource', 'media_resource.id', 'media_bookmark.media_resource_id')
                ->where('media_bookmark.users_id', $users_id)
                ->where('media_bookmark.events_id', $events_id)->orderby('media_bookmark.id', 'desc')->paginate($limit);
        } elseif ($type == 'video') {
            return DB::table('company_video_bookmark')->where('users_id', $users_id)
                ->where('events_id', $events_id)->orderby('id', 'desc')->paginate($limit);
        }
    }
}
