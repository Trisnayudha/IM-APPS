<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Services\Email\EmailService;
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
    public function __construct(UserService $userService, ProfileService $profileService, EmailService $emailService)
    {
        $this->userService = $userService;
        $this->profileService = $profileService;
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
            $find->phone = $phone;
            $find->is_verification = 1;
            $find->save();
            $response['status'] = 200;
            $response['message'] = 'Successfully change mobile number';
            $response['payload'] = null;
        }
        return response()->json($response);
    }
}
