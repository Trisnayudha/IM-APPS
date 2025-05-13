<?php

namespace App\Services\Ai;

use App\Models\Auth\User;
use App\Models\Company\Company;
use App\Models\Company\CompanyRepresentative;
use App\Models\Products\Product;
use App\Repositories\AiRepositoryInterface;
use Illuminate\Support\Facades\Http;

class AiService implements AiRepositoryInterface
{
    protected $promptTemplates = [
        'Suggest Meeting' => "
I’m {user_name} from {user_company_name}.
I’d like to meet with {company_name} to discuss a possible collaboration—please have {delegate_name} reach out to set up a time.
Write exactly 2 clear sentences in English, no salutations or subject lines.",

        'Receive Quotation' => "
I’m {user_name} from {user_company_name}.
Please send me a current quote for {company_name}'s products/services.
Write exactly 2 clear sentences in English, no salutations or subject lines.",

        'Be Contacted by Phone' => "
I’m {user_name} from {user_company_name}.
Ask {company_name} to call me for a brief discussion—preferably have {delegate_name} reach out.
Write exactly 2 clear sentences in English, no salutations or subject lines.",

        'Receive Documentation' => "
I’m {user_name} from {user_company_name}.
Please send the product or service documentation for {company_name}.
Write exactly 2 clear sentences in English, no salutations or subject lines.",

        'Receive Pricing Information' => "
I’m {user_name} from {user_company_name}.
I’d like detailed pricing information for {company_name}'s offerings.
Write exactly 2 clear sentences in English, no salutations or subject lines.",
    ];

    /**
     * @param  int         $user_id
     * @param  string      $category       // e.g. "Suggest Meeting"
     * @param  int         $company_id
     * @param  int|null    $delegate_id
     * @param  int|null    $product_id
     * @return string|array
     */
    public function getSuggestMeet(
        $user_id,
        $category,
        $company_id,
        $delegate_id = null,
        $product_id = null
    ) {
        // Fetch user and company
        $user    = User::find($user_id);
        $company = Company::find($company_id);

        if (! $user || ! $company) {
            return ['error' => 'User or Company not found'];
        }

        // Optional contexts
        $delegate = $delegate_id ? CompanyRepresentative::find($delegate_id) : null;
        $product  = $product_id  ? Product::find($product_id) : null;

        // Load template
        $template = $this->promptTemplates[$category] ?? null;
        if (! $template) {
            return ['error' => 'Invalid category'];
        }

        // Replace placeholders
        $prompt = str_replace(
            [
                '{user_name}',
                '{user_job_title}',
                '{user_company_name}',
                '{company_name}',
                '{delegate_name}',
                '{product_name}',
            ],
            [
                $user->name,
                $user->job_title     ?? '-',
                $user->company_name ?? '-',
                $company->name,
                $delegate->name     ?? '',
                $product->name      ?? '',
            ],
            $template
        );

        // Append product context if exists
        if ($product) {
            $prompt .= " Also mention the product “{$product->name}”.";
        }

        // Append delegate context if exists
        if ($delegate) {
            $prompt .= " And ask for {delegate_name} to reach out.";
        }

        // Call OpenAI
        $response = Http::withToken(env('OPENAI_API_KEY'))
            ->post('https://api.openai.com/v1/chat/completions', [
                'model'       => 'gpt-4',
                'messages'    => [
                    ['role' => 'system', 'content' => 'You are a professional business message assistant.'],
                    ['role' => 'user',   'content' => trim($prompt)],
                ],
                'max_tokens'  => 200,
                'temperature' => 0.7,
            ]);

        if (! $response->successful()) {
            return ['error' => 'AI generation failed', 'detail' => $response->body()];
        }

        // Clean up the output: remove new lines & excess spaces
        $raw = $response->json()['choices'][0]['message']['content'] ?? '';
        $singleLine = preg_replace('/\s+/', ' ', str_replace(["\r", "\n"], ' ', $raw));
        $clean = trim($singleLine);

        return $clean ?: 'No response from AI.';
    }

    public function getRoomChat()
    {
        // Future implementation...
    }
}
