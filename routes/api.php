<?php

use App\Http\Controllers\API\AdsController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\BookmarkController;
use App\Http\Controllers\API\DirectoryController;
use App\Http\Controllers\API\EventsController;
use App\Http\Controllers\API\EventsPollingController;
use App\Http\Controllers\API\EventsQuestionController;
use App\Http\Controllers\API\ExhibitionController;
use App\Http\Controllers\API\HomeController;
use App\Http\Controllers\API\InboxController;
use App\Http\Controllers\API\InsightController;
use App\Http\Controllers\API\MiningDirectoryController;
use App\Http\Controllers\API\MsPrefixController;
use App\Http\Controllers\API\NetworkingController;
use App\Http\Controllers\API\NotificationController;
use App\Http\Controllers\API\ProfileController;
use App\Http\Controllers\API\ScheduleController;
use App\Http\Controllers\API\SpeakerController;
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

// routes/api.php
Route::post('/capture', [App\Http\Controllers\PhotoController::class, 'capture'])
    ->name('camera.capture');
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

//API route AdsService
Route::post('/ads-screen', [AdsController::class, 'screen']);
Route::post('/ads-banner', [AdsController::class, 'banner']);

//API route Insight
Route::post('/insight', [InsightController::class, 'insight']);

//API route MsService
Route::post('/msphone', [MsPrefixController::class, 'showMsPrefixPhoneAll']);
Route::post('/msphone/detail', [MsPrefixController::class, 'showMsPrefixPhoneDetail']);
Route::post('/mdCategorySuggest', [MsPrefixController::class, 'showMdCategorySuggest']);
Route::post('/msCompanyCategory', [MsPrefixController::class, 'showMsCompanyCategory']);


//API route Home
Route::post('/home/banner', [HomeController::class, 'banner']);
Route::post('/home/sponsors', [HomeController::class, 'sponsors']);
Route::post('/home/benefit', [HomeController::class, 'benefit']);
Route::post('/home/benefit/request', [HomeController::class, 'sendRequest']);
Route::post('/home/check/event', [HomeController::class, 'checkEvent']);
Route::post('/home/scan', [HomeController::class, 'scan']);
Route::post('/home/eventBooklet', [HomeController::class, 'eventBooklet']);

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
Route::post('/timeline/sendMeet', [DirectoryController::class, 'postSendMeet']);
Route::post('/timeline/sendCard', [DirectoryController::class, 'postSendCard']);


//Profile
Route::post('/profile', [ProfileController::class, 'getIndex']);
Route::post('/profile/checking', [ProfileController::class, 'checking']);
Route::post('/profile/updatePersonal', [ProfileController::class, 'updatePersonal']);
Route::post('/profile/updatePassword', [ProfileController::class, 'changePassword']);
Route::post('/profile/verify/change', [ProfileController::class, 'requestOtp']);
Route::post('/profile/verify/change/v2', [ProfileController::class, 'verify']);
Route::post('/profile/deleteAccount', [ProfileController::class, 'deleteAccount']);
Route::post('/profile/updateCompany', [ProfileController::class, 'updateCompany']);
Route::post('/profile/faq', [ProfileController::class, 'faq']);
Route::post('/profile/contactus', [ProfileController::class, 'contactUs']);
Route::post('/profile/uploadCompanyLogo', [ProfileController::class, 'uploadCompanyProfile']);
Route::post('/profile/progress', [ProfileController::class, 'progress']);


//Networking
Route::post('/networking', [NetworkingController::class, 'index']);
Route::post('/networking/detail', [NetworkingController::class, 'detail']);
Route::post('/networking/getRoom', [NetworkingController::class, 'createRoom']);

Route::post('/inbox', [InboxController::class, 'index']);
// Route::post('/inbox/v2', [InboxController::class, 'index2']);
// Route::post('/inbox/v3', [InboxController::class, 'test']);
Route::post('/inbox/detail', [InboxController::class, 'showChatRoom']);
Route::post('/inbox/send', [InboxController::class, 'sendChat']);


//Exhibition
Route::post('/exhibition', [ExhibitionController::class, 'index']);
Route::post('/exhibition/v2', [ExhibitionController::class, 'indexV2']);

Route::post('/mining/directory', [MiningDirectoryController::class, 'index']);

//Schedule
Route::post('schedule', [ScheduleController::class, 'showList']);
Route::post('schedule/reserve', [ScheduleController::class, 'reserve']);

//Bookmark
Route::post('bookmark', [BookmarkController::class, 'index']);
Route::post('bookmark/all', [BookmarkController::class, 'listAll']);

//Events Replay
Route::post('events/replay', [EventsController::class, 'index']);
Route::post('events/replay/all', [EventsController::class, 'listAll']);
Route::post('/events/detail', [EventsController::class, 'detail']);

//Speakers
Route::post('speakers', [SpeakerController::class, 'index']);
Route::post('speakers/detail', [SpeakerController::class, 'detail']);

//Notification
Route::post('/notification', [NotificationController::class, 'index']);

//Scan

// Group Question
Route::post('scan/question', [EventsQuestionController::class, 'index']);
Route::post('scan/post/question', [EventsQuestionController::class, 'store']);

//Group Polling
Route::post('scan/polling', [EventsPollingController::class, 'index']);
Route::post('scan/post/polling', [EventsPollingController::class, 'store']);

//Scan Login
Route::post('scan/login/v1', [AuthController::class, 'loginQrCode']);
