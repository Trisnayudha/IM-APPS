<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class PhotoController extends Controller
{
    protected string $phone;
    protected string $message;

    /**
     * Tampilkan halaman camera (resources/views/camera.blade.php)
     */
    public function showCamera()
    {
        return view('camera');
    }

    /**
     * Terima gambar, simpan, panggil OpenAI Vision‑QA, lalu kirim WA
     */
    public function capture(Request $request)
    {
        // 1. Validasi
        $request->validate([
            'image' => 'required|string',
            'phone' => ['required', 'regex:/^62\d+$/'],
        ]);

        // 2. Decode base64 & simpan ke storage/app/public/uploads
        $data     = preg_replace('#^data:image/\w+;base64,#i', '', $request->input('image'));
        $image    = base64_decode($data);
        $filename = uniqid('img_', true) . '.png';
        $diskPath = 'public/uploads/' . $filename;
        $fullPath = storage_path('app/' . $diskPath);

        if (! file_exists(dirname($fullPath))) {
            mkdir(dirname($fullPath), 0755, true);
        }
        file_put_contents($fullPath, $image);

        // 3. Buat URL publik (pastikan sudah menjalankan php artisan storage:link)
        $publicUrl = asset(str_replace('public/', 'storage/', $diskPath));

        // 4. Panggil OpenAI Chat API dengan image_url
        $openaiResp = Http::withToken(env('OPENAI_API_KEY'))
            ->post('https://api.openai.com/v1/chat/completions', [
                'model'    => 'gpt-4o-mini',
                'max_tokens' => 512,
                'messages' => [
                    [
                        'role'    => 'system',
                        'content' => 'You are a helpful assistant that can answer questions from images.',
                    ],
                    [
                        'role'    => 'user',
                        // **Content** harus array dari segment text+image
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => 'Tolong jawab soal pada gambar berikut.',
                            ],
                            [
                                'type'      => 'image_url',
                                'image_url' => [
                                    'url' => $publicUrl,
                                ],
                            ],
                        ],
                    ],
                ],
            ]);

        if (! $openaiResp->successful()) {
            return response()->json([
                'status'  => 'error_openai',
                'message' => $openaiResp->body(),
            ], 500);
        }

        $body = $openaiResp->json();
        $this->message = trim($body['choices'][0]['message']['content'] ?? '');
        $this->phone   = $request->input('phone');

        // 5. Kirim WA via NusaGateway
        $waStatus = $this->sendWhatsapp();

        return response()->json([
            'status' => $waStatus,
            'answer' => $this->message,
        ]);
    }

    /**
     * Cek nomor WA & kirim pesan
     */
    protected function sendWhatsapp(): string
    {
        $token = env('NUSAGATEWAY_API_TOKEN');

        // cek nomor
        $check = Http::asForm()->post('https://nusagateway.com/api/check-number.php', [
            'phone' => $this->phone,
            'token' => $token,
        ])->json();

        if (($check['status'] ?? '') !== 'valid') {
            return 'invalid_number';
        }

        // kirim pesan
        $send = Http::asForm()->post('https://nusagateway.com/api/send-message.php', [
            'token'   => $token,
            'phone'   => $this->phone,
            'message' => $this->message,
        ])->json();

        return $send['status'] ?? 'error';
    }
}
