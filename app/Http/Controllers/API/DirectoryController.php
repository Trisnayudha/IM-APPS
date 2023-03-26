<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\Company\CompanyService;
use Illuminate\Http\Request;

class DirectoryController extends Controller
{
    protected $companyService;
    public function __construct(CompanyService $companyService)
    {
        $this->companyService = $companyService;
    }

    public function getContactDetail(Request $request)
    {
        $id = $request->id;

        $findContact = $this->companyService->getDetailContactById($id);
        if ($findContact) {
            $response['status'] = 200;
            $response['message'] = 'Successfully show contact detail';
            $response['payload'] = $findContact;
        } else {
            $response['status'] = 404;
            $response['message'] = 'Contact Not Found';
            $response['payload'] = null;
        }
        return response()->json($response);
    }

    public function sendConcact(Request $request)
    {
        //
    }

    public function listTimeline(Request $request)
    {
        $type = $request->type;
        $id = $request->id;
        $search = $request->search;
        $tags = $request->tags;
        $category = $request->category;
        $filter = $request->filter;
        $company = $request->company;
        $is_directory = $request->is_directory;

        $data = $this->companyService->getListTimeline($type, $id, $search, $tags, $category, $filter, $company, $is_directory);
        $response['status'] = 200;
        $response['message'] = 'Successfully show data';
        $response['payload'] = $data;
        return response()->json($response);
    }
}
