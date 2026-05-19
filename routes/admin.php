<?php

use App\Http\Controllers\Admin\AdminProfileController;
use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\AppSettingsController;
use App\Http\Controllers\Admin\HomeController as AdminHomeController;
use App\Http\Controllers\Admin\LiveShowController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\PushNotificationController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminHomeController::class, 'home'])->name('dashboard');

    Route::get('/settings', [AppSettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [AppSettingsController::class, 'update'])->name('settings.update');

    Route::get('/gtm', [\App\Http\Controllers\Admin\GtmController::class, 'index'])->name('gtm.index');
    Route::post('/gtm', [\App\Http\Controllers\Admin\GtmController::class, 'update'])->name('gtm.update');
    Route::resource('users', AdminUserController::class);

    Route::resource('roles', RoleController::class)->except(['show']);
    Route::resource('permissions', PermissionController::class)->only(['index', 'create', 'store', 'destroy']);

    Route::resource('live-shows', \App\Http\Controllers\Admin\LiveShowController::class);
    Route::get('live-shows/{live_show}/gallery-attach', [\App\Http\Controllers\Admin\MediaGalleryController::class, 'liveShowsAttachPage'])->name('live-shows.gallery-attach');
    Route::get('live-shows/{live_show}/copy', [\App\Http\Controllers\Admin\LiveShowController::class, 'copyLiveShow'])->name('live-shows.copy');

    Route::post('live-shows/{live_show}/media-hidden', [\App\Http\Controllers\Admin\LiveShowController::class, 'mediaHidden'])->name('live-shows.media-hidden');
    Route::post('live-shows/{live_show}/media-played', [\App\Http\Controllers\Admin\LiveShowController::class, 'mediaPlayed'])->name('live-shows.media-played');

    Route::post('live-shows/{live_show}/inject-media-stream', [\App\Http\Controllers\Admin\LiveShowController::class, 'injectMediaStream'])->name('live-shows.inject-media-stream');

    Route::resource('live-show-quizzes', \App\Http\Controllers\Admin\LiveShowQuizController::class);

    Route::get('media-gallery', [\App\Http\Controllers\Admin\MediaGalleryController::class, 'index'])->name('media-gallery.index');
    Route::get('media-gallery/all', [\App\Http\Controllers\Admin\MediaGalleryController::class, 'allMedia'])->name('media-gallery.all');
    Route::get('media-gallery/create', [\App\Http\Controllers\Admin\MediaGalleryController::class, 'create'])->name('media-gallery.create');
    Route::post('media-gallery/upload', [\App\Http\Controllers\Admin\MediaGalleryController::class, 'upload'])->name('media-gallery.upload');
    Route::get('media-gallery/{media_gallery}/edit', [\App\Http\Controllers\Admin\MediaGalleryController::class, 'edit'])->name('media-gallery.edit');
    Route::put('media-gallery/{media_gallery}', [\App\Http\Controllers\Admin\MediaGalleryController::class, 'update'])->name('media-gallery.update');
    Route::delete('media-gallery/{media_gallery}', [\App\Http\Controllers\Admin\MediaGalleryController::class, 'destroy'])->name('media-gallery.destroy');
    Route::get('media-gallery/{media_gallery}/attach-show', [\App\Http\Controllers\Admin\MediaGalleryController::class, 'attachShow'])->name('media-gallery.attach-show');
    Route::post('media-gallery/attach-to-live-show', [\App\Http\Controllers\Admin\MediaGalleryController::class, 'attachToLiveShow'])->name('media-gallery.attach-to-live-show');
    Route::post('media-gallery/detach-from-live-show', [\App\Http\Controllers\Admin\MediaGalleryController::class, 'detachFromLiveShow'])->name('media-gallery.detach-from-live-show');
    Route::post('media-gallery/reorder', [\App\Http\Controllers\Admin\MediaGalleryController::class, 'reorder'])->name('media-gallery.reorder');

    Route::get('media-gallery/items/{id}', [\App\Http\Controllers\Admin\MediaGalleryController::class, 'items'])->name('media-gallery.items');

    Route::get('live-shows/{live_show}/gallery-stream-state', [\App\Http\Controllers\Admin\MediaGalleryController::class, 'galleryStreamState'])->name('live-shows.gallery-stream-state');

    Route::post('live-shows/{live_show}/gallery-stream/show', [\App\Http\Controllers\Admin\MediaGalleryController::class, 'galleryStreamShow'])->name('live-shows.gallery-stream.show');
    Route::patch('live-shows/{live_show}/gallery-stream', [\App\Http\Controllers\Admin\MediaGalleryController::class, 'galleryStreamUpdate'])->name('live-shows.gallery-stream.update');
    Route::post('live-shows/{live_show}/gallery-stream/visibility', [\App\Http\Controllers\Admin\MediaGalleryController::class, 'galleryStreamVisibility'])->name('live-shows.gallery-stream.visibility');

    Route::get('live-shows/stream-management/{id}', [\App\Http\Controllers\Admin\LiveShowController::class, 'streamManagement'])->name('live-shows.stream-management');
    Route::get('live-shows/stream-broadcaster/{id}', [\App\Http\Controllers\Admin\LiveShowController::class, 'streamBroadcaster'])->name('live-shows.stream-management.broadcaster');

    // Single-broadcaster lock: the broadcaster page claims ownership of the
    // live show on load, kicking out any previously open broadcaster tab.
    Route::post('live-shows/stream-broadcaster/{id}/claim-tab', [\App\Http\Controllers\Admin\LiveShowController::class, 'claimBroadcasterTab'])->name('live-shows.stream-management.broadcaster.claim-tab');
    Route::get('live-shows/stream-broadcaster/{id}/active-tab', [\App\Http\Controllers\Admin\LiveShowController::class, 'getBroadcasterTab'])->name('live-shows.stream-management.broadcaster.active-tab');

    Route::get('live-shows/{id}/players', [\App\Http\Controllers\Admin\LiveShowController::class, 'allPlayers'])->name('live-shows.players');

    Route::post('live-shows/stream-management/{id}/save-room-id', [\App\Http\Controllers\Admin\LiveShowController::class, 'saveRoomID'])->name('live-shows.stream-management.save-room-id');

    Route::get('live-shows/stream-management/{id}/attached-media', [\App\Http\Controllers\Admin\LiveShowController::class, 'getAttachedMedia'])->name('live-shows.stream-management.attached-media');

    Route::post('live-shows/stream-management/{id}/quizzes/{quizId}/send-quiz-question', [\App\Http\Controllers\Admin\LiveShowController::class, 'sendQuizQuestion'])->name('live-shows.stream-management.send-quiz-question');

    Route::post('live-shows/stream-management/{id}/quizzes/{quizId}/remove-quiz-question', [\App\Http\Controllers\Admin\LiveShowController::class, 'removeQuizQuestion'])->name('live-shows.stream-management.remove-quiz-question');

    Route::get('live-shows/stream-management/{id}/fetch-messages', [\App\Http\Controllers\Admin\LiveShowController::class, 'fetchChatMessages'])->name('live-shows.stream-management.fetch-chat-messages');
    Route::post('live-shows/stream-management/{id}/send-message', [\App\Http\Controllers\Admin\LiveShowController::class, 'sendMessage'])->name('live-shows.stream-management.send-message');
    Route::post('live-shows/stream-management/{id}/reset-chat', [\App\Http\Controllers\Admin\LiveShowController::class, 'resetChat'])->name('live-shows.stream-management.reset-chat');
    Route::post('live-shows/stream-management/{id}/show-winners-tab', [\App\Http\Controllers\Admin\LiveShowController::class, 'showWinnersTab'])->name('live-shows.stream-management.show-winners-tab');
    Route::post('live-shows/stream-management/{id}/hide-winners-tab', [\App\Http\Controllers\Admin\LiveShowController::class, 'hideWinnersTab'])->name('live-shows.stream-management.hide-winners-tab');
    Route::post('live-shows/stream-management/{id}/chat-status', [\App\Http\Controllers\Admin\LiveShowController::class, 'updateChatStatus'])->name('live-shows.stream-management.chat-status');
    Route::post('live-shows/stream-management/{id}/show-gallery-image', [\App\Http\Controllers\Admin\LiveShowController::class, 'showGalleryImage'])->name('live-shows.stream-management.show-gallery-image');
    Route::post('live-shows/stream-management/{id}/hide-gallery-image', [\App\Http\Controllers\Admin\LiveShowController::class, 'hideGalleryImage'])->name('live-shows.stream-management.hide-gallery-image');

    // updateWinners
    Route::post('live-shows/stream-management/{liveShowId}/update-winners', [\App\Http\Controllers\Admin\LiveShowController::class, 'updateWinners'])->name('live-shows.update-winners');
    Route::post('live-shows/stream-management/{liveShowId}/unannounce-winners', [\App\Http\Controllers\Admin\LiveShowController::class, 'unannounceWinners'])->name('live-shows.unannounce-winners');

    Route::post('live-shows/{id}/block-user/{userId}', [\App\Http\Controllers\Admin\LiveShowController::class, 'blockUser'])->name('live-shows.block-user');
    Route::post('live-shows/{id}/unblock-user/{userId}', [\App\Http\Controllers\Admin\LiveShowController::class, 'unblockUser'])->name('live-shows.unblock-user');

    Route::get('players', [\App\Http\Controllers\Admin\PlayerController::class, 'index'])->name('players.index');
    Route::get('players/{id}', [\App\Http\Controllers\Admin\PlayerController::class, 'show'])->name('players.show');

    Route::get('live-shows/{id}/get-users-quiz-responses/{quiz_id}', [\App\Http\Controllers\Admin\LiveShowController::class, 'getUsersQuizResponses'])->name('live-shows.get-users-quiz-responses');

    Route::post('live-shows/{id}/update-live-show', [\App\Http\Controllers\Admin\LiveShowController::class, 'updateLiveShow'])->name('live-shows.update-live-show');

    Route::post('live-show/{id}/admin/reset-game', [LiveShowController::class, 'resetGame'])->name('live-shows.reset-game');

    Route::get('/profile/password', [AdminProfileController::class, 'showChangePasswordForm'])
        ->name('password.form');

    Route::post('/profile/password', [AdminProfileController::class, 'updatePassword'])
        ->name('password.update');

    Route::get('/test-notification', [PushNotificationController::class, 'testNotifcation'])->name('test-notification');

    Route::post('announcement', function () {
        event(new \App\Events\AnnouncementEvent('This is a test announcement @ '.date('Y-m-d H:i:s')));

        return response()->json(['success' => true, 'message' => 'Announcement sent successfully']);
    })->name('announcement.send');

    // Route::post('live-shows/stream-management/{liveShowId}/block-user/{userId}', [\App\Http\Controllers\Admin\LiveShowController::class, 'blockUser'])->name('live-shows.block-user');
    Route::post('live-shows/stream-management/{liveShowId}/toggle-block-status-for-player/{userId}', [\App\Http\Controllers\Admin\LiveShowController::class, 'toggleBlockStatusForPlayer'])->name('live-shows.toggle-block-status-for-player');
    Route::post('live-shows/stream-management/{liveShowId}/reset-score/{userId}', [\App\Http\Controllers\Admin\LiveShowController::class, 'resetPlayerScore'])->name('live-shows.reset-player-score');

    Route::get('live-shows/{id}/export-all-chats-as-csv', [\App\Http\Controllers\Admin\LiveShowController::class, 'exportAllChatsOfLiveShowAsCSV'])->name('live-shows.export-all-chats-as-csv');
    Route::get('live-shows/{id}/export-all-users-as-csv', [\App\Http\Controllers\Admin\LiveShowController::class, 'exportAllUsersOfLiveShowAsCSV'])->name('live-shows.export-all-users-as-csv');
    Route::get('live-shows/{id}/export-all-quizzes-as-csv', [\App\Http\Controllers\Admin\LiveShowController::class, 'exportAllQuizzesOfLiveShowAsCSV'])->name('live-shows.export-all-quizzes-as-csv');

    Route::get('live-shows/{id}/view-details', [LiveShowController::class, 'viewDetails'])->name('live-shows.view-details');
    Route::get('live-shows/{id}/player-responses/{userId}', [LiveShowController::class, 'getPlayerResponses'])->name('live-shows.player-responses');
    Route::get('live-shows/{id}/export-participants-csv', [LiveShowController::class, 'exportAllParticipantsCSV'])->name('live-shows.export-participants-csv');
    Route::get('live-shows/{id}/export-player-csv/{userId}', [LiveShowController::class, 'exportPlayerCSV'])->name('live-shows.export-player-csv');
    Route::get('live-shows/{id}/export-winners-csv', [LiveShowController::class, 'exportWinnersCSV'])->name('live-shows.export-winners-csv');

    Route::resource(
        'push-notifications',
        PushNotificationController::class
    )->only(['index', 'create', 'store', 'show']);

    Route::get('analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
    Route::get('analytics/chart-data', [AnalyticsController::class, 'chartData'])->name('analytics.chart-data');
    Route::get('analytics/quiz-accuracy', [AnalyticsController::class, 'quizAccuracyData'])->name('analytics.quiz-accuracy');
    Route::get('analytics/response-time', [AnalyticsController::class, 'responseTimeData'])->name('analytics.response-time');
    Route::get('analytics/top-performers', [AnalyticsController::class, 'topPerformers'])->name('analytics.top-performers');
    Route::get('analytics/show-summary', [AnalyticsController::class, 'showSummary'])->name('analytics.show-summary');
})->middleware(['auth']);
