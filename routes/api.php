<?php

use Agence104\LiveKit\AccessToken;
use Agence104\LiveKit\AccessTokenOptions;
use Agence104\LiveKit\VideoGrant;
use App\Http\Controllers\Admin\LiveShowController as AdminLiveShowController;
use App\Http\Controllers\GamePlayController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PushNotificationController;
use App\Http\Controllers\User\LiveShowController as UserLiveShowController;
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

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('live-show')->group(function () {
    Route::get('/{id}/get-live-show-users', [AdminLiveShowController::class, 'apiGetLiveShowUsers']);
    Route::get('/{id}/get-live-show-messages', [AdminLiveShowController::class, 'apiGetLiveShowMessages']);

    Route::post('/{id}/user/updateOnlineStatus', [UserLiveShowController::class, 'updateOnlineStatus']);

});

Route::get('/livekit-token', function (Request $request) {
    $room = $request->query('room');
    $identity = $request->query('identity');
    $role = $request->query('role', 'viewer'); // broadcaster/viewer

    if (! $room || ! $identity) {
        return response()->json(['error' => 'Missing params'], 400);
    }

    $grant = (new VideoGrant)
        ->setRoomJoin(true)
        ->setRoomName($room);

    if ($role === 'broadcaster') {
        $grant
            ->setCanPublish(true)
            ->setCanSubscribe(true);
    } else {
        $grant
            ->setCanPublish(false)
            ->setCanSubscribe(true);
    }

    $tokenOptions = (new AccessTokenOptions)
        ->setIdentity($identity)
        ->setTtl(6 * 60 * 60);

    $token = (new AccessToken(env('LIVEKIT_API_KEY'), env('LIVEKIT_API_SECRET')))
        ->init($tokenOptions)
        ->setGrant($grant)
        ->toJwt();

    return [
        'token' => $token,
        'url' => env('LIVEKIT_URL'),
    ];
});

Route::post('/push/subscribe', [PushNotificationController::class, 'subscribe']);

Route::get('/get-latest-live-or-scheduled-show', [GamePlayController::class, 'getLatestLiveOrScheduledShow'])->name('api.get-latest-live-or-scheduled-show');

Route::post('/register-affiliate-user', [HomeController::class, 'registerAffiliateUserViaAPI'])->name('api.register-affiliate-user');
Route::get('/get-affiliate-user/{userName}', [HomeController::class, 'getAffiliateUserViaAPI'])->name('api.get-affiliate-user');

// auto test apis

// Convert: sendQuizQuestion
Route::post('/live-show/{id}/quizzes/{quizId}/send-quiz-question', function (\Illuminate\Http\Request $request, $id, $quizId) {
    $request->validate([
        'seconds' => 'integer|min:2|max:120',
        'is_last' => 'nullable|boolean',
    ]);

    $liveShow = \App\Models\LiveShow::findOrFail($id);

    $quiz = \App\Models\LiveShowQuiz::where('id', $quizId)->first();
    if (! $quiz) {
        return response()->json(['message' => 'Quiz not found for this live show.'], 404);
    }
    $quizArr = $quiz->toArray();

    $quizOptions = \App\Models\QuizOption::where('quiz_id', $quizId)->select('id', 'quiz_id', 'option_text')->get()->toArray();
    $quizArr['options'] = $quizOptions;

    $totalQuizQuestions = $liveShow->quizzes()->count();
    $quizArr['totalQuizQuestions'] = $totalQuizQuestions;

    \App\Events\ShowLiveShowQuizQuestionEvent::dispatch(
        $quizArr, (string) $liveShow->id, $request->seconds ?? 10, $request->is_last ?? false
    );

    return response()->json(['message' => 'Quiz question sent successfully!']);
});

// Convert: removeQuizQuestion
Route::post('/live-show/{id}/quizzes/{quizId}/remove-quiz-question', function (\Illuminate\Http\Request $request, $id, $quizId) {
    $liveShow = \App\Models\LiveShow::findOrFail($id);
    $quiz = $liveShow->quizzes()->where('id', $quizId)->first();

    if (! $quiz) {
        return response()->json(['message' => 'Quiz not found for this live show.'], 404);
    }

    \App\Events\RemoveLiveShowQuizQuestionEvent::dispatch($quiz->id, (string) $liveShow->id);

    return response()->json(['message' => 'Quiz question removed successfully!']);
});

Route::get('live-show/{id}/get-live-show-quizzes', function ($id) {

    $liveShow = \App\Models\LiveShow::findOrFail($id);
    $quizzes = $liveShow->quizzes()->with('options')->get();

    return response()->json($quizzes);

})->name('api.get-live-show-quizzes');
