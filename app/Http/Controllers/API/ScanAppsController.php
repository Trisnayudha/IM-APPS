<?php

namespace App\Http\Controllers\API;

use App\Helpers\WhatsappApi;
use App\Http\Controllers\Controller;
use App\Models\Auth\User;
use App\Models\Payment\Payment;
use App\Models\Payment\UsersDelegate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ScanAppsController extends Controller
{
    // Event dates: May 5-7, 2026
    private const EVENT_YEAR  = 2026;
    private const EVENT_MONTH = 5;
    private const EVENT_DAYS  = [5 => 'date_day1', 6 => 'date_day2', 7 => 'date_day3'];

    private function resolveCheckinColumn(Carbon $dt): ?string
    {
        if ($dt->year === self::EVENT_YEAR && $dt->month === self::EVENT_MONTH) {
            return self::EVENT_DAYS[$dt->day] ?? null;
        }
        return null;
    }

    private function ok($message, $data = null, int $httpCode = 200)
    {
        return response()->json([
            'status'  => $httpCode,
            'message' => $message,
            'data'    => $data,
        ], $httpCode);
    }

    private function err($message, int $httpCode = 400)
    {
        return response()->json([
            'status'  => $httpCode,
            'message' => $message,
            'data'    => null,
        ], $httpCode);
    }

    public function checkin(Request $request)
    {
        $data = $request->all();

        $codePayment = $data['code_payment'] ?? null;
        $linkWebhook = $data['link_webhook'] ?? null;
        $day         = $data['day'] ?? null;
        $name        = $data['name'] ?? null;
        $job         = $data['job_title'] ?? null;
        $company     = $data['company'] ?? null;
        $image       = $data['image'] ?? null;

        if (!$codePayment || !$linkWebhook || !$day) {
            return $this->err('code_payment, link_webhook, and day are required', 400);
        }

        try {
            $result = Payment::join('users_delegate as ud', function ($join) {
                $join->on('ud.users_id', '=', 'payment.users_id')
                    ->on('ud.events_id', '=', 'payment.events_id');
            })
                ->where('payment.code_payment', $codePayment)
                ->where('payment.aproval_quota_users', 1)
                ->select('payment.id as payment_id', 'ud.id as delegate_id', 'ud.users_id')
                ->first();

            if (!$result) {
                return $this->err('QR Code tidak valid', 404);
            }

            $paymentId  = $result->payment_id;
            $delegateId = $result->delegate_id;

            $col = null;
            try {
                $dt  = Carbon::parse($day);
                $col = $this->resolveCheckinColumn($dt);
            } catch (\Exception $e) {
                // parsing fails — skip checkin column update
            }

            $delegate      = UsersDelegate::where('payment_id', $paymentId)->first();
            $existingImage = $delegate ? $delegate->image : null;
            $filename      = null;

            if ($image) {
                $imageBinary = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $image));
                $filename    = uniqid() . '.png';
                Storage::disk('public')->put('uploads/images/exhibition/' . $filename, $imageBinary);
            } elseif ($existingImage) {
                $filename = $existingImage;
            }

            $delegateUpdate = ['image' => $filename];
            if ($col) {
                $delegateUpdate[$col] = $day;
            }
            UsersDelegate::where('id', $delegateId)->update($delegateUpdate);

            User::where('id', $result->users_id)->update([
                'name'         => $name,
                'job_title'    => $job,
                'company_name' => $company
            ]);

            $payload = [
                'name'         => $name,
                'company'      => $company,
                'job_title'    => $job,
                'code_payment' => $codePayment,
                'day'          => $day
            ];

            if ($filename) {
                $payload['image_url'] = url('storage/uploads/images/exhibition/' . $filename);
            }

            $this->sendWebhook($linkWebhook, [
                'name'         => $name,
                'job_title'    => $job,
                'company'      => $company,
                'code_payment' => $codePayment
            ]);

            return $this->ok('Check-in berhasil', $payload);
        } catch (\Exception $e) {
            Log::error("Error in /checkin: " . $e->getMessage());
            return $this->err($e->getMessage(), 500);
        }
    }

    public function scanQr(Request $request)
    {
        $data        = $request->json()->all();
        $codePayment = $data['code_payment'] ?? null;
        $day         = $data['day'] ?? null;

        if (!$codePayment || !$day) {
            return $this->err('code_payment and day are required', 400);
        }

        try {
            $col = null;
            $dt  = null;
            try {
                $dt  = Carbon::parse($day);
                $col = $this->resolveCheckinColumn($dt);
            } catch (\Exception $e) {
                return $this->err('Invalid day format', 400);
            }

            if (!$col) {
                return $this->err('Day is not a valid event day (May 5, 6, or 7, 2026)', 400);
            }

            $result = DB::table('payment as p')
                ->join('users as u', 'u.id', '=', 'p.users_id')
                ->join('events_tickets as et', 'et.id', '=', 'p.package_id')
                ->where('p.code_payment', $codePayment)
                ->where('p.aproval_quota_users', 1)
                ->whereNotIn('p.status', ['trash', 'Waiting', 'cancelled', 'Expired'])
                ->select(
                    'p.id as payment_id',
                    'u.name',
                    'u.job_title',
                    'u.company_name',
                    'et.category',
                    'et.title',
                    'et.type'
                )
                ->first();

            if (!$result) {
                $payment = DB::table('payment')->where('code_payment', $codePayment)->first();
                if (!$payment) {
                    return response()->json([
                        'status'  => 404,
                        'message' => 'Delegate Not Found',
                        'data'    => null,
                    ], 404);
                }
                return $this->err('Payment is not approved or has been cancelled', 403);
            }

            $paymentId = $result->payment_id;
            $category  = $result->category;
            $typeVal   = $result->type;
            $title     = $result->title;

            // Day access check for non-All Access tickets
            if ($category !== 'All Access' && preg_match('/^Day (\d+)$/', $category, $matches)) {
                $accessDay   = (int) $matches[1];
                $expectedDay = 4 + $accessDay; // Day 1 → May 5, Day 2 → May 6, Day 3 → May 7
                if ($dt->day !== $expectedDay) {
                    return $this->err(
                        "Your ticket grants Day {$accessDay} Access. See you on May {$expectedDay} for Indonesia Miner 2026!",
                        403
                    );
                }
            }

            $delegate = DB::table('users_delegate')->where('payment_id', $paymentId)->first();
            if (!$delegate) {
                return $this->err('Delegate record not found', 404);
            }

            $imageUrl         = $delegate->image
                ? url("storage/uploads/images/exhibition/{$delegate->image}")
                : null;
            $alreadyCheckedIn = !empty($delegate->$col);

            $jakartaTime = Carbon::now('Asia/Jakarta');
            DB::table('users_delegate')
                ->where('payment_id', $paymentId)
                ->update([$col => $jakartaTime->format('Y-m-d H:i:s')]);

            list($ticketLabel, $ticketColor, $accessAreas) = $this->mapTicketType($typeVal, $title);

            if ($ticketLabel === 'Speaker Pass') {
                $this->sendWhatsAppNotification($result->name, $result->company_name, $ticketLabel, $imageUrl);
            }

            return $this->ok(
                $alreadyCheckedIn ? 'Already checked in (re-scan)' : 'Scan QR successful',
                [
                    'name'               => $result->name,
                    'job_title'          => $result->job_title,
                    'company'            => $result->company_name,
                    'code_payment'       => $codePayment,
                    'checkin_field'      => $col,
                    'ticket_type'        => $ticketLabel,
                    'ticket_color'       => $ticketColor,
                    'access_areas'       => $accessAreas,
                    'image'              => $imageUrl,
                    'already_checked_in' => $alreadyCheckedIn,
                ]
            );
        } catch (\Exception $e) {
            return $this->err($e->getMessage(), 500);
        }
    }

    /**
     * GET /api/guests/snapshot
     * Download seluruh data tamu untuk offline-first mode di Flutter app.
     * Cache TTL dikontrol via SNAPSHOT_CACHE_TTL di .env (default 5 menit).
     * Tambah ?refresh=1 untuk force rebuild cache.
     */
    public function guestsSnapshot(Request $request)
    {
        try {
            $ttl     = (int) env('SNAPSHOT_CACHE_TTL', 300); // detik
            $cacheKey = 'guests_snapshot_event_14';

            if ($request->boolean('refresh')) {
                Cache::forget($cacheKey);
            }

            $payload = Cache::remember($cacheKey, $ttl, function () {
                $results = DB::table('payment as p')
                    ->join('users as u', 'u.id', '=', 'p.users_id')
                    ->join('events_tickets as et', 'et.id', '=', 'p.package_id')
                    ->leftJoin('users_delegate as ud', 'ud.payment_id', '=', 'p.id')
                    ->where('p.events_id', 14)
                    ->where('p.aproval_quota_users', 1)
                    ->whereNotIn('p.status', ['trash', 'Waiting', 'cancelled', 'Expired'])
                    ->select(
                        'p.id as payment_id',
                        'p.code_payment',
                        'u.name',
                        'u.job_title',
                        'u.company_name',
                        'et.category',
                        'et.title',
                        'et.type',
                        'ud.image',
                        'ud.date_day1',
                        'ud.date_day2',
                        'ud.date_day3'
                    )
                    ->get();

                $guests = $results->map(function ($r) {
                    list($ticketLabel, $ticketColor, $accessAreas) = $this->mapTicketType($r->type, $r->title);
                    return [
                        'payment_id'   => $r->payment_id,
                        'code_payment' => $r->code_payment,
                        'name'         => $r->name,
                        'job_title'    => $r->job_title,
                        'company'      => $r->company_name,
                        'ticket_type'  => $ticketLabel,
                        'ticket_color' => $ticketColor,
                        'access_areas' => $accessAreas,
                        'category'     => $r->category,
                        'image'        => $r->image
                            ? url("storage/uploads/images/exhibition/{$r->image}")
                            : null,
                        'checkins'     => [
                            'day1' => $r->date_day1,
                            'day2' => $r->date_day2,
                            'day3' => $r->date_day3,
                        ],
                    ];
                });

                return [
                    'event_id'     => 14,
                    'generated_at' => Carbon::now('Asia/Jakarta')->toIso8601String(),
                    'total'        => $guests->count(),
                    'guests'       => $guests,
                ];
            });

            return $this->ok('Success', $payload);
        } catch (\Exception $e) {
            return $this->err($e->getMessage(), 500);
        }
    }

    /**
     * POST /api/checkins/batch
     * Upload pending checkins dari tablet saat koneksi pulih.
     * Body: { "checkins": [{ "code_payment": "...", "day": "2026-05-05", "checked_in_at": "..." }] }
     */
    public function batchCheckin(Request $request)
    {
        $data     = $request->json()->all();
        $checkins = $data['checkins'] ?? [];

        if (empty($checkins) || !is_array($checkins)) {
            return $this->err('checkins array is required', 400);
        }

        $results     = [];
        $jakartaTime = Carbon::now('Asia/Jakarta');

        foreach ($checkins as $checkin) {
            $codePayment = $checkin['code_payment'] ?? null;
            $day         = $checkin['day'] ?? null;
            $checkedInAt = $checkin['checked_in_at'] ?? $jakartaTime->format('Y-m-d H:i:s');
            $imageBase64 = $checkin['image'] ?? null;
            $name        = $checkin['name'] ?? null;
            $jobTitle    = $checkin['job_title'] ?? null;
            $company     = $checkin['company'] ?? null;

            if (!$codePayment || !$day) {
                $results[] = [
                    'code_payment' => $codePayment,
                    'status'       => 400,
                    'message'      => 'Missing code_payment or day',
                    'image'        => null,
                ];
                continue;
            }

            try {
                $dt  = Carbon::parse($day);
                $col = $this->resolveCheckinColumn($dt);

                if (!$col) {
                    $results[] = [
                        'code_payment' => $codePayment,
                        'status'       => 400,
                        'message'      => 'Invalid event day',
                        'image'        => null,
                    ];
                    continue;
                }

                $payment = DB::table('payment')->where('code_payment', $codePayment)->first();
                if (!$payment) {
                    $results[] = [
                        'code_payment' => $codePayment,
                        'status'       => 404,
                        'message'      => 'Not found',
                        'image'        => null,
                    ];
                    continue;
                }

                $delegate         = DB::table('users_delegate')->where('payment_id', $payment->id)->first();
                $alreadyCheckedIn = $delegate && !empty($delegate->$col);

                // Save new face image only if explicitly sent
                $filename = null;
                if ($imageBase64) {
                    $imageBinary = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $imageBase64));
                    $filename    = uniqid() . '.png';
                    Storage::disk('public')->put('uploads/images/exhibition/' . $filename, $imageBinary);
                }

                // Update users_delegate: checkin time (if not yet) + image (only if new image sent)
                $delegateUpdate = [];
                if (!$alreadyCheckedIn) {
                    $delegateUpdate[$col] = Carbon::parse($checkedInAt)
                        ->setTimezone('Asia/Jakarta')
                        ->format('Y-m-d H:i:s');
                }
                if ($filename) {
                    $delegateUpdate['image'] = $filename;
                }
                if ($delegateUpdate) {
                    DB::table('users_delegate')
                        ->where('payment_id', $payment->id)
                        ->update($delegateUpdate);
                }

                // Update user profile fields if provided
                $userUpdate = array_filter([
                    'name'         => $name,
                    'job_title'    => $jobTitle,
                    'company_name' => $company,
                ]);
                if ($userUpdate) {
                    DB::table('users')
                        ->where('id', $payment->users_id)
                        ->update($userUpdate);
                }

                // Resolve final image URL (new upload takes priority, fallback to existing)
                $finalImage = $filename ?? ($delegate->image ?? null);
                $imageUrl   = $finalImage
                    ? url("storage/uploads/images/exhibition/{$finalImage}")
                    : null;

                $results[] = [
                    'code_payment' => $codePayment,
                    'status'       => 200,
                    'message'      => !$alreadyCheckedIn ? 'Check-in recorded' : 'Already checked in, skipped',
                    'image'        => $imageUrl,
                ];
            } catch (\Exception $e) {
                $results[] = [
                    'code_payment' => $codePayment,
                    'status'       => 500,
                    'message'      => $e->getMessage(),
                    'image'        => null,
                ];
            }
        }

        $synced  = collect($results)->where('status', 200)->where('message', 'Check-in recorded')->count();
        $skipped = collect($results)->where('message', 'Already checked in, skipped')->count();
        $failed  = collect($results)->whereIn('status', [400, 404, 500])->count();

        // Invalidate snapshot cache so next GET /guests/snapshot reflects updated checkins
        if ($synced > 0) {
            Cache::forget('guests_snapshot_event_14');
        }

        return $this->ok("Batch processed: {$synced} synced, {$skipped} skipped, {$failed} failed", [
            'summary' => compact('synced', 'skipped', 'failed'),
            'results' => $results,
        ]);
    }

    public function listDelegate(Request $request)
    {
        $search = $request->query('search', '');

        if (strlen($search) < 4) {
            return $this->err('Search term must be at least 4 characters', 400);
        }

        try {
            $likePattern = "%{$search}%";

            $results = DB::table('payment as p')
                ->join('users as u', 'u.id', '=', 'p.users_id')
                ->join('events_tickets as et', 'et.id', '=', 'p.package_id')
                ->where('p.events_id', 14)
                ->where('p.aproval_quota_users', 1)
                ->whereNotIn('p.status', ['trash', 'Waiting', 'cancelled'])
                ->where(function ($query) use ($likePattern) {
                    $query->where('u.name', 'like', $likePattern)
                        ->orWhere('u.company_name', 'like', $likePattern);
                })
                ->select('u.name', 'u.job_title', 'u.company_name', 'p.code_payment', 'et.title', 'et.type')
                ->orderBy('u.name')
                ->limit(5)
                ->get();

            $data = $results->map(function ($r) {
                list($ticketLabel, $ticketColor, $accessAreas) = $this->mapTicketType($r->type, $r->title);
                return [
                    'name'         => $r->name,
                    'job_title'    => $r->job_title,
                    'company'      => $r->company_name,
                    'code_payment' => $r->code_payment,
                    'ticket_type'  => $ticketLabel,
                    'ticket_color' => $ticketColor,
                    'access_areas' => $accessAreas,
                ];
            });

            return $this->ok('Success', $data);
        } catch (\Exception $e) {
            return $this->err($e->getMessage(), 500);
        }
    }

    /**
     * Returns [$label, $color, $access_areas]
     *
     * access_areas reflects what the badge grants:
     *   All Access  → Conference + Exhibition + Networking Functions
     *   Exhibitor   → Exhibition + Networking Functions
     *   Explore     → Exhibition only
     */
    private function mapTicketType($typeVal, $title)
    {
        $allAccess      = ['Conference', 'Exhibition', 'Networking Functions'];
        $exhibitorAccess = ['Exhibition', 'Networking Functions'];
        $exploreAccess  = ['Exhibition'];

        if ($typeVal == 'Platinum' || $typeVal == 'Delegate Speaker') {
            return ['Delegate Pass', '#1428DF', $allAccess];
        }
        if ($typeVal == 'Speaker') {
            return ['Speaker Pass', '#D60000', $allAccess];
        }
        if ($typeVal == 'Gold') {
            if (strpos($title, 'Working') !== false) {
                return ['Working Pass', '#DAA520', []];
            }
            if (strpos($title, 'Upgrade') !== false) {
                return ['Exhibitor Pass', '#FFD700', $allAccess];
            }
            return ['Exhibitor Pass', '#FFD700', $exhibitorAccess];
        }
        if ($typeVal == 'Silver') {
            if (strpos($title, 'Explore') !== false) {
                return ['Explore Pass', '#F97316', $exploreAccess];
            } elseif (strpos($title, 'Investor') !== false) {
                return ['Investor Pass', '#1E90FF', $allAccess];
            } elseif (strpos($title, 'Mining') !== false) {
                return ['Mining Pass', '#228B22', $allAccess];
            } elseif (strpos($title, 'Media') !== false) {
                return ['Media Pass', '#8A2BE2', $allAccess];
            } elseif (strpos($title, 'Exhibitor') !== false || strpos($title, 'Exhibition') !== false) {
                return ['Exhibitor Pass', '#FFD700', $exhibitorAccess];
            }
            return ['Working Pass', '#DAA520', $exhibitorAccess];
        }
        return ['Unknown', '#808080', []];
    }

    private function sendWhatsAppNotification($name, $company, $typeVal, $imageUrl = null)
    {
        try {
            $timeCheckin = Carbon::now('Asia/Jakarta')->format('H:i');

            $message = "✅ *INDONESIA MINER 2026 — Check-In Alert*\n\n"
                . "*{$name}*\n"
                . "{$company}\n"
                . "🎫 {$typeVal}\n\n"
                . "📍 Lobby Utama, The Westin Jakarta\n"
                . "🕐 {$timeCheckin} WIB";

            $wa        = new WhatsappApi();
            $wa->phone = '120363389769846913';

            if ($imageUrl) {
                $message .= "\n📸 Foto: {$imageUrl}";
            }

            $wa->message = $message;
            $wa->WhatsappMessageGroup();
        } catch (\Exception $e) {
            Log::error("Failed to send WhatsApp notification: " . $e->getMessage());
        }
    }

    private function sendWebhook($url, $payload)
    {
        try {
            $response = Http::timeout(15)->post($url, $payload);
            if ($response->status() !== 200) {
                Log::error("Webhook failed with status: {$response->status()}, Response: {$response->body()}");
            }
        } catch (\Exception $e) {
            Log::error("Error sending webhook: " . $e->getMessage());
        }
    }

    public function getAllNgrok()
    {
        try {
            $ngrokData = DB::select("SELECT id, link, type, created_at, updated_at FROM ngrok");

            $data = array_map(function ($row) {
                return [
                    'id'         => $row->id,
                    'link'       => $row->link,
                    'link_local' => $row->link_local,
                    'type'       => $row->type,
                    'created_at' => isset($row->created_at) && $row->created_at
                        ? (method_exists($row->created_at, 'toIso8601String') ? $row->created_at->toIso8601String() : (string) $row->created_at)
                        : null,
                    'updated_at' => isset($row->updated_at) && $row->updated_at
                        ? (method_exists($row->updated_at, 'toIso8601String') ? $row->updated_at->toIso8601String() : (string) $row->updated_at)
                        : null,
                ];
            }, $ngrokData);

            return $this->ok('Success', $data);
        } catch (\Exception $e) {
            return $this->err($e->getMessage(), 500);
        }
    }

    public function getNgrokById($ngrokId)
    {
        try {
            $ngrokData = DB::select("SELECT id, link, type, created_at, updated_at FROM ngrok WHERE id = ?", [$ngrokId]);

            if (!$ngrokData) {
                return $this->err('Record not found', 404);
            }

            $row = $ngrokData[0];
            return $this->ok('Success', [
                'id'         => $row->id,
                'link'       => $row->link,
                'type'       => $row->type,
                'created_at' => isset($row->created_at) && $row->created_at
                    ? (method_exists($row->created_at, 'toIso8601String') ? $row->created_at->toIso8601String() : (string) $row->created_at)
                    : null,
                'updated_at' => isset($row->updated_at) && $row->updated_at
                    ? (method_exists($row->updated_at, 'toIso8601String') ? $row->updated_at->toIso8601String() : (string) $row->updated_at)
                    : null,
            ]);
        } catch (\Exception $e) {
            return $this->err($e->getMessage(), 500);
        }
    }
}
