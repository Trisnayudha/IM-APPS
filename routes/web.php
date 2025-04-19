<?php

use App\Http\Controllers\TestController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/term', function () {
    return view('term');
});
Route::get('/privacy', function () {
    return view('privacy');
});

// routes/web.php
Route::get('/camera', [App\Http\Controllers\PhotoController::class, 'showCamera'])->name('camera.show');

// Route::get('users', [TestController::class, 'index']);
