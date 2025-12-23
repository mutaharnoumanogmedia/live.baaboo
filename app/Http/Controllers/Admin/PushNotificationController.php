<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\PushNotificationService;
use App\Models\PushNotification;
use App\Models\User;

class PushNotificationController extends Controller
{
    //

    public function index()
    {
        return view('admin.push-notifications.index', [
            'notifications' => PushNotification::latest()->paginate(20),
        ]);
    }

    public function create()
    {
        return view('admin.push-notifications.create', [
            'users' => User::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'nullable|integer|exists:users,id',
            'title'   => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        $notification = PushNotification::create([
            'user_id' => $data['user_id'] ?? null,
            'title'   => $data['title'],
            'message' => $data['message'],
            'data'    => ['url' => '/'],
        ]);

        PushNotificationService::send(
            userId: $notification->user_id ?? 0,
            title: $notification->title,
            message: $notification->message,
            data: $notification->data
        );

        $notification->update(['sent_at' => now()]);

        return redirect()
            ->route('admin.push-notifications.index')
            ->with('success', 'Push notification sent successfully.');
    }

    public function show(PushNotification $pushNotification)
    {
        return view('admin.push-notifications.show', [
            'notification' => $pushNotification,
        ]);
    }

    public function testNotifcation()
    {
        PushNotificationService::send(
            userId: 0,
            title: 'Live Show Started',
            message: 'Join now and win exciting prizes!',
            data: [
                'url' => '/live-show'
            ]
        );
        return response()->json(['success' => true]);
    }
}
