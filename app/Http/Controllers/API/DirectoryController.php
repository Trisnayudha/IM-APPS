<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\Company\CompanyService;
use App\Traits\Directory;
use Illuminate\Http\Request;

class DirectoryController extends Controller
{
    use Directory;

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
        $slug = $request->slug;
        $search = $request->search;
        $tags = $request->tags;
        $category = $request->category;
        $filter = $request->filter;
        $company_id = $this->companyService->getCompanyBySlug($slug);
        if ($company_id) {
            $data = $this->companyService->getListTimeline($type, $company_id->id, $category, $search, $tags, $filter);
            $response['status'] = 200;
            $response['message'] = 'Successfully show data';
            $response['payload'] = $data;
        } else {
            $response['status'] = 404;
            $response['message'] = 'Company Not Found';
            $response['payload'] = null;
        }
        return response()->json($response);
    }

    public function detailProduct($slug)
    {
        $id =  auth('sanctum')->user()->id ?? null;
        $product = $this->companyService->getDetailProduct($slug, $id);
        $relate = $this->companyService->getRelateProduct($slug);
        self::countVisitPage('Product', 'Out Events', $product->id, $product->company_id, 11, $id);
        $data = [
            'product' => $product,
            'releate' => $relate
        ];
        $response['status'] = 200;
        $response['message'] = 'Successfully show data';
        $response['payload'] = $data;
        return response()->json($response);
    }

    public function detailNews($slug)
    {
        $id =  auth('sanctum')->user()->id ?? null;

        $news = $this->companyService->getDetailNews($slug, $id);
        $relate = $this->companyService->getRelateNews($slug);
        self::countVisitPage('News', 'Out Events', $news->id, $news->company_id, 11, $id);
        $data = [
            'news' => $news,
            'releate' => $relate
        ];
        $response['status'] = 200;
        $response['message'] = 'Successfully show data';
        $response['payload'] = $data;
        return response()->json($response);
    }


    public function detailProject($slug)
    {
        $id =  auth('sanctum')->user()->id ?? null;
        $project = $this->companyService->getDetailProject($slug, $id);
        $relate = $this->companyService->getRelateProject($slug);
        self::countVisitPage('Project', 'Out Events', $project->id, $project->company_id, 11, $id);
        $data = [
            'project' => $project,
            'releate' => $relate
        ];
        $response['status'] = 200;
        $response['message'] = 'Successfully show data';
        $response['payload'] = $data;
        return response()->json($response);
    }

    public function detailMedia($slug)
    {
        $id =  auth('sanctum')->user()->id ?? null;
        $media = $this->companyService->getDetailMedia($slug, $id);
        $relate = $this->companyService->getRelateMedia($slug);
        self::countVisitPage('Media', 'Out Events', $media->id, $media->company_id, 11, $id);
        $data = [
            'media' => $media,
            'releate' => $relate
        ];
        $response['status'] = 200;
        $response['message'] = 'Successfully show data';
        $response['payload'] = $data;
        return response()->json($response);
    }

    public function postBookmarkTimeline(Request $request)
    {
        $id =  auth('sanctum')->user()->id ?? null;
        $type = $request->type;
        $bookmark_id = $request->bookmark_id;
        $type = $request->type;
        if ($id) {
            $data = $this->companyService->postBookmark($id, $bookmark_id, $type);
            $response['status'] = 200;
            $response['message'] = $data;
            $response['payload'] = null;
        } else {
            $response['status'] = 401;
            $response['message'] = 'Unauthorized';
            $response['payload'] = null;
        }
        return response()->json($response);
    }
}
