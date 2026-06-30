<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PushNotification;
use App\Models\PushSubscription;
use App\Services\PushNotificationService;
use Illuminate\Http\Request;

class PushNotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:can-manage-push-notifications');
    }

    public function index()
    {
        return view('admin.push-notifications.index', [
            'notifications' => PushNotification::with(['user', 'pushSubscription.user'])->latest()->paginate(20),
            'subscriptionCount' => PushSubscription::count(),
        ]);
    }

    public function create()
    {
        return view('admin.push-notifications.create', [
            'subscriptions' => PushSubscription::with('user')->latest()->get(),
            'subscriptionCount' => PushSubscription::count(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'push_subscription_id' => 'nullable|integer|exists:push_subscriptions,id',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'url' => 'nullable|string|max:2048',
        ]);

        $subscriptionId = isset($data['push_subscription_id'])
            ? (int) $data['push_subscription_id']
            : null;

        $url = trim((string) ($data['url'] ?? ''));
        if ($url === '') {
            $url = url('/');
        }

        $deviceCount = PushNotificationService::countTargetDevices(0, $subscriptionId);
        if ($deviceCount === 0) {
            return back()
                ->withInput()
                ->withErrors([
                    'message' => 'No push subscription was found for the selected target.',
                ]);
        }

        $subscription = $subscriptionId
            ? PushSubscription::find($subscriptionId)
            : null;

        $notification = PushNotification::create([
            'user_id' => $subscription?->user_id,
            'push_subscription_id' => $subscriptionId,
            'title' => $data['title'],
            'message' => $data['message'],
            'url' => $url,
            'data' => [],
        ]);

        PushNotificationService::send(
            userId: 0,
            title: $notification->title,
            message: $notification->message,
            data: ['url' => $notification->url],
            subscriptionId: $subscriptionId,
        );

        $notification->update(['sent_at' => now()]);

        $targetLabel = $subscriptionId
            ? '1 device (subscription #'.$subscriptionId.')'
            : "{$deviceCount} device(s)";

        return redirect()
            ->route('admin.push-notifications.index')
            ->with('success', "Push notification queued for {$targetLabel}.");
    }

    public function show(PushNotification $pushNotification)
    {
        $pushNotification->load(['user', 'pushSubscription.user']);

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
                'url' => url('/'),
            ],
        );

        return response()->json(['success' => true]);
    }
}
