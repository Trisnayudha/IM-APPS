<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Services\Email\EmailService;
use App\Services\MsPrefix\MsService;
use App\Services\Profile\ProfileService;
use App\Services\Users\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Intervention\Image\Facades\Image;

class ProfileController extends Controller
{
    protected $userService;
    protected $profileService;
    protected $emailService;
    protected $msService;
    public function __construct(UserService $userService, ProfileService $profileService, EmailService $emailService, MsService $msService)
    {
        $this->userService = $userService;
        $this->profileService = $profileService;
        $this->msService = $msService;
        $this->emailService = $emailService;
    }

    public function getIndex()
    {
        $id =  auth('sanctum')->user()->id ?? null;

        $find = $this->userService->getUserById($id);
        if ($id) {
            $response['status'] = 200;
            $response['message'] = "Success show data users";
            $response['payload'] = $find;
        } else {
            $response['status'] = 401;
            $response['message'] = 'Unauthorized';
            $response['payload'] = null;
        }
        return response()->json($response);
    }

    public function updatePersonal(UpdateProfileRequest $request)
    {
        $id = auth('sanctum')->user()->id ?? null;
        $user = $this->userService->getUserById($id);

        if (!$user) {
            return response()->json([
                'status' => 401,
                'message' => 'User Not Found',
                'payload' => null
            ], 401);
        }

        // ✅ HANDLE IMAGE (OPTIONAL)
        if ($request->hasFile('image')) {

            $file = $request->file('image');

            // Validasi tambahan (opsional tapi disarankan)
            if (!$file->isValid()) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Invalid image file',
                    'payload' => null
                ], 422);
            }

            // Convert image ke base64
            $base64 = 'data:' . $file->getMimeType() . ';base64,' .
                base64_encode(file_get_contents($file->getRealPath()));

            // Upload ke API eksternal
            $upload = Http::timeout(30)->post(
                'https://indonesiaminer.com/api/upload-image/company',
                [
                    'image'  => $base64,
                    'folder' => 'uploads/images/profile'
                ]
            );

            if (!$upload->successful() || !isset($upload['image'])) {
                return response()->json([
                    'status' => 500,
                    'message' => 'Upload image failed',
                    'payload' => $upload->json()
                ], 500);
            }

            // Path image dari API
            $user->image_users = $upload['image'];
        }

        // ✅ UPDATE DATA LAIN
        $user->name = $request->name;
        $user->bio_desc = $request->bio_desc;
        $user->save();

        return response()->json([
            'status' => 200,
            'message' => 'Successfully update data',
            'payload' => null
        ], 200);
    }

    public function uploadCompanyProfile(Request $request)
    {
        $id = auth('sanctum')->user()->id ?? null;
        $user = $this->userService->getUserById($id);

        if (!$user) {
            return response()->json([
                'status' => 401,
                'message' => 'User Not Found',
                'payload' => null
            ], 401);
        }

        if (!$request->hasFile('image')) {
            return response()->json([
                'status' => 422,
                'message' => 'Image is required',
                'payload' => null
            ], 422);
        }

        $file = $request->file('image');

        if (!$file->isValid()) {
            return response()->json([
                'status' => 422,
                'message' => 'Invalid image file',
                'payload' => null
            ], 422);
        }

        // ✅ Convert image ke base64
        $base64 = 'data:' . $file->getMimeType() . ';base64,' .
            base64_encode(file_get_contents($file->getRealPath()));

        // ✅ Upload ke API eksternal
        $upload = Http::timeout(30)->post(
            'https://indonesiaminer.com/api/upload-image/company',
            [
                'image'  => $base64,
                'folder' => 'uploads/images/company-logo',
            ]
        );

        if (!$upload->successful() || !isset($upload['image'])) {
            return response()->json([
                'status' => 500,
                'message' => 'Upload company logo failed',
                'payload' => $upload->json()
            ], 500);
        }

        // ✅ Simpan path logo dari API
        $user->company_logo = $upload['image'];
        $user->save();

        return response()->json([
            'status' => 200,
            'message' => 'Successfully upload company logo',
            'payload' => $upload['image']
        ], 200);
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        $id = auth('sanctum')->user()->id ?? null;
        $old_password = $request->current_password;
        $new_password = $request->new_password;
        $find = $this->userService->getUserById($id);
        if (Hash::check($old_password, $find->password)) {
            //password true
            $find->password = Hash::make($new_password);
            $find->save();
            $response['status'] = 200;
            $response['message'] = 'Successfully Update new password';
            $response['payload'] = null;
        } else {
            $data = [
                'current_password' => 'Password was wrong',
            ];
            $response['status'] = 422;
            $response['message'] = 'Invalid data';
            $response['payload'] = $data;
        }
        return response()->json($response, 200);
    }
    //

    public function checking(Request $request)
    {
        $email = $request->email;
        $phone = $request->phone;

        if (!empty($email)) {
            //
            $find = $this->userService->getUserByEmailActive($email);
            if (empty($find)) {
                $response['status'] = 200;
                $response['message'] = 'Next';
                $response['payload'] = null;
            } else {
                $data = [
                    'email' => 'Email address already used',
                ];
                $response['status'] = 401;
                $response['message'] = 'Something was wrong';
                $response['payload'] = $data;
            }
        } else {
            $find = $this->userService->getUserByPhone($phone);
            if (empty($find)) {
                $response['status'] = 200;
                $response['message'] = 'Next';
                $response['payload'] = null;
            } else {
                $data = [
                    'Phone' => 'Phone number already used',
                ];
                $response['status'] = 401;
                $response['message'] = 'Something was wrong';
                $response['payload'] = $data;
            }
        }
        return response()->json($response, 200);
    }

    public function requestOtp(Request $request)
    {
        $id = auth('sanctum')->user()->id ?? null;
        $email = $request->email;
        $phone = $request->phone;
        $params = $request->params;
        $otp = rand(10000, 99999);
        $find = $this->userService->getUserById($id);
        if ($params == 'change') {
            //request Change
            $subject = 'Change Email From Indonesia Miner';
            $wording = 'We received a request to change your account. To change, please use this
                    code:';
        } else {
            //request Verify
            $subject = 'Verify Email From Indonesia Miner';
            $wording = 'We received a request to verify your account. To verify, please use this
                    code:';
        }
        if (!empty($email)) {
            //Email
            $send = $this->emailService->sendOtpVerify($find, $otp, $wording, $subject, $email);
            $response['status'] = 200;
            $response['message'] = 'Successfully send OTP to Email';
            $response['payload'] = $send;
            $find->otp = $otp;
            $find->save();
            return response()->json($response);
        }
    }

    public function verify(Request $request)
    {
        $id = auth('sanctum')->user()->id ?? null;
        $otp = $request->otp;
        $email = $request->email;
        $phone = $request->phone;
        $code = $request->code;

        $find = $this->userService->getUserById($id);
        if (!empty($email)) {
            //email
            if ($otp == $find->otp) {
                $find->email = $email;
                $find->otp = null;
                $find->save();
                $response['status'] = 200;
                $response['message'] = 'Successfully change Email';
                $response['payload'] = null;
            } else {
                $response['status'] = 401;
                $response['message'] = 'OTP Not Match';
                $response['payload'] = null;
            }
        } else {

            $check_id = $this->msService->getMsPrefixPhoneDetail($code);
            $find->ms_prefix_call_id = $check_id->ms_country_id;
            $find->phone = $phone;
            $find->is_verification = 1;
            $find->save();
            $response['status'] = 200;
            $response['message'] = 'Successfully change mobile number';
            $response['payload'] = null;
        }
        return response()->json($response);
    }

    public function deleteAccount()
    {
        $id = auth('sanctum')->user()->id ?? null;
        $this->userService->deleteAccount($id);
        $response['status'] = 200;
        $response['message'] = 'Successfully Delete Account';
        $response['payload'] = null;

        return response()->json($response);
    }

    public function updateCompany(Request $request)
    {
        $id =  auth('sanctum')->user()->id ?? null;
        // dd($id);
        $code_phone = $request->code_phone;
        $phone = $request->phone;
        $country = $request->country;
        $state = $request->state;
        $city = $request->city;
        $email_alternate = $request->email_alternate;
        $job_title = $request->job_title;
        $company_web = $request->company_web;
        $company_name = $request->company_name;
        $category_id = $request->category_id;
        $category_name = $request->category_name;
        $ms_company_project_type_id = $request->ms_company_project_type_id;
        $classify_minerals_name = $request->classify_minerals_name;
        $classify_mining_name = $request->classify_mining_name;
        $commodities_minerals_name = $request->commodities_minerals_name;
        $commodities_minerals_coal_name = $request->commodities_minerals_coal_name;
        $commodities_mining_name = $request->commodities_mining_name;
        $origin_manufacturer_name = $request->origin_manufacturer_name;
        $msPhonePrefix = $this->msService->getMsPrefixPhoneDetail($code_phone);
        $msPhoneId = $msPhonePrefix->id ?? 102;
        $company_logo = $request->company_logo ?? null;
        if ($id) {
            $save = $this->userService->getUserById($id);
            if ($save) {
                $save->job_title = $job_title;
                $save->company_name = $company_name;
                $save->ms_prefix_call_id = $msPhoneId;
                $save->phone = $phone;
                $save->email_alternate = $email_alternate;
                $save->country = strtoupper($country);
                $save->state = strtoupper($state);
                $save->city = strtoupper($city);
                $save->company_web = $company_web;
                $save->is_register = 1;
                $save->ms_company_category_id = $category_id;
                $save->ms_company_category_other = $category_name;
                $save->class_company_minerals_other = $classify_minerals_name;
                $save->class_company_mining_other = $classify_mining_name;
                $save->commod_company_minerals_other = $commodities_minerals_name;
                $save->commod_company_minerals_coal_other = $commodities_minerals_coal_name;
                $save->commod_company_mining_other = $commodities_mining_name;
                $save->origin_manufactur_company_other = $origin_manufacturer_name;
                $save->ms_company_project_type_id = $ms_company_project_type_id;
                $save->company_logo = $company_logo;
                $save->save();
                $response['status'] = 200;
                $response['message'] = 'Update Company Success';
                $response['payload'] = $save;
            } else {
                $response['status'] = 401;
                $response['message'] = 'You cant complete this account, because this account has been active';
                $response['payload'] = null;
            }
        } else {
            $response['status'] = 401;
            $response['message'] = 'Unauthorized';
            $response['payload'] = null;
        }
        return response()->json($response);
    }

    public function faq()
    {
        $data = $this->profileService->getFaq();
        $response['status'] = 200;
        $response['message'] = 'Get data Faq';
        $response['payload'] = $data;
        return response()->json($response);
    }

    public function contactUs(Request $request)
    {
        $category = $request->category;
        $subject = $request->subject;
        $message = $request->message;
        $id =  auth('sanctum')->user()->id ?? null;
        $user = $this->userService->getUserById($id);
        $send = $this->emailService->sendContactUs($user, $category, $subject, $message);
        $response['status'] = 200;
        $response['message'] = 'Send Contact success';
        $response['payload'] = null;
        return response()->json($response);
    }

    public function progress(Request $request)
    {
        $id = auth('sanctum')->user()->id ?? null;
        $user = $this->userService->getUserById($id);

        $fields = [
            'image_users',
            'bio_desc',
            'name',
            'job_title',
            'country',
            'state',
            'city',
            'email_alternate',
            'company_name',
            'company_web',
            'ms_company_category_id',
            'company_logo'
        ];

        $filled = 0;

        foreach ($fields as $field) {
            if (!empty($user->$field)) {
                $filled++;
            }
        }

        $total = count($fields);
        $progress = round(($filled / $total) * 100); // misal 8 dari 11 = 72%

        $response['status'] = 200;
        $response['message'] = 'Get Data Progress';
        $response['payload'] = $progress;
        return response()->json($response);
    }
}
