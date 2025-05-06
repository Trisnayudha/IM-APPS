<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Insight\InsightModel;
use Illuminate\Http\Request;

class InsightController extends Controller
{
    public function insight(Request $request)
    {
        $id =  auth('sanctum')->user()->id ?? null;
        $save = new InsightModel();
        $save->users_id = $id;
        $save->email = $request->email;
        $save->text = $request->text;
        $save->save();
        $response['status'] = 200;
        $response['message'] = 'Successfully show prefix phone';
        $response['payload'] = $save;
        return response()->json($response);
    }
}
