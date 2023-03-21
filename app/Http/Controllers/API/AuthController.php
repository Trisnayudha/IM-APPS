<?php

namespace App\Http\Controllers\API;

use App\Helpers\EmailSender;
use App\Http\Controllers\Controller;
use App\Http\Requests\CompleteRegister;
use App\Http\Requests\LoginOtpRequest;
use App\Http\Requests\RegisterOtpRequest;
use App\Http\Requests\VerifyRegisterOtpRequest;
use App\Models\Auth\User;
use App\Repositories\EmailServiceInterface;
use App\Repositories\UserRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    protected $userRepository;
    protected $emailService;

    public function __construct(UserRepositoryInterface $userRepository, EmailServiceInterface $emailService)
    {
        $this->userRepository = $userRepository;
        $this->emailService = $emailService;
    }

    public function registerOtp(RegisterOtpRequest $request)
    {
        $email = $request->email;
        $user = $this->userRepository->getUserByEmailActive($email);
        if ($user) {
            // Langsung masuk ke return karena email dan is_register nya 1
            $response['status'] = 409;
            $response['message'] = 'Email already register.';
            $response['payload'] = null;
            return response()->json($response);
        } else {
            // Cek jika email dan is_register nya 0
            $user = $this->userRepository->getUserByEmailDeactive($email);
            if (!$user) {
                // Masuk ke else karena email tidak ketemu
                $user = $this->userRepository->createUsers();
            }
        }

        $otp = rand(10000, 99999);
        $user->otp = $otp;
        $user->is_register = 0;
        $user->email = $email;
        $user->save();
        $send = $this->emailService->sendOtpRegisterEmail($user, $otp);

        $response['status'] = 200;
        $response['message'] = 'Successfully send OTP to Email';
        $response['payload'] = $user;

        return response()->json($response);
    }

    public function loginOtp(LoginOtpRequest $request)
    {
        $credentials = $request->only('email');
        $user = $this->userRepository->getUserByEmailActive($credentials['email']);

        if ($user) {
            $otp = rand(10000, 99999);
            $user->otp = $otp;
            $user->save();

            $send = $this->emailService->sendOtpEmail($user, $otp);

            $response['status'] = 200;
            $response['message'] = 'Successfully send OTP to Email';
            $response['payload'] = $user;
            return response()->json($response);
        } else {
            $response['status'] = 404;
            $response['message'] = 'Email not Found.';
            $response['payload'] = null;
            return response()->json($response);
        }
    }

    public function verifyRegisterOtp(VerifyRegisterOtpRequest $request)
    {
        $email = $request->email;
        $otp = $request->otp;
        $user = $this->userRepository->getUserByEmailDeactive($email);

        if ($user->otp == $otp) {
            $user->otp = null;
            $user->save();
            $response['status'] = 200;
            $response['message'] = 'OTP verification successful';
            $response['payload'] = null;
        } else {
            $response['status'] = 401;
            $response['message'] = 'Invalid OTP';
            $response['payload'] = null;
        }
        return response()->json($response);
    }

    public function verifyLoginOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|integer',
        ]);

        $credentials = $request->only('email', 'otp');
        $user = $this->userRepository->getUserByEmail($credentials['email']);

        if (!$user) {
            $response['status'] = 404;
            $response['message'] = 'User not found';
            $response['payload'] = null;
        } elseif ($user->otp == $credentials['otp']) {
            // OTP match, login successful
            $user->otp = null;
            $user->save();
            $data = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'token' => $user->createToken('token-name')->plainTextToken,
            ];
            $response['status'] = 200;
            $response['message'] = 'OTP verification successful';
            $response['payload'] = $data;
        } else {
            $response['status'] = 401;
            $response['message'] = 'Invalid OTP';
            $response['payload'] = null;
        }

        return response()->json($response, $response['status']);
    }

    public function registerCompleteV1(CompleteRegister $request)
    {
        $email = $request->email;
        $name = $request->name;
        $password = $request->password;

        $user = $this->userRepository->getUserByEmailDeactive($email);
        if ($user) {
            //true
            $user->name = $name;
            $user->password = Hash::make($password);
            $user->is_register = 1;
            $user->save();
            $data = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'token' => $user->createToken('token-name')->plainTextToken,
            ];
            $response['status'] = 200;
            $response['message'] = 'OTP verification successful';
            $response['payload'] = $data;
        } else {
            $response['status'] = 401;
            $response['message'] = 'You cant complete this account, because this account has been active';
            $response['payload'] = null;
        }
        return response()->json($response);
    }

    public function registerCompleteV2(Request $request)
    {
        $id =  auth('sanctum')->user()->id ?? null;
        $phone = $request->phone;
        $country = $request->country;
        $state = $request->state;
        $city = $request->city;
        $email_alternate = $request->email_alternate;
        $job_title = $request->job_title;
        $company_web = $request->company_web;
        $company_name = $request->company_name;
        $category_name = $request->category_name;
        $classify_minerals_name = $request->classify_minerals_name;
        $classify_mining_name = $request->classify_mining_name;
        $commodities_minerals_name = $request->commodities_minerals_name;
        $commodities_minerals_coal_name = $request->commodities_minerals_coal_name;
        $commodities_mining_name = $request->commodities_mining_name;
        $origin_manufacturer_name = $request->origin_manufacturer_name;
        if ($id) {
            $save = $this->userRepository->getUserById($id);
            if ($save) {
                $save->job_title = $job_title;
                $save->company_name = $company_name;
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
                $save->save();
                $response['status'] = 200;
                $response['message'] = 'Complete register account';
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
    public function resendVerifyLoginOtp(Request $request)
    {
        $credentials = $request->only('email');
        $validator = Validator::make(
            $credentials,
            [
                'email' => [
                    'required', 'email',
                    'exists:users,email'
                ]
            ],
            [
                'email.exists' => 'Email not found'
            ]
        );

        if ($validator->fails()) {
            $response['status'] = 404;
            $response['message'] = $validator->errors()->first();
            $response['payload'] = null;
            return response()->json($response);
        }
        $user = $this->userRepository->getUserByEmail($credentials['email']);
        $otp = rand(10000, 99999);
        $user->otp = $otp;
        $user->save();
        //send email;
        $send = new EmailSender();
        $send->subject = "OTP Login Indonesia Miner";
        $wording = 'We received a request to login your account. To login, please use this
                    code:';
        $send->template = "email.tokenverify";
        $send->data = [
            'name' => $user->name,
            'wording' => $wording,
            'otp' => $otp
        ];
        $send->from = env('EMAIL_SENDER');
        $send->name_sender = env('EMAIL_NAME');
        $send->to = $user->email;
        $send->sendEmail();

        $response['status'] = 200;
        $response['message'] = 'Successfully send OTP to Email';
        $response['payload'] = $send;
        return response()->json($response);
    }


    public function loginPassword(Request $request)
    {
        $validate = Validator::make(
            $request->all(),
            [
                'email' => ['required', 'email', 'exists:users,email'],
            ],
            [
                'email.required' => 'Email wajib diisi',
                'email.exists' => 'Email Not Found'
            ]
        );
        $user = $this->userRepository->getUserByEmail($request->email);
        if ($validate->fails()) {
            $data = [
                'email' => $validate->errors()->first('email')
            ];
            $response['status'] = 422;
            $response['message'] = 'Invalid data';
            $response['payload'] = $data;
        } else if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();
            $data = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'token' => $user->createToken('token-name')->plainTextToken,
            ];
            $response['status'] = 200;
            $response['message'] = 'Successfully Login';
            $response['payload'] = $data;
        } else {
            $data = [
                'password' => 'Password was wrong'
            ];
            $response['status'] = 422;
            $response['message'] = 'Invalid data';
            $response['payload'] = $data;
        }
        return response()->json($response);
    }

    // method for user logout and delete token
    public function logout()
    {
        auth()->user()->tokens()->delete();

        $response['status'] = 200;
        $response['message'] = 'Successfully Logout';
        $response['payload'] = null;
        return response()->json($response);
    }
}
