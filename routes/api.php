<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\MsPrefixController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
//API route for register new user
Route::post('register/otp', [AuthController::class, 'registerOtp']);
Route::post('/verify/register/otp', [AuthController::class, 'verifyRegisterOtp']);
Route::post('/register/complete/v1', [AuthController::class, 'registerCompleteV1']);
Route::post('/register/complete/v2', [AuthController::class, 'registerCompleteV2']);
//API route for login user otp
Route::post('/login/v1', [AuthController::class, 'loginOtp']);
Route::post('/verify/login/otp', [AuthController::class, 'verifyLoginOtp']);
Route::post('/resend/verify/login/otp', [AuthController::class, 'resendVerifyLoginOtp']);

//API route for login user password
Route::post('/login/v2', [AuthController::class, 'loginPassword']);

//API route for logout
Route::post('/logout', [AuthController::class, 'logout']);

//API route for forgot password
Route::post('/forgot', [AuthController::class, 'forgot']);
Route::post('/verify/forgot/otp', [AuthController::class, 'verifyForgotPassword']);
Route::post('/reset/password', [AuthController::class, 'resetPassword']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/msphone', [MsPrefixController::class, 'showMsPrefixPhoneAll']);
Route::post('/msphone/detail', [MsPrefixController::class, 'showMsPrefixPhoneDetail']);
