<?php

namespace App\Http\Controllers;

use App\Models\LiveShow;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    //

    public function index()
    {

        $currentLiveShow = LiveShow::where('status', 'live')->with('users')->orderBy('created_at', 'desc')->first();
        return view('index', compact('currentLiveShow'));
    }

    public function dashboard_redirect()
    {
        $role = auth()->user()->getRoleNames()[0] ?? 'user';
        if ($role == 'admin') {
            return redirect()->route('admin.dashboard');
        } else {
            abort(403, __('Unauthorized action.'));
        }
    }
}
