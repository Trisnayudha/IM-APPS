<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfileRequest;
use App\Services\Profile\ProfileService;
use App\Services\Users\UserService;
use Illuminate\Http\Request;
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
    //
}
