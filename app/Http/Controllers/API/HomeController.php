<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\MsPrefix\MsService;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    protected $msService;
    public function __construct(MsService $msService)
    {
        $this->msService = $msService;
    }
    public function banner()
    {
        $banner = $this->msService->getBannerHome();
        $response['status'] = 200;
        $response['message'] = 'Successfully show Image Banner';
        $response['payload'] = $banner;
        return response()->json($response);
    }

    public function sponsors(Request $request)
    {
        $type = $request->type;
    }
}
