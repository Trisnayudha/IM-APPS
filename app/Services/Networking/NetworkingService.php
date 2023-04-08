<?php

namespace App\Services\Networking;

use App\Models\Auth\User;
use App\Repositories\NetworkingRepositoryInterface;
use Illuminate\Support\Facades\DB;

class NetworkingService implements NetworkingRepositoryInterface
{
    public function listAll($search, $limit, $users_id, $events_id)
    {
        //
        return DB::table('users_delegate')
            ->select(
                'users_delegate.id',
                'users_delegate.users_id',
                'users_delegate.events_id',
                'users.name as users_name',
                'users.job_title as users_job_title',
                'users.company_name as users_company_name',
                'users.image_users as image_users',
                'users.email'
            )
            ->leftJoin('users', function ($join) {
                $join->on('users.id', '=', 'users_delegate.users_id');
                $join->whereNotNull('users.id');
            })
            ->leftJoin('events', function ($join) {
                $join->on('events.id', '=', 'users_delegate.events_id');
                $join->whereNotNull('events.id');
            })
            ->where(function ($q) use ($events_id, $search, $users_id) {
                if ($search) {
                    $q->where('users.name', "LIKE", "%" . $search . "%");
                    // ->orWhere('users.job_title', "LIKE", "%" . $search . "%");
                }
                if ($users_id) {
                    $q->where('users_delegate.users_id', '<>', $users_id);
                }
                $q->whereIn('users_delegate.payment_status', ['Free', 'Paid Off']);
                if ($events_id) {
                    $q->where('users_delegate.events_id', $events_id);
                }
                $q->whereNotNull('users.name');
            })
            ->orderBy('users.id', 'asc')
            ->paginate($limit);
    }

    public function detailDelegate($users_id)
    {
        return User::where('id', $users_id)->first();
    }
}
