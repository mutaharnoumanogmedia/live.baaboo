<?php

use App\Http\Controllers\Admin\LiveShowController as AdminLiveShowController;
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

Route::prefix("live-show")->group(function () {
    Route::get("/{id}/get-live-show-users", [AdminLiveShowController::class, 'apiGetLiveShowUsers']);
    Route::get("/{id}/get-live-show-messages", [AdminLiveShowController::class, 'apiGetLiveShowMessages']);

    Route::post("/{id}/user/updateOnlineStatus", [UserLiveShowController::class, 'updateOnlineStatus']);
   
});
