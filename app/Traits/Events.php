<?php

namespace App\Traits;

use App\Repositories\EventsConferenLog;
use App\Repositories\EventsLog;
use App\Repositories\UsersLog;
use Illuminate\Support\Facades\Cookie;

trait Events
{
    /**
     * @param $events_id
     * @return bool
     */
    public static function isCanJoinEvents($events_id)
    {
        $isCanJoin = false;
        $users_id = getSessionDelegate('login_id');
        if (!empty($users_id)) {
            $findPaidPayment = \App\Repositories\UsersDelegate::findPaidPayment($users_id, $events_id);
            if (!empty($findPaidPayment->id) && in_array(!empty($findPaidPayment->status), ['Paid Off', 'Free'])) {
                $isCanJoin = true;
            }
        }
        return $isCanJoin;
    }

    public static function isCanJoinEventsCompany($events_id)
    {
        $isCanJoin = false;
        $users_id = getSessionDelegate('login_id');
        if (!empty($users_id)) {
            $findPaidPayment = \App\Repositories\UsersCompany::findPaidPayment($users_id, $events_id);
            if (!empty($findPaidPayment->id) && in_array(!empty($findPaidPayment->status), ['Paid Off', 'Free'])) {
                $isCanJoin = true;
            }
        }
        return $isCanJoin;
    }

    /**
     * @param $events_id
     * @return string
     */
    public static function returnLinkEvents($events_id)
    {
        $findEvents = \App\Repositories\Events::findById($events_id);
        return action('Frontend\EventsJoinController@getShow', ['slug' => $findEvents->slug]);
    }

    /**
     * @param $events_id
     * @param $package
     * @return bool
     */
    public static function isShowPackage($events_id, $package)
    {
        $findEvents = \App\Repositories\Events::findById($events_id);
        $active_pack_silver = $findEvents->active_pack_silver;
        $active_pack_gold = $findEvents->active_pack_gold;
        $active_pack_platinum = $findEvents->active_pack_platinum;
        $res = false;
        if (in_array($package, ['Silver'])) {
            $res = ($active_pack_silver == 'Yes' ? true : false);
        } elseif (in_array($package, ['Gold'])) {
            $res = ($active_pack_gold == 'Yes' ? true : false);
        } elseif (in_array($package, ['Platinum'])) {
            $res = ($active_pack_platinum == 'Yes' ? true : false);
        }
        return $res;
    }

    /**
     * is events is expired
     *
     * @param $events_id
     * @return bool
     * @throws \Exception
     */
    public static function isExpiredEvent($events_id)
    {
        $findEvents = \App\Repositories\Events::findById($events_id);
        $dateNow = date('Y-m-d');
        $dateEnd = date('Y-m-d', strtotime($findEvents->date_end));
        if (new \DateTime($dateNow) <= new \DateTime($dateEnd))
            return false;
        return true;
    }

    public static function updateCursEvent($events_id)
    {
        $events_dollar_curs = \crocodicstudio\crudbooster\helpers\CRUDBooster::getSetting('setting_dollar_curs');
        $findEvents = \App\Repositories\Events::findById($events_id);
        if (!empty($findEvents->id) && $findEvents->isUpdateCurs == 1) {
            $findEvents->platinum_price_rupiah = (float) $findEvents->platinum_price_dollar*(float) $events_dollar_curs;
            $findEvents->gold_price_rupiah = (float) $findEvents->gold_price_dollar*(float) $events_dollar_curs;
            $findEvents->isUpdateCurs = 0;
            $findEvents->save();

            return true;
        }
        return false;
    }

    /**
     * check before destroy cookie
     */
    public static function destroyCookieEvents()
    {
        if (Cookie::get('pageEvents') === false)
            Cookie::queue('pageEvents', "", 1 * 24 * 60 * 60 * 1000);
    }

    /**
     * destroy cookie
     * @param $value
     */
    public static function setCookieEvents($value)
    {
        Cookie::queue('pageEvents', $value, 1 * 24 * 60 * 60 * 1000);
    }

