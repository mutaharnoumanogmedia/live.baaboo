<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PushSubscription;
use Illuminate\View\View;

class PushSubscriptionController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:can-manage-push-notifications');
    }

    public function index(): View
    {
        return view('admin.push-subscriptions.index', [
            'subscriptions' => PushSubscription::with('user')
                ->latest()
                ->paginate(25),
        ]);
    }
}
