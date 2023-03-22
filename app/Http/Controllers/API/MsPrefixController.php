<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\MsPrefix\MsService;
use Illuminate\Http\Request;

class MsPrefixController extends Controller
{
    protected $msService;
    public function __construct(MsService $msService)
    {
        $this->msService = $msService;
    }
    public function showMsPrefixPhoneAll()
    {
        $payload = $this->msService->getMsPrefixPhone();
        $response['status'] = 200;
        $response['message'] = 'Successfully show prefix phone';
        $response['payload'] = $payload;
        return response()->json($response);
    }

    public function showMsPrefixPhoneDetail(Request $request)
    {
        $code = $request->code;
        $payload = $this->msService->getMsPrefixPhoneDetail($code);
        $response['status'] = 200;
        $response['message'] = 'Successfully show detail prefix phone';
        $response['payload'] = $payload;
        return response()->json($response);
    }
}
