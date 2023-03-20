<?php

namespace App\Http\Controllers\API;

use App\Helpers\EmailSender;
use App\Http\Controllers\Controller;
use App\Models\Auth\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()
            ->json(['data' => $user, 'access_token' => $token, 'token_type' => 'Bearer',]);
    }

    public function loginOtp(Request $request)
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

        $user = User::where('email', $credentials['email'])->first();

        if (!$user->otp) {
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
        $response['status'] = 422;
        $response['message'] = 'OTP has already been sent to your email.';
        $response['payload'] = null;
        return response()->json($response);
    }

    public function VerifyLoginOtp(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'email' => [
                    'required', 'email',
                    'exists:users,email'
                ],
                'otp' => [
                    'required'
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

        $email = $request->email;
        $otp = $request->otp;
        $user = User::where('email', $email)->first();
        if ($user->otp) {
            $checkOtp = User::where('email', $email)->where('otp', $otp)->first();
            if (empty($checkOtp)) {
                $response['status'] = 422;
                $response['message'] = 'OTP not match';
                $response['payload'] = null;
            } else {
                $data = [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'token' => $user->createToken('token-name')->plainTextToken,
                ];
                $response['status'] = 200;
                $response['message'] = 'Successfully Login';
                $response['payload'] = $data;
            }
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
        $user = User::where('email', $credentials['email'])->first();
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
