<?php

namespace App\Traits;

use App\Models\Directory\CompanyVideo;
use App\Models\Directory\MediaResource;
use App\Models\Directory\News;
use App\Models\Directory\Product;
use App\Models\Directory\Project;
use App\Models\Log\CompanyLog;
use App\Models\Log\CompanyVideoLog;
use App\Models\Log\MediaResourceLog;
use App\Models\Log\NewsLog;
use App\Models\Log\ProductLog;
use App\Models\Log\ProjectLog;
use App\Models\Log\UsersLog;
use Illuminate\Support\Facades\Cookie;

trait Directory
{
    /**
     * check is there another request or not
     *
     * @param $company_id
     * @param $users_id
     * @return bool
     * @throws \Exception
     */
    // public static function isExpiredSendCard($company_id, $users_id)
    // {
    //     date_default_timezone_set('Asia/Jakarta');
    //     $find = \App\Repositories\CompanySendCard::findByCompanyUsers($company_id, $users_id);
    //     if (!empty($find->id)) {
    //         $dateNow = date('Y-m-d H:i:s');
    //         $date = date('Y-m-d H:i:s', strtotime($find->date_expired_req));

    //         if (new \DateTime($dateNow) <= new \DateTime($date)) {
    //             return true;
    //         }
    //     }

    //     return false;
    // }



    /**
     * count visit page Media Resource, Project, Product
     *
     * @param $page
     * @param $id
     * @return bool
     */
    public static function countVisitPage($page, $visit = "Out Events", $id, $company_id = null, $events_id = null, $users_id)
    {
        date_default_timezone_set('Asia/Jakarta');
        if ($page == 'Media') {
            $save = new MediaResourceLog();
            $save->flag = 'Visit';
            $save->flag_visit = $visit;
            $save->day = date('d');
            $save->month = date('m');
            $save->year = date('Y');
            $save->events_id = $events_id;
            $save->company_id = $company_id;
            $save->media_resource_id = $id;
            $save->users_id = $users_id;
            $save->ip = request()->ip();
            $save->user_agent = request()->userAgent();
            $save->save();

            $find = MediaResource::find($id);
            $find->views = (int) $find->views + 1;
            $find->save();

            self::usersActivity('Visit Media Resource Detail', $visit, $events_id, $id);

            return true;
        } elseif ($page == 'Project') {
            $save = new ProjectLog();
            $save->flag = 'Visit';
            $save->flag_visit = $visit;
            $save->day = date('d');
            $save->month = date('m');
            $save->year = date('Y');
            $save->events_id = $events_id;
            $save->company_id = $company_id;
            $save->project_id = $id;
            $save->users_id = $users_id;
            $save->ip = request()->ip();
            $save->user_agent = request()->userAgent();
            $save->save();

            $find = Project::find($id);
            $find->views = (int) $find->views + 1;
            $find->save();

            self::usersActivity('Visit Project Detail', $visit, $events_id, $id);

            return true;
        } elseif ($page == 'Product') {
            $save = new ProductLog();
            $save->flag = 'Visit';
            $save->flag_visit = $visit;
            $save->day = date('d');
            $save->month = date('m');
            $save->year = date('Y');
            $save->events_id = $events_id;
            $save->company_id = $company_id;
            $save->product_id = $id;
            $save->users_id = $users_id;
            $save->ip = request()->ip();
            $save->user_agent = request()->userAgent();
            $save->save();

            $find = Product::find($id);
            $find->views = (int) $find->views + 1;
            $find->save();

            self::usersActivity('Visit Product Detail', $visit, $events_id, $id);

            return true;
        } elseif ($page == 'News') {
            $save = new NewsLog();
            $save->flag = 'Visit';
            $save->flag_visit = $visit;
            $save->day = date('d');
            $save->month = date('m');
            $save->year = date('Y');
            $save->events_id = $events_id;
            $save->company_id = $company_id;
            $save->news_id = $id;
            $save->users_id = $users_id;
            $save->ip = request()->ip();
            $save->user_agent = request()->userAgent();
            $save->save();

            $find = News::find($id);
            $find->views = (int) $find->views + 1;
            $find->save();

            self::usersActivity('Visit News Detail', $visit, $events_id, $id);

            return true;
        } elseif ($page == 'Company') {
            $save = new CompanyLog();
            $save->flag_visit = $visit;
            $save->day = date('d');
            $save->month = date('m');
            $save->year = date('Y');
            $save->events_id = $events_id;
            $save->company_id = $id;
            $save->users_id = $users_id;
            $save->ip = request()->ip();
            $save->user_agent = request()->userAgent();
            $save->save();

            self::usersActivity('Visit Company Profile', $visit, $events_id, $id);

            return true;
        } elseif ($page == 'Video') {
            $save = new CompanyVideoLog();
            $save->flag_visit = $visit;
            $save->day = date('d');
            $save->month = date('m');
            $save->year = date('Y');
            $save->events_id = $events_id;
            $save->company_video_id = $id;
            $save->company_id = $company_id;
            $save->users_id = $users_id;
            $save->ip = request()->ip();
            $save->user_agent = request()->userAgent();
            $save->save();

            $find = CompanyVideo::find($id);
            $find->views = (int) $find->views + 1;
            $find->save();

            self::usersActivity('Visit Video Detail', $visit, $events_id, $id);

            return true;
        }

        return false;
    }