    /**
     * check events is started or ended
     *
     * @param $events_id
     * @param int $days
     * @return bool
     * @throws \Exception
     */
    public static function checkIsStartEndDays($events_id, $days = 2)
    {
        $users_id = getSessionDelegate('login_id');
        $findPaidPayment = \App\Repositories\UsersDelegate::findPaidPayment($users_id, $events_id);

        $findEvents = \App\Repositories\Events::findById($events_id);

        $start_events = date('Y-m-d', strtotime($findEvents->date_start));
        $end_events = date('Y-m-d', strtotime($findEvents->date_end));
        $before_day = date('Y-m-d', strtotime("-$days days", strtotime($start_events)));
        $after_day = date('Y-m-d', strtotime("+$days days", strtotime($end_events)));
        $is_day = date('Y-m-d');

        if (!empty($users_id)) {
            if (new \DateTime($is_day) >= new \DateTime($before_day) && new \DateTime($is_day) < new \DateTime($start_events) && in_array(!empty($findPaidPayment->package_name), ['Platinum', 'Silver']) && in_array(!empty($findPaidPayment->status), ['Paid Off', 'Free']))
                return true;
            if (new \DateTime($is_day) <= new \DateTime($after_day) && new \DateTime($is_day) > new \DateTime($end_events) && in_array(!empty($findPaidPayment->package_name), ['Platinum', 'Silver']) && in_array(!empty($findPaidPayment->status), ['Paid Off', 'Free']))
                return true;
            if (new \DateTime($is_day) >= new \DateTime($start_events) && new \DateTime($is_day) <= new \DateTime($end_events))
                return true;
        }
        return false;
    }

    /**
     * check events is running
     *
     * @param $events_id
     * @return bool
     * @throws \Exception
     */
    public static function checkIsRunningDays($events_id)
    {
        $findEvents = \App\Repositories\Events::findById($events_id);

        $start_events = date('Y-m-d', strtotime($findEvents->date_start));
        $end_events = date('Y-m-d', strtotime($findEvents->date_end));
        $is_day = date('Y-m-d');

        if (new \DateTime($is_day) >= new \DateTime($start_events) && new \DateTime($is_day) <= new \DateTime($end_events))
            return true;
        return false;
    }

    /**
     * check package events from users
     *
     * @param $events_id
     * @return string
     */
    public static function checkPackageUsers($events_id) : String
    {
        $users_id = getSessionDelegate('login_id');
        if (!empty($users_id)) {
            $findPaidPayment = \App\Repositories\UsersDelegate::findPaidPayment($users_id, $events_id);
            if (!empty($findPaidPayment->id) && in_array(!empty($findPaidPayment->status), ['Paid Off', 'Free'])) {
                return (!empty($findPaidPayment->package_name)?$findPaidPayment->package_name:'');
            }
        }
        return "";
    }

    /**
     * save log events
     *
     * @param $events_id
     * @return bool
     */
    public static function countVisitEvents($events_id)
    {
        date_default_timezone_set('Asia/Jakarta');
        $users_id = getSessionDelegate('login_id');

        $save = new EventsLog();
        $save->created_at = date('Y-m-d H:i:s');
        $save->day = date('d');
        $save->month = date('m');
        $save->year = date('Y');
        $save->events_id = $events_id;
        $save->users_id = $users_id;
        $save->ip = request()->ip();
        $save->user_agent = request()->userAgent();
        $save->save();

        return true;
    }

    public static function countVisitConference($events_id, $id)
    {
        date_default_timezone_set('Asia/Jakarta');
        $users_id = getSessionDelegate('login_id');

        $save = new EventsConferenLog();
        $save->created_at = date('Y-m-d H:i:s');
        $save->day = date('d');
        $save->month = date('m');
        $save->year = date('Y');
        $save->events_id = $events_id;
        $save->events_conferen_id = $id;
        $save->users_id = $users_id;
        $save->ip = request()->ip();
        $save->user_agent = request()->userAgent();
        $save->save();

        return true;

    }

    public static function isTextPayment($package_name, $events_id)
    {
        $users_id = getSessionDelegate('login_id');
        $findEvents = \App\Repositories\Payment::findPayedPaymentEvents($events_id, $users_id);
        if (!empty($findEvents->id) && !empty($users_id)) {
            if (in_array($package_name, ['Silver'])) {
                return "you have registered and waiting for Approval";
            }
        }
        return "";
    }

}
