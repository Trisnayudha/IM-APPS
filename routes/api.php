<?php

use App\Http\Controllers\API\AuthController;
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
//API route for login user otp
Route::post('/login/v1', [AuthController::class, 'loginOtp']);
Route::post('/verify/login/otp', [AuthController::class, 'verifyLoginOtp']);
Route::post('/resend/verify/login/otp', [AuthController::class, 'resendVerifyLoginOtp']);

//API route for login user password
Route::post('/login/v2', [AuthController::class, 'loginPassword']);

Route::post('/logout', [AuthController::class, 'logout']);


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