    /**
     * count dowload page Media Resource, Project, Product
     *
     * @param $page
     * @param $id
     * @return bool
     */
    public static function countDownloadPage($page, $id, $company_id = null, $events_id = null, $users_id)
    {
        $visit = (self::isInEvents() ? 'In' : 'Out') . ' Events';

        if ($page == 'Media') {
            $save = new MediaResourceLog();
            $save->flag = 'Download';
            $save->flag_visit = $visit;
            $save->day = date('d');
            $save->month = date('m');
            $save->year = date('Y');
            $save->events_id = $events_id;
            $save->company_id = $company_id;
            $save->users_id = $users_id;
            $save->media_resource_id = $id;
            $save->ip = request()->ip();
            $save->user_agent = request()->userAgent();
            $save->save();

            $find = MediaResource::find($id);
            $find->download = (int) $find->download + 1;
            $find->save();

            self::usersActivity('Media Resource Download File', (self::isInEvents() ? 'In' : 'Out') . ' Events', self::getIdEvents(), $id);

            return true;
        } elseif ($page == 'Project') {
            $save = new ProjectLog();
            $save->flag = 'Download';
            $save->flag_visit = $visit;
            $save->day = date('d');
            $save->month = date('m');
            $save->year = date('Y');
            $save->events_id = $events_id;
            $save->company_id = $company_id;
            $save->users_id = $users_id;
            $save->project_id = $id;
            $save->ip = request()->ip();
            $save->user_agent = request()->userAgent();
            $save->save();

            $find = Project::find($id);
            $find->download = (int) $find->download + 1;
            $find->save();

            self::usersActivity('Project Download File', (self::isInEvents() ? 'In' : 'Out') . ' Events', self::getIdEvents(), $id);

            return true;
        } elseif ($page == 'Product') {
            $save = new ProductLog();
            $save->flag = 'Download';
            $save->flag_visit = $visit;
            $save->day = date('d');
            $save->month = date('m');
            $save->year = date('Y');
            $save->events_id = $events_id;
            $save->company_id = $company_id;
            $save->users_id = $users_id;
            $save->product_id = $id;
            $save->ip = request()->ip();
            $save->user_agent = request()->userAgent();
            $save->save();

            $find = Product::find($id);
            $find->download = (int) $find->download + 1;
            $find->save();

            self::usersActivity('Product Download File', $visit, self::getIdEvents(), $id);

            return true;
        }
        return false;
    }

    /**
     * get detail company
     *
     * @return bool
     */
    public static function isDetailCompany()
    {
        $get = \Illuminate\Support\Facades\Session::get('isDetailCompany');
        if ($get)
            return true;
        return false;
    }

