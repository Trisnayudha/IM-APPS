<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\DirectoryController;
use App\Http\Controllers\API\HomeController;
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

//API route MsService
Route::post('/msphone', [MsPrefixController::class, 'showMsPrefixPhoneAll']);
Route::post('/msphone/detail', [MsPrefixController::class, 'showMsPrefixPhoneDetail']);


//API route Home
Route::post('/home/banner', [HomeController::class, 'banner']);
Route::post('/home/sponsors', [HomeController::class, 'sponsors']);

//API get detail sponsors
Route::post('/sponsors/free/detail', [HomeController::class, 'detail_free']);
Route::post('/sponsors/premium/detail', [HomeController::class, 'detail_premium']);


//API route Directory

Route::post('/contact/detail', [DirectoryController::class, 'getContactDetail']);

Route::post('/timeline/directory', [DirectoryController::class, 'listTimeline']);
Route::post('detail/{slug}/product', [DirectoryController::class, 'detailProduct']);
Route::post('detail/{slug}/news', [DirectoryController::class, 'detailNews']);
Route::post('detail/{slug}/project', [DirectoryController::class, 'detailProject']);
Route::post('detail/{slug}/media', [DirectoryController::class, 'detailMedia']);

Route::post('/timeline/bookmark', [DirectoryController::class, 'postBookmarkTimeline']);
Route::post('/timeline/download', [DirectoryController::class, 'postDownloadTimeline']);
