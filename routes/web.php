<?php

use App\Http\Controllers\Admin\HomeController as AdminHomeController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\GamePlayController;
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

Route::get('/', [HomeController::class, 'dashboard_redirect']);
Route::get("dashboard", [HomeController::class, 'dashboard_redirect'])->name('dashboard');

Route::get("live-show-play/{id}", [GamePlayController::class, 'liveShow'])->name('live-show');

Route::post("live-show/{id}/user/updateOnlineStatus", [GamePlayController::class, 'updateOnlineStatus'])->name('live-show.user.updateOnlineStatus');
Route::post('live-show/{id}/send-message', [GamePlayController::class, 'postMessage'])->name('live-show.post-message');
Route::get('live-show/{id}/messages', [GamePlayController::class, 'fetchMessages'])->name('live-show.fetch-messages');
//report user message 
Route::post('live-show/{id}/report-message', [GamePlayController::class, 'reportUserMessage'])->name('live-show.report-message');
//register user ajax call
Route::post("live-show/{id}/user/register", [GamePlayController::class, 'registerUser'])->name('live-show.user.register');
//livestream.logout
Route::post('liveshow/{id}/logout', [GamePlayController::class, 'liveShowLogout'])->name('livestream.logout');

//submit-quiz
Route::post('live-show/{id}/submit-quiz', [GamePlayController::class, 'submitQuiz'])->name('live-show.submit-quiz');

require __DIR__ . '/admin.php';
require __DIR__ . '/auth.php';
