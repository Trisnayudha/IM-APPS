<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class PhotoController extends Controller
{
    protected string $phone;
    protected string $message;

    public function showCamera()
    {
        return view('camera');
    }

    public function capture(Request $request)
    {
        // 1. Validasi
        $request->validate([
            'image' => 'required|string',
            'phone' => ['required', 'regex:/^62\d+$/'],
        ]);

        // 2. Decode & simpan
        $data     = preg_replace('#^data:image/\w+;base64,#i', '', $request->image);
        $image    = base64_decode($data);
        $filename = uniqid('img_', true) . '.png';
        $path     = storage_path('app/public/uploads/' . $filename);
        if (!file_exists(dirname($path))) mkdir(dirname($path), 0755, true);
        file_put_contents($path, $image);

        // 3. Panggil OpenAI via Guzzle (multipart/form-data)
        $client = new Client();
        $resp   = $client->request('POST', 'https://api.openai.com/v1/chat/completions', [
            'headers'   => [
                'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
                // omit Content-Type: Guzzle akan set sesuai multipart
            ],
            'multipart' => [
                [
                    'name'     => 'model',
                    'contents' => 'gpt-4o-mini',
                ],
                [
                    'name'     => 'messages',
                    'contents' => json_encode([
                        ['role' => 'user', 'content' => 'Tolong jawab soal pada gambar berikut.']
                    ]),
                ],
                [
                    'name'     => 'file',
                    'contents' => fopen($path, 'r'),
                    'filename' => $filename,
                ],
                [
                    'name'     => 'purpose',
                    'contents' => 'vision',
                ],
            ],
        ]);

        $body = json_decode($resp->getBody()->getContents(), true);
        $this->message = trim($body['choices'][0]['message']['content']);
        $this->phone   = $request->phone;

        // 4. Kirim WA via NusaGateway
        $status = $this->sendWhatsapp();

        return response()->json([
            'status' => $status,
            'answer' => $this->message,
        ]);
    }

    protected function sendWhatsapp(): string
    {
        $token = env('NUSAGATEWAY_API_TOKEN');

        // 1. cek nomor
        $check = Http::asForm()->post('https://nusagateway.com/api/check-number.php', [
            'phone' => $this->phone,
            'token' => $token,
        ])->json();

        if (($check['status'] ?? '') !== 'valid') {
            return 'invalid_number';
        }

        // 2. kirim pesan
        $send = Http::asForm()->post('https://nusagateway.com/api/send-message.php', [
            'token'   => $token,
            'phone'   => $this->phone,
            'message' => $this->message,
        ])->json();

        return $send['status'] ?? 'error';
    }
}
