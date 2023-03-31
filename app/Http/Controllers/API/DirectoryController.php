<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Auth\User;
use App\Models\Company\CompanyRepresentative;
use App\Models\Company\CompanySuggestMeet;
use App\Services\Company\CompanyService;
use App\Services\Email\EmailService;
use App\Traits\Directory;
use Illuminate\Http\Request;

class DirectoryController extends Controller
{
    use Directory;

    protected $companyService;
    protected $emailService;
    public function __construct(CompanyService $companyService, EmailService $emailService)
    {
        $this->companyService = $companyService;
        $this->emailService = $emailService;
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

    public function postSendMeet(Request $request)
    {

        $company_id = $request->company_id;
        $delegation_id = $request->delegate_id;
        $category_id = $request->category_id;
        $message = $request->message;
        $product_id = $request->product_id;
        $users_id = auth('sanctum')->user()->id ?? null;

        $findDele = CompanyRepresentative::find($delegation_id);
        $findUser = User::find($users_id);


        $save = new CompanySuggestMeet();
        $save->created_at = date('Y-m-d H:i:s');
        $save->flag_visit = (self::isInEvents() ? 'In' : 'Out') . ' Events';
        $save->day = date('d');
        $save->month = date('m');
        $save->year = date('Y');
        $save->company_id = $company_id;
        $save->users_id = $users_id;
        $save->users_name = $findUser->name;
        $save->product_id = $product_id;
        $save->users_company_name = $findUser->company_name;
        if (!empty($findDele->id)) {
            $save->company_representative_id = $findDele->id;
        }
        $save->md_category_suggest_meet_id = $category_id;
        $save->message = $message;
        $save->save();

        if (empty($findDele->id)) {
            self::usersActivity('Suggest Meeting to Company', (self::isInEvents() ? 'In' : 'Out') . ' Events', self::getIdEvents(), $save->id);
        } else {
            self::usersActivity('Suggest Meeting to Company Delegation', (self::isInEvents() ? 'In' : 'Out') . ' Events', self::getIdEvents(), $save->id);
        }

        $findSuggest = $this->companyService->getDetailFormSuggest($save->id);
        if (!empty($findSuggest->id) && $product_id == null) {
            $users_name = $findSuggest->users_name;
            $category_name = $findSuggest->category_name;
            $message = $findSuggest->message;

            $company_name = $findSuggest->company_name;
            $company_name_pic = $findSuggest->company_name_pic;
            $name = (!empty($company_name) ? $company_name : $company_name_pic);
            $company_email = $findSuggest->company_email;
            $representative_email = $findSuggest->representative_email;
            $email = (!empty($findDele->id) ? $representative_email : $company_email);
            $this->emailService->sendSuggestMeet($name, $users_name, $category_name, $message, $email);
        }
        if ($save->id) {
            $response['status'] = 200;
            $response['message'] = 'Suggest Meet Successfully';
            $response['payload'] = null;
        } else {
            $response['status'] = 404;
            $response['message'] = 'Oops, something went wrong';
            $response['payload'] = null;
        }
        return response()->json($response, 200);
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

    public function postDownloadTimeline(Request $request)
    {
        $id =  auth('sanctum')->user()->id ?? null;
        $type = $request->type;
        $directory_id = $request->directory_id;
        $company_id = $request->company_id;
        if ($id) {
            self::countDownloadPage($type, $directory_id, $company_id, 11, $id);
            $response['status'] = 200;
            $response['message'] = 'Success Download';
            $response['payload'] = null;
        } else {
            $response['status'] = 401;
            $response['message'] = 'Unauthorized';
            $response['payload'] = null;
        }
        return response()->json($response);
    }

    public function postSendCard(Request $request)
    {
        $id =  auth('sanctum')->user()->id ?? null;
        $company_id = $request->company_id;
        $data  = $this->companyService->postSendCard($company_id, $id);
        self::usersActivity('Send Business Card to Company', (self::isInEvents() ? 'In' : 'Out') . ' Events', self::getIdEvents(), null);
        $response['status'] = 200;
        $response['message'] = 'Success send card';
        $response['payload'] = null;

        return response()->json($response, 200);
    }
}
