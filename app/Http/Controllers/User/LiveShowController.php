<?php

namespace App\Http\Controllers\User;

use App\Events\LiveShowOnlineUsersEvent;
use App\Http\Controllers\Controller;
use App\Models\LiveShow;
use App\Models\User;
use Illuminate\Http\Request;

class LiveShowController extends Controller
{
    //

    public function index($liveShowId)
    {
        $liveShow  = LiveShow::findOrFail($liveShowId);
        return view('user.live-shows.index', compact('liveShow'));
    }


    public function updateOnlineStatus(Request $request, $liveShowId)
    {
        $user_id = $request->user_id ?? null;
        $user = User::with('liveShows')->find($user_id);

        $onlineStatus = $request->is_online == 1 ? 1 : 0;
        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }
        $liveShow = LiveShow::find($liveShowId);
        if (!$liveShow) {
            return response()->json(['message' => 'Live show not found.'], 404);
        }

        // Update the pivot table to set the user as online
        $liveShow->users()->updateExistingPivot($user->id, ['is_online' => $onlineStatus]);

        $updatedOnlineUsers = $liveShow->users()
            ->wherePivot('is_online', 1)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'is_online' => $user->pivot->is_online,
                    'is_winner' => $user->pivot->is_winner ?? null,
                ];
            })->toArray();

        LiveShowOnlineUsersEvent::dispatch($updatedOnlineUsers, (string)$liveShowId);

        return response()->json(['message' => 'User status updated to ' . ($onlineStatus ? 'online' : 'offline') . '.', 'users' => $updatedOnlineUsers]);
    }
}
