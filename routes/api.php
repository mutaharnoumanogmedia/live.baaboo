<?php

use App\Http\Controllers\Admin\LiveShowController as AdminLiveShowController;
use App\Http\Controllers\User\LiveShowController as UserLiveShowController;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


use Agence104\LiveKit\AccessToken;
use Agence104\LiveKit\AccessTokenOptions;
use Agence104\LiveKit\VideoGrant;
use App\Http\Controllers\GamePlayController;
use App\Http\Controllers\PushNotificationController;

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

Route::prefix("live-show")->group(function () {
    Route::get("/{id}/get-live-show-users", [AdminLiveShowController::class, 'apiGetLiveShowUsers']);
    Route::get("/{id}/get-live-show-messages", [AdminLiveShowController::class, 'apiGetLiveShowMessages']);

    Route::post("/{id}/user/updateOnlineStatus", [UserLiveShowController::class, 'updateOnlineStatus']);
});



Route::get('/livekit-token', function (Request $request) {
    $room = $request->query('room');
    $identity = $request->query('identity');
    $role = $request->query('role', 'viewer'); // broadcaster/viewer

    if (!$room || !$identity) {
        return response()->json(['error' => 'Missing params'], 400);
    }

    $grant = (new VideoGrant())
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

    $tokenOptions = (new AccessTokenOptions())
        ->setIdentity($identity)
        ->setTtl(6 * 60 * 60);

    $token = (new AccessToken(env('LIVEKIT_API_KEY'), env('LIVEKIT_API_SECRET')))
        ->init($tokenOptions)
        ->setGrant($grant)
        ->toJwt();

    return [
        'token' => $token,
        'url'   => env('LIVEKIT_URL'),
    ];
});

Route::post('/push/subscribe', [PushNotificationController::class, 'subscribe']);


Route::get('/get-latest-live-or-scheduled-show', [GamePlayController::class, 'getLatestLiveOrScheduledShow'])->name('api.get-latest-live-or-scheduled-show');