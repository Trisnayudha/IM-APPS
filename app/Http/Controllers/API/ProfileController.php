<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Services\Profile\ProfileService;
use App\Services\Users\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Intervention\Image\Facades\Image;

class ProfileController extends Controller
{
    protected $userService;
    protected $profileService;

    public function __construct(UserService $userService, ProfileService $profileService)
    {
        $this->userService = $userService;
        $this->profileService = $profileService;
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
                $db = '/storage/profile/' . $imageName;
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
}
