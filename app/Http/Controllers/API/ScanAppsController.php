<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ScanAppsController extends Controller
{
    public function checkin(Request $request)
    {
        $data = $request->all();

        $codePayment = $data['code_payment'] ?? null;
        $linkWebhook = $data['link_webhook'] ?? null;
        $day = $data['day'] ?? null;
        $name = $data['name'] ?? null;
        $job = $data['job_title'] ?? null;
        $company = $data['company'] ?? null;
        $image = $data['image'] ?? null;
        if (!$codePayment || !$linkWebhook || !$day) {
            return response()->json([
                'status' => 0,
                'message' => 'code_payment, link_webhook, and day are required',
                'data' => null
            ], 400);
        }

        try {
            $result = DB::table('payment as p')
                ->join('users_delegate as ud', function ($join) {
                    $join->on('ud.users_id', '=', 'p.users_id')
                        ->on('ud.events_id', '=', 'p.events_id');
                })
                ->where('p.code_payment', $codePayment)
                ->where('p.aproval_quota_users', 1)
                ->select('p.id as payment_id', 'ud.id as delegate_id', 'ud.users_id')
                ->first();

            if (!$result) {
                return response()->json([
                    'status' => 0,
                    'message' => 'QR Code tidak valid',
                    'data' => null
                ]);
            }

            $paymentId = $result->payment_id;
            $delegateId = $result->delegate_id;

            // Determine which checkin_day column based on the provided day
            $col = null;
            try {
                $dt = Carbon::parse($day);
                if ($dt->year == 2025 && $dt->month == 6) {
                    if ($dt->day == 10) {
                        $col = 'date_day1';
                    } elseif ($dt->day == 11) {
                        $col = 'date_day2';
                    } elseif ($dt->day == 12) {
                        $col = 'date_day3';
                    }
                }
            } catch (\Exception $e) {
                // If parsing fails, do not update any checkin_day
            }

            $filename = null;
            if ($image) {
                $filename = $codePayment . '_' . Carbon::now()->timestamp . '.jpg';
                Storage::put('uploads/' . $filename, base64_decode($image));
            }

            if ($col) {
                DB::table('users_delegate')
                    ->where('id', $delegateId)
                    ->update([
                        $col => $day,
                        'image' => $filename
                    ]);
            }

            // Update users table with name, job, and company
            DB::table('users')
                ->where('id', $result->users_id)
                ->update([
                    'name' => $name,
                    'job_title' => $job,
                    'company_name' => $company
                ]);

            $payload = [
                'name' => $name,
                'company' => $company,
                'job_title' => $job,
                'code_payment' => $codePayment,
                'day' => $day
            ];

            // Include full image URL in response if image exists
            if ($filename) {
                $baseUrl = url('/');
                $uploadFolder = Storage::disk('public')->url('uploads/');
                $payload['image_url'] = "{$baseUrl}/{$uploadFolder}{$filename}";
            }

            // Send simplified payload to the webhook URL asynchronously
            $webhookPayload = [
                'name' => $name,
                'job_title' => $job,
                'company' => $company,
                'code_payment' => $codePayment
            ];

            $this->sendWebhook($linkWebhook, $webhookPayload);

            return response()->json([
                'status' => 1,
                'message' => 'Check-in berhasil',
                'data' => $payload
            ]);
        } catch (\Exception $e) {
            Log::error("Error in /checkin: " . $e->getMessage());
            return response()->json([
                'status' => 0,
                'message' => 'Error: ' . $e->getMessage(),
                'data' => null
            ], 500);
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

    public function listDelegate(Request $request)
    {
        $search = $request->query('search', '');

        if (strlen($search) < 4) {
            return response()->json([
                'status' => 0,
                'message' => 'Search term must be at least 4 characters',
                'data' => []
            ], 400);
        }

        try {
            $likePattern = "%{$search}%";
            $results = DB::table('payment as p')
                ->join('users as u', 'u.id', '=', 'p.users_id')
                ->join('events_tickets as et', 'et.id', '=', 'p.package_id')
                ->where(function ($query) use ($likePattern) {
                    $query->where('u.name', 'like', $likePattern)
                        ->orWhere('u.company_name', 'like', $likePattern);
                })
                ->where('p.aproval_quota_users', 1)
                ->where('p.events_id', 13)
                ->whereNotIn('p.status', ['trash', 'Waiting', 'cancelled'])
                ->orderByRaw("CASE WHEN u.name NOT LIKE '% %' THEN 0 ELSE 1 END, u.name")
                ->limit(5)
                ->select('u.name', 'u.job_title', 'u.company_name', 'p.code_payment', 'et.title', 'et.type')
                ->get();

            $data = [];
            foreach ($results as $r) {
                list($ticketLabel, $ticketColor) = $this->mapTicketType($r->type, $r->title);
                $data[] = [
                    'name' => $r->name,
                    'job_title' => $r->job_title,
                    'company' => $r->company_name,
                    'code_payment' => $r->code_payment,
                    'checkin_field' => null,
                    'ticket_type' => $ticketLabel,
                    'ticket_color' => $ticketColor
                ];
            }

            return response()->json([
                'status' => 1,
                'message' => 'Success',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    // Add mapTicketType method in the controller if not already defined
    private function mapTicketType($ticketType, $ticketTitle)
    {
        // Logic for mapping ticket types, return a label and color based on the ticket type
        // Example
        if ($ticketType == 'VIP') {
            return ['VIP Ticket', 'gold'];
        } elseif ($ticketType == 'Regular') {
            return ['Regular Ticket', 'blue'];
        }
        return ['General Ticket', 'green'];
    }

    public function scanQr(Request $request)
    {
        $data = $request->json()->all();
        $codePayment = $data['code_payment'] ?? null;
        $day = $data['day'] ?? null;
        if (!$codePayment || !$day) {
            return response()->json([
                'status' => 0,
                'message' => 'code_payment and day are required',
                'data' => null
            ], 400);
        }

        try {
            // Parse the day parameter (ISO or Indonesian format) and map to checkin_day field
            $col = null;
            try {
                $dt = \Carbon\Carbon::parse($day);
                if ($dt->year == 2025 && $dt->month == 6) {
                    if ($dt->day == 10) {
                        $col = 'date_day1';
                    } elseif ($dt->day == 11) {
                        $col = 'date_day2';
                    } elseif ($dt->day == 12) {
                        $col = 'date_day3';
                    }
                }
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Invalid day format',
                    'data' => null
                ], 400);
            }

            $result = DB::table('payment as p')
                ->join('users as u', 'u.id', '=', 'p.users_id')
                ->join('events_tickets as et', 'et.id', '=', 'p.package_id')
                ->where('p.code_payment', $codePayment)
                ->where('p.aproval_quota_users', 1)
                ->whereNotIn('p.status', ['trash', 'Waiting', 'cancelled'])
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

            if ($result) {
                $paymentId = $result->payment_id;
                $name = $result->name;
                $jobTitle = $result->job_title;
                $company = $result->company_name;
                $category = $result->category;
                $title = $result->title;
                $typeVal = $result->type;

                if ($col && $category != 'All Access') {
                    if (preg_match('/^Day (\d+)$/', $category, $matches)) {
                        $accessDay = (int) $matches[1];
                        if ($dt->day != (9 + $accessDay)) {
                            return response()->json([
                                'status' => 0,
                                'message' => "Your ticket grants Day {$accessDay} Access. See you on June " . (9 + $accessDay) . " for Indonesia Miner 2025!",
                                'data' => null
                            ], 403);
                        }
                    }
                }

                $image = DB::table('users_delegate')->where('payment_id', $paymentId)->value('image');
                $imageUrl = $image ? url("uploads/{$image}") : null;

                if ($col) {
                    DB::table('users_delegate')
                        ->where('payment_id', $paymentId)
                        ->update([$col => now()]);
                }

                list($ticketLabel, $ticketColor) = $this->mapTicketType($typeVal, $title);

                // Send WhatsApp notification for speakers (example)
                if ($typeVal && strtolower($typeVal) == 'speaker') {
                    $this->sendWhatsAppNotification($name, $company, $typeVal);
                }

                return response()->json([
                    'status' => 1,
                    'message' => 'Scan QR successful',
                    'data' => [
                        'name' => $name,
                        'job_title' => $jobTitle,
                        'company' => $company,
                        'code_payment' => $codePayment,
                        'checkin_field' => $col,
                        'ticket_type' => $ticketLabel,
                        'ticket_color' => $ticketColor,
                        'image' => $imageUrl
                    ]
                ]);
            } else {
                return response()->json([
                    'status' => 0,
                    'message' => 'Delegate not found',
                    'data' => null
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    private function sendWhatsAppNotification($name, $company, $typeVal)
    {
        $jakartaTime = \Carbon\Carbon::now('Asia/Jakarta');
        $timeCheckin = $jakartaTime->format('H:i');
        $apiUrl = "https://nusagateway.com/api/send-message.php";
        $payload = [
            "token" => env('NUSA_GATEWAY_TOKEN'),
            "phone" => "120363389769846913",
            "message" => "✅ Team, *{$name}* dari {$company} melakukan check-in sebagai {$typeVal} di Lobby Utama The Westin Jakarta 🏨 pada pukul {$timeCheckin} WIB hari ini."
        ];

        try {
            $response = Http::post($apiUrl, $payload);
            $response->throw();
        } catch (\Exception $e) {
            Log::error("Failed to send WhatsApp notification: " . $e->getMessage());
        }
    }

    public function getAllNgrok()
    {
        try {
            $ngrokData = DB::select("SELECT id, link, type, created_at, updated_at FROM ngrok");

            $ngrokList = [];
            foreach ($ngrokData as $row) {
                $ngrokList[] = [
                    'id' => $row->id,
                    'link' => $row->link,
                    'type' => $row->type,
                    'created_at' => isset($row->created_at) && $row->created_at ? (method_exists($row->created_at, 'toIso8601String') ? $row->created_at->toIso8601String() : (string)$row->created_at) : null,
                    'updated_at' => isset($row->updated_at) && $row->updated_at ? (method_exists($row->updated_at, 'toIso8601String') ? $row->updated_at->toIso8601String() : (string)$row->updated_at) : null
                ];
            }

            return response()->json([
                'status' => 1,
                'data' => $ngrokList
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Error: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function getNgrokById($ngrokId)
    {
        try {
            $ngrokData = DB::select("SELECT id, link, type, created_at, updated_at FROM ngrok WHERE id = ?", [$ngrokId]);

            if ($ngrokData) {
                $row = $ngrokData[0];
                return response()->json([
                    'status' => 1,
                    'data' => [
                        'id' => $row->id,
                        'link' => $row->link,
                        'type' => $row->type,
                        'created_at' => isset($row->created_at) && $row->created_at ? (method_exists($row->created_at, 'toIso8601String') ? $row->created_at->toIso8601String() : (string)$row->created_at) : null,
                        'updated_at' => isset($row->updated_at) && $row->updated_at ? (method_exists($row->updated_at, 'toIso8601String') ? $row->updated_at->toIso8601String() : (string)$row->updated_at) : null
                    ]
                ]);
            } else {
                return response()->json([
                    'status' => 0,
                    'message' => 'Record not found',
                    'data' => null
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Error: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }
}
