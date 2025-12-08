<?php

use App\Http\Controllers\Admin\HomeController as AdminHomeController;
use App\Http\Controllers\Admin\LiveShowController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;



Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminHomeController::class, 'home'])->name('dashboard');
    Route::resource('users', AdminUserController::class);

    Route::resource('live-shows', \App\Http\Controllers\Admin\LiveShowController::class);
    Route::resource('live-show-quizzes', \App\Http\Controllers\Admin\LiveShowQuizController::class);

    Route::get("live-shows/stream-management/{id}", [\App\Http\Controllers\Admin\LiveShowController::class, 'streamManagement'])->name('live-shows.stream-management');
    Route::post("live-shows/stream-management/{id}/quizzes/{quizId}/send-quiz-question", [\App\Http\Controllers\Admin\LiveShowController::class, 'sendQuizQuestion'])->name('live-shows.stream-management.send-quiz-question');

    Route::post("live-shows/stream-management/{id}/quizzes/{quizId}/remove-quiz-question", [\App\Http\Controllers\Admin\LiveShowController::class, 'removeQuizQuestion'])->name('live-shows.stream-management.remove-quiz-question');

    Route::get("live-shows/stream-management/{id}/fetch-messages", [\App\Http\Controllers\Admin\LiveShowController::class, 'fetchChatMessages'])->name('live-shows.stream-management.fetch-chat-messages');
    Route::post("live-shows/stream-management/{id}/send-message", [\App\Http\Controllers\Admin\LiveShowController::class, 'sendMessage'])->name('live-shows.stream-management.send-message');

    //updateWinners
    Route::post("live-shows/stream-management/{liveShowId}/update-winners", [\App\Http\Controllers\Admin\LiveShowController::class, 'updateWinners'])->name('live-shows.update-winners');


    Route::post("live-shows/{id}/block-user/{userId}", [\App\Http\Controllers\Admin\LiveShowController::class, 'blockUser'])->name('live-shows.block-user');
    Route::post("live-shows/{id}/unblock-user/{userId}", [\App\Http\Controllers\Admin\LiveShowController::class, 'unblockUser'])->name('live-shows.unblock-user');

    Route::get("players", [\App\Http\Controllers\Admin\PlayerController::class, 'index'])->name('players.index');
    Route::get("players/{id}", [\App\Http\Controllers\Admin\PlayerController::class, 'show'])->name('players.show');

    Route::get("live-shows/{id}/get-users-quiz-responses/{quiz_id}", [\App\Http\Controllers\Admin\LiveShowController::class, 'getUsersQuizResponses'])->name('live-shows.get-users-quiz-responses');


    Route::post("live-shows/{id}/update-live-show", [\App\Http\Controllers\Admin\LiveShowController::class, 'updateLiveShow'])->name('live-shows.update-live-show');

    Route::post('live-show/{id}/admin/reset-game', [LiveShowController::class, 'resetGame'])->name('live-shows.reset-game');
})->middleware(['auth']);
