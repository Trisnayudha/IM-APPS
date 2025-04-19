<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

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
     * Tangkap gambar, pakai prompt BUMN built‑in, panggil OpenAI Vision‑QA, lalu kirim WA
     */
    public function capture(Request $request)
    {
        // 1. Validasi: hanya image + phone
        $request->validate([
            'image' => 'required|string',
            'phone' => ['required', 'regex:/^62\d+$/'], // awali 62
        ]);

        // 2. Ambil data URI dari front‑end
        $dataUrl = $request->input('image');

        // 3. Definisikan prompt soal BUMN langsung di sini
        $prompt = "Tolong bantu saya menjawab soal Tes Kompetensi Dasar (TKD), " .
            "Tes Intelegensi Umum (TIU), Tes Wawasan Kebangsaan (TWK), dan " .
            "Tes Karakteristik Pribadi (TKP) seleksi masuk BUMN berikut. " .
            "Jelaskan langkah demi langkah, lalu berikan jawaban akhirnya.";

        // 4. Panggil OpenAI dengan vision‑enabled chat
        $openaiResp = Http::withToken(env('OPENAI_API_KEY'))
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
                            ['type' => 'text',      'text' => $prompt],
                            ['type' => 'image_url', 'image_url' => ['url' => $dataUrl]],
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

        $body          = $openaiResp->json();
        $this->message = trim($body['choices'][0]['message']['content'] ?? '');
        $this->phone   = $request->input('phone');

        // 5. Kirim jawaban via WhatsApp NusaGateway
        $waStatus = $this->sendWhatsapp();

        return response()->json([
            'status' => $waStatus,
            'answer' => $this->message,
        ]);
    }

    /**
     * Cek nomor WA & kirim pesan melalui NusaGateway
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
