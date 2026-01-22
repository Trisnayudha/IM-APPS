<?php

namespace App\Services\Networking;

use App\Models\Auth\User;
use App\Repositories\NetworkingRepositoryInterface;
use App\Traits\Directory;
use Illuminate\Support\Facades\DB;

class NetworkingService implements NetworkingRepositoryInterface
{
    use Directory;
    public function listAll($search, $limit, $users_id, $events_id)
    {
        return DB::table('users_delegate')
            ->select(
                'users_delegate.id',
                'users_delegate.users_id',
                'users_delegate.events_id',
                'users.name as users_name',
                'users.job_title as users_job_title',
                'users.company_name as users_company_name',
                'users.image_users as image_users',
                'users.email',

                // sudah connect (accepted)
                DB::raw('
                CASE
                    WHEN nr_accept.id IS NOT NULL THEN 1
                    ELSE 0
                END as isConnected
            '),

                // sudah request tapi belum ada action (pending)
                DB::raw('
                CASE
                    WHEN nr_pending.id IS NOT NULL THEN 1
                    ELSE 0
                END as request_pending
            ')
            )

            ->leftJoin('users', function ($join) {
                $join->on('users.id', '=', 'users_delegate.users_id');
            })

            ->leftJoin('events', function ($join) {
                $join->on('events.id', '=', 'users_delegate.events_id');
            })

            /* ===== ACCEPTED ===== */
            ->leftJoin('networking_requests as nr_accept', function ($join) use ($users_id, $events_id) {
                $join->where('nr_accept.status', 'accepted')
                    ->where('nr_accept.events_id', $events_id)
                    ->where(function ($q) use ($users_id) {
                        $q->where(function ($sub) use ($users_id) {
                            $sub->on('nr_accept.requester_id', '=', 'users_delegate.users_id')
                                ->where('nr_accept.target_id', '=', $users_id);
                        })
                            ->orWhere(function ($sub) use ($users_id) {
                                $sub->on('nr_accept.target_id', '=', 'users_delegate.users_id')
                                    ->where('nr_accept.requester_id', '=', $users_id);
                            });
                    });
            })

            /* ===== PENDING ===== */
            ->leftJoin('networking_requests as nr_pending', function ($join) use ($users_id, $events_id) {
                $join->where('nr_pending.status', 'pending')
                    ->where('nr_pending.events_id', $events_id)
                    ->where(function ($q) use ($users_id) {
                        $q->where(function ($sub) use ($users_id) {
                            $sub->on('nr_pending.requester_id', '=', 'users_delegate.users_id')
                                ->where('nr_pending.target_id', '=', $users_id);
                        })
                            ->orWhere(function ($sub) use ($users_id) {
                                $sub->on('nr_pending.target_id', '=', 'users_delegate.users_id')
                                    ->where('nr_pending.requester_id', '=', $users_id);
                            });
                    });
            })

            ->where(function ($q) use ($events_id, $search, $users_id) {
                if ($search) {
                    $q->where(function ($subQ) use ($search) {
                        $subQ->where('users.name', 'LIKE', "%{$search}%")
                            ->orWhere('users.company_name', 'LIKE', "%{$search}%");
                    });
                }

                if ($users_id) {
                    $q->where('users_delegate.users_id', '<>', $users_id);
                }

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
        $user = User::select(
            'id',
            'name',
            'image_users',
            'job_title',
            'company_name',
            'company_logo',
            'bio_desc'
        )
            ->where('id', $users_id)
            ->firstOrFail();

        $event = DB::table('events')->orderBy('id', 'desc')->first();

        $isBookmark = self::isBookmark(
            'Networking',
            $user->id,
            $event->id
        );

        $isConnected = DB::table('networking_requests')
            ->where('status', 'accepted')
            ->where(function ($q) use ($users_id) {
                $q->where('requester_id', auth('sanctum')->id())
                    ->where('target_id', $users_id);
            })
            ->orWhere(function ($q) use ($users_id) {
                $q->where('requester_id', $users_id)
                    ->where('target_id', auth('sanctum')->id());
            })
            ->exists() ? 1 : 0;

        return [
            'id'            => $user->id,
            'name'          => $user->name,
            'image_users'   => $user->image_users,
            'job_title'     => $user->job_title,
            'company_name'  => $user->company_name,
            'company_logo'  => $user->company_logo,
            'bio_desc'      => $user->bio_desc,
            'isBookmark'    => $isBookmark ? 1 : 0,
            'isConnected'   => $isConnected,
        ];
    }


    public function scanUsers($codePayment)
    {
        return  DB::table('payment')
            ->join('users', 'users.id', 'payment.users_id')
            ->join('users_delegate', 'users_delegate.users_id', 'users.id')
            ->where('code_payment', $codePayment)
            ->select(
                'users_delegate.id',
                'users_delegate.users_id',
                'users_delegate.events_id',
                'users.name as users_name',
                'users.job_title as users_job_title',
                'users.company_name as users_company_name',
                'users.image_users as image_users',
                'users.email'
            )->first();
    }
}
