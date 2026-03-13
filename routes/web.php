<?php

use App\Http\Controllers\GamePlayController;
use App\Http\Controllers\HomeController;
use App\Models\User;
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

Route::get('/', [HomeController::class, 'index'])->name('index');
Route::get('thank-you/{user_name}', [HomeController::class, 'thankYouForYourParticipation'])->name('thank-you-for-your-participation');

Route::get('agb', [HomeController::class, 'agreementTerms'])->name('agb');
// Teilnahmebedingungen
Route::get('teilnahmebedingungen', [HomeController::class, 'participationTerms'])->name('teilnahmebedingungen');
//Datenschutz
Route::get('datenschutz', [HomeController::class, 'privacyPolicy'])->name('datenschutz');

Route::get('{name}', [HomeController::class, 'registerUserViaForm'])->name('register-user-via-form');
Route::get('/live-show-magic-link/{name}', [HomeController::class, 'liveShowMagicLink'])->name('live-show-magic-link');

// Route::middleware(['auth:admin', 'role:admin'])->group(function () {
//     Route::get("dashboard", [HomeController::class, 'dashboard_redirect'])->name('dashboard');
// });

Route::get('live-show-play/{id}', [GamePlayController::class, 'liveShow'])->name('live-show');
Route::post('live-show/{id}/user/register', [GamePlayController::class, 'registerUser'])->name('live-show.user.register');
Route::get('live-show/{id}/messages', [GamePlayController::class, 'fetchMessages'])->name('live-show.fetch-messages');

Route::middleware(['auth:web'])->group(function () {
    Route::post('live-show/{id}/user/updateOnlineStatus', [GamePlayController::class, 'updateOnlineStatus'])->name('live-show.user.updateOnlineStatus');

    Route::post('live-show/{id}/send-message', [GamePlayController::class, 'postMessage'])->name('live-show.post-message');
    Route::post('live-show/{id}/heart-reaction', [GamePlayController::class, 'heartReaction'])->name('live-show.heart-reaction');

    // report user message
    Route::post('live-show/{id}/report-message', [GamePlayController::class, 'reportUserMessage'])->name('live-show.report-message');

    Route::post('liveshow/{id}/logout', [GamePlayController::class, 'liveShowLogout'])->name('livestream.logout');

    // update-elimination-status
    Route::post('live-show/{id}/update-elimination-status', [GamePlayController::class, 'updateEliminationStatus'])->name('live-show.update-elimination-status');

    Route::post('live-show/{id}/submit-quiz', [GamePlayController::class, 'submitQuiz'])->name('live-show.submit-quiz');
});
Route::get('live-show/{id}/get-live-show-users-with-scores', [GamePlayController::class, 'getLiveShowUsersWithScores']);
Route::get('show-live-broadcast/{id}', [GamePlayController::class, 'showLiveBroadcast'])->name('show-live-broadcast');

Route::get('/test-message-event', function () {
    event(new \App\Events\LiveShowMessageEvent([
        'live_show_id' => 1,
        'user_id' => 123,
        'message' => 'Hello, this is a test message!',
    ]));

    return 'Event has been sent!';
});
Route::get('/live-show/get-my-points/{liveShowId}', [GamePlayController::class, 'getLiveShowUserPoints'])->name('api.get-my-points');
Route::get('/live-show/get-my-referral-link', [GamePlayController::class, 'getMyReferralLink'])->name('api.get-my-referral-link')->middleware('auth:web');
Route::get('/live-show/{id}/user-prize', [GamePlayController::class, 'getUserPrize'])->name('api.get-user-prize');
Route::get('/live-show/{id}/check-if-user-blocked-from-live-show', [GamePlayController::class, 'checkIfUserBlockedFromLiveShow'])->name('api.check-if-user-blocked-from-live-show');

Route::post('register-user-via-form-submit', [HomeController::class, 'registerUserViaFormSubmit'])->name('register-user-via-form-submit');



Route::get('/test/registration-welcome-email', function () {
    $user = User::where('user_name', 'mutahar1996')->first();

    return view('emails.registration_welcome', compact('user'));
});

require __DIR__.'/admin.php';
require __DIR__.'/auth.php';
