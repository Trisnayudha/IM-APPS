<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\Ai\AiService;
use Illuminate\Http\Request;

class AiController extends Controller
{
    protected $aiService;
    public function __construct(AiService $aiService)
    {
        $this->aiService = $aiService;
    }
    public function suggestMeet(Request $request)
    {
        $id = auth('sanctum')->user()->id;
        $category = $request->input('category');
        $company_id = $request->input('company_id');
        $delegate_id = $request->input('delegate_id');   // opsional
        $product_id = $request->input('product_id');     // opsional

        if (!$category || !$company_id) {
            return response()->json([
                'status' => 422,
                'message' => 'category dan company_id wajib diisi.',
                'payload' => null
            ], 422);
        }

        $data = $this->aiService->getSuggestMeet($id, $category, $company_id, $delegate_id, $product_id);

        return response()->json([
            'status' => 200,
            'message' => 'Generate Text AI',
            'payload' => $data,
        ]);
    }
}
