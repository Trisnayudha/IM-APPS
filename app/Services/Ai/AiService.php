<?php

namespace App\Services\Ai;

use App\Models\Auth\User;
use App\Repositories\AiRepositoryInterface;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class AiService implements AiRepositoryInterface
{

    public function getSuggestMeet($id, $category)
    {
        $user = User::find($id);

        if (!$user) {
            return ['error' => 'User not found'];
        }

        // Prompt templates
        $prompts = [
            'investment' => "Tulis pesan kerja sama investasi ke {company_name} secara profesional dan ringkas. Tujuannya adalah menjalin diskusi awal.",
            'partnership' => "Tulis pesan singkat dan profesional untuk menawarkan kolaborasi strategis dengan {company_name}.",
            'technology' => "Buat pesan pendek profesional untuk mengajak {company_name} berdiskusi tentang potensi kerja sama teknologi.",
            'supply' => "Tulis pesan formal dan singkat untuk menawarkan kerja sama dalam rantai pasok ke {company_name}."
        ];

        if (!isset($prompts[$category])) {
            return ['error' => 'Invalid category'];
        }

        $prompt = str_replace('{company_name}', $user->company_name ?? 'perusahaan Anda', $prompts[$category]);

        // Call OpenAI
        $response = Http::withToken(env('OPENAI_API_KEY'))
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4',
                'messages' => [
                    ['role' => 'system', 'content' => 'Kamu adalah asisten yang membantu membuat pesan bisnis profesional.'],
                    ['role' => 'user', 'content' => $prompt]
                ],
                'max_tokens' => 300,
                'temperature' => 0.7,
            ]);

        if (!$response->successful()) {
            return ['error' => 'AI generation failed', 'detail' => $response->body()];
        }

        return $response->json()['choices'][0]['message']['content'] ?? 'Tidak ada respon dari AI.';
    }

    public function getRoomChat()
    {
        //
    }
}
