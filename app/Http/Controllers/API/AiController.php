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
        $id =  auth('sanctum')->user()->id ?? null;
        $category = $request->category;
        $data = $this->aiService->getSuggestMeet($id, $category);
        $response['status'] = 200;
        $response['message'] = 'Generate Text AI';
        $response['payload'] = $data;
        return response()->json($response);
    }
}