    /**
     * get is in events or not
     *
     * @return bool
     */
    public static function isInEvents()
    {
        $get = \Illuminate\Support\Facades\Session::get('isInEvents');
        if ($get)
            return true;
        return false;
    }

    /**
     * get id events is in events or not
     *
     * @return bool
     */
    public static function getIdEvents()
    {
        $get = \Illuminate\Support\Facades\Session::get('isInEvents');
        if ($get)
            return $get;
        return false;
    }

    // public static function isBookmark($type, $id, $events_id = null)
    // {
    //     $users_id =  auth('sanctum')->user()->id ?? null;
    //     if ($type == 'Company') {
    //         $find = \App\Repositories\CompanyBookmark::findBy(['company_id' => $id, 'users_id' => $users_id]);
    //     } else if ($type == 'Media Directory') {
    //         $find = \App\Repositories\MediaBookmark::findBy(['media_resource_id' => $id, 'users_id' => $users_id, 'events_id' => $events_id]);
    //     } else if ($type == 'Project') {
    //         $find = \App\Repositories\ProjectBookmark::findBy(['project_id' => $id, 'users_id' => $users_id, 'events_id' => $events_id]);
    //     } else if ($type == 'Product') {
    //         $find = \App\Repositories\ProductBookmark::findBy(['product_id' => $id, 'users_id' => $users_id, 'events_id' => $events_id]);
    //     } else if ($type == 'News') {
    //         $find = \App\Repositories\NewsBookmark::findBy(['news_id' => $id, 'users_id' => $users_id, 'events_id' => $events_id]);
    //     } else if ($type == 'Networking') {
    //         $find = \App\Repositories\NetworkingBookmark::findBy(['users_delegate_id' => $id, 'users_id' => $users_id, 'events_id' => $events_id]);
    //     } else if ($type == 'Conference') {
    //         $find = \App\Repositories\ConferenceBookmark::findBy(['events_conferen_id' => $id, 'users_id' => $users_id, 'events_id' => $events_id]);
    //     } else if ($type == 'Conference Agenda') {
    //         $find = \App\Repositories\ConferenceAgendaBookmark::findBy(['events_schedule_id' => $id, 'users_id' => $users_id, 'events_id' => $events_id]);
    //     } else if ($type == 'Video') {
    //         $find = \App\Repositories\CompanyVideoBookmark::findBy(['company_video_id' => $id, 'users_id' => $users_id, 'events_id' => $events_id]);
    //     }

    //     if (!empty($find->id))
    //         return true;
    //     return false;
    // }

    /**
     * check before destroy cookie
     */
    public static function destroyCookieDirectory()
    {
        if (Cookie::get('pageDirectory') === false)
            Cookie::queue('pageDirectory', "", 1 * 24 * 60 * 60 * 1000);
    }

    /**
     * destroy cookie
     * @param $value
     */
    public static function setCookieDirectory($value)
    {
        Cookie::queue('pageDirectory', $value, 1 * 24 * 60 * 60 * 1000);
    }


    /**
     * check before destroy cookie
     */
    public static function destroyCookieCompany()
    {
        if (Cookie::get('pageDirectory') === false)
            Cookie::queue('pageCompany', "", 1 * 24 * 60 * 60 * 1000);
    }

    /**
     * destroy cookie
     * @param $value
     */
    public static function setCookieCompany($value)
    {
        Cookie::queue('pageCompany', $value, 1 * 24 * 60 * 60 * 1000);
    }

    /**
     * get redirect conference
     *
     * @return false|mixed
     */
    public static function isFromConference()
    {
        $get = \Illuminate\Support\Facades\Session::get('redirectConference');
        if ($get)
            return $get;
        return false;
    }

