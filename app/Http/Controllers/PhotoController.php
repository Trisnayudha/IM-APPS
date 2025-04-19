<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PhotoController extends Controller
{
    protected string $phone;
    protected string $message;

    /**
     * Tampilkan view camera (resources/views/camera.blade.php).
     */
    public function showCamera()
    {
        return view('camera');
    }

    /**
     * Tangkap data:image dari front-end, panggil OpenAI dengan data URI,
     * lalu kirim jawabannya via WhatsApp NusaGateway.
     */
    public function capture(Request $request)
    {
        // 1. Validasi
        $request->validate([
            'image' => 'required|string',
            'phone' => ['required', 'regex:/^62\d+$/'],
        ]);

        // 2. Ambil kembali data URI (misal: data:image/png;base64,...)
        $dataUrl = $request->input('image');

        // 3. Panggil OpenAI Chat Completions dengan vision payload
        $resp = Http::withToken(env('OPENAI_API_KEY'))
            ->post('https://api.openai.com/v1/chat/completions', [
                'model'      => 'gpt-4o-mini',
                'max_tokens' => 512,
                'messages'   => [
                    [
                        'role'    => 'system',
                        'content' => 'You are a helpful assistant that can answer questions from images.'
                    ],
                    [
                        'role'    => 'user',
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => 'Tolong jawab soal pada gambar berikut.'
                            ],
                            [
                                'type'      => 'image_url',
                                'image_url' => [
                                    'url'    => $dataUrl,
                                    'detail' => 'auto'
                                ]
                            ]
                        ]
                    ]
                ],
            ]);

        if (! $resp->successful()) {
            return response()->json([
                'status'  => 'error_openai',
                'message' => $resp->body(),
            ], 500);
        }

        $body          = $resp->json();
        $this->message = trim($body['choices'][0]['message']['content'] ?? '');
        $this->phone   = $request->input('phone');

        // 4. Kirim jawaban via WA NusaGateway
        $waStatus = $this->sendWhatsapp();

        return response()->json([
            'status' => $waStatus,
            'answer' => $this->message,
        ]);
    }

    /**
     * Cek nomor WA dan kirim pesan melalui NusaGateway.
     */
    protected function sendWhatsapp(): string
    {
        $token = env('NUSAGATEWAY_API_TOKEN');

        // a) cek validitas nomor
        $check = Http::asForm()->post('https://nusagateway.com/api/check-number.php', [
            'phone' => $this->phone,
            'token' => $token,
        ])->json();

        if (($check['status'] ?? '') !== 'valid') {
            return 'invalid_number';
        }

        // b) kirim pesan
        $send = Http::asForm()->post('https://nusagateway.com/api/send-message.php', [
            'token'   => $token,
            'phone'   => $this->phone,
            'message' => $this->message,
        ])->json();

        return $send['status'] ?? 'error';
    }
}
