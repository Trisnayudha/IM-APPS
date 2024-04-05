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
        $find = $this->userService->getUserById($id);
        if ($find) {
            $file = $request->image;
            if (!empty($file)) {
                $imageName = time() . '.' . $request->image->extension();
                $db = 'storage/profile/' . $imageName;
                $save_folder = $request->image->storeAs('public/profile', $imageName);
                // Create a new Intervention Image instance from the uploaded file
                $compressedImage = Image::make(storage_path('app/' . $save_folder));
                // Resize the image while maintaining aspect ratio and avoiding upscaling
                $compressedImage->resize(800, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
                // Save the resized image
                $compressedImage->save(storage_path('app/public/profile/' . $imageName));
                $find->image_users = $db;
            }
            $find->name = $request->name;
            $find->bio_desc = $request->bio_desc;
            $find->save();

            $response['status'] = 200;
            $response['message'] = 'Successfully update data';
            $response['payload'] = null;
        } else {
            $response['status'] = 401;
            $response['message'] = 'User Not Found';
            $response['payload'] = null;
        }

        return response()->json($response, 200);
    }

    public function uploadCompanyProfile(Request $request)
    {
        $id = auth('sanctum')->user()->id ?? null;
        $find = $this->userService->getUserById($id);
        if ($find) {
            $file = $request->image;
            if (!empty($file)) {
                $imageName = time() . '.' . $request->image->extension();
                $db = 'storage/company-logo/' . $imageName;
                $save_folder = $request->image->storeAs('public/company-logo', $imageName);
                // Create a new Intervention Image instance from the uploaded file
                $compressedImage = Image::make(storage_path('app/' . $save_folder));
                // Resize the image while maintaining aspect ratio and avoiding upscaling
                $compressedImage->resize(800, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
                // Save the resized image
                $compressedImage->save(storage_path('app/public/company-logo/' . $imageName));
                // $find->company_logo = $db;
            }
            $find->save();

            $response['status'] = 200;
            $response['message'] = 'Successfully update data';
            $response['payload'] = $db ?? null;
        } else {
            $response['status'] = 401;
            $response['message'] = 'User Not Found';
            $response['payload'] = null;
        }
        return response()->json($response, 200);
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
}