    public static function usersActivity($summary, $visit = "Out Events", $events_id = null, $target_id = null)
    {
        date_default_timezone_set('Asia/Jakarta');
        $users_id =  auth('sanctum')->user()->id ?? null;

        $save = new UsersLog();
        $save->created_at = date('Y-m-d H:i:s');
        $save->flag_visit = $visit;
        $save->day = date('d');
        $save->month = date('m');
        $save->year = date('Y');
        $save->users_id = $users_id;
        if (!empty($events_id)) {
            $save->events_id = $events_id;
        }
        if (!empty($target_id)) {
            $save->target_id = $target_id;
        }
        $save->ip = request()->ip();
        $save->user_agent = request()->userAgent();
        $save->summary = $summary;
        $save->save();
    }

    public static function countSharePage($page, $visit = "Out Events", $id, $company_id = null, $events_id = null, $type = null)
    {
        date_default_timezone_set('Asia/Jakarta');

        $users_id =  auth('sanctum')->user()->id ?? null;
        if ($page == 'Media Resource') {
            $save = new MediaResourceLog();
            $save->flag = 'Share';
            $save->flag_visit = $visit;
            $save->day = date('d');
            $save->month = date('m');
            $save->year = date('Y');
            $save->events_id = $events_id;
            $save->company_id = $company_id;
            $save->media_resource_id = $id;
            $save->users_id = $users_id;
            $save->ip = request()->ip();
            $save->user_agent = request()->userAgent();
            $save->share_type = $type;
            $save->save();

            $find = MediaResource::findById($id);
            $find->share = (int) $find->share + 1;
            $find->save();

            self::usersActivity('Share Media Resource Detail', $visit, $events_id, $id);

            return true;
        } elseif ($page == 'Project') {
            $save = new ProjectLog();
            $save->flag = 'Share';
            $save->flag_visit = $visit;
            $save->day = date('d');
            $save->month = date('m');
            $save->year = date('Y');
            $save->events_id = $events_id;
            $save->company_id = $company_id;
            $save->project_id = $id;
            $save->users_id = $users_id;
            $save->ip = request()->ip();
            $save->user_agent = request()->userAgent();
            $save->share_type = $type;
            $save->save();

            $find = Project::findById($id);
            $find->share = (int) $find->share + 1;
            $find->save();

            self::usersActivity('Share Project Detail', $visit, $events_id, $id);

            return true;
        } elseif ($page == 'Product') {
            $save = new ProductLog();
            $save->flag = 'Share';
            $save->flag_visit = $visit;
            $save->day = date('d');
            $save->month = date('m');
            $save->year = date('Y');
            $save->events_id = $events_id;
            $save->company_id = $company_id;
            $save->product_id = $id;
            $save->users_id = $users_id;
            $save->ip = request()->ip();
            $save->user_agent = request()->userAgent();
            $save->share_type = $type;
            $save->save();

            $find = Product::findById($id);
            $find->share = (int) $find->share + 1;
            $find->save();

            self::usersActivity('Share Product Detail', $visit, $events_id, $id);

            return true;
        } elseif ($page == 'News') {
            $save = new NewsLog();
            $save->flag = 'Share';
            $save->flag_visit = $visit;
            $save->day = date('d');
            $save->month = date('m');
            $save->year = date('Y');
            $save->events_id = $events_id;
            $save->company_id = $company_id;
            $save->news_id = $id;
            $save->users_id = $users_id;
            $save->ip = request()->ip();
            $save->user_agent = request()->userAgent();
            $save->share_type = $type;
            $save->save();

            $find = News::findById($id);
            $find->share = (int) $find->share + 1;
            $find->save();

            self::usersActivity('Share News Detail', $visit, $events_id, $id);

            return true;
        } elseif ($page == 'Company') {
            $save = new CompanyLog();
            $save->flag_visit = $visit;
            $save->day = date('d');
            $save->month = date('m');
            $save->year = date('Y');
            $save->events_id = $events_id;
            $save->company_id = $id;
            $save->users_id = $users_id;
            $save->ip = request()->ip();
            $save->user_agent = request()->userAgent();
            $save->save();

            self::usersActivity('Share Company Profile', $visit, $events_id, $id);

            return true;
        }

        return false;
    }
}
