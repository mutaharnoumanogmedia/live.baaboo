<?php

namespace App\Http\Controllers;

use App\Models\LiveShow;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function dashboard_redirect()
    {
        $role = auth()->user()->getRoleNames()[0];
        if ($role == 'admin') {
            return redirect()->route('admin.dashboard');
        } else {
            abort(403, __('Unauthorized action.'));
        }
    }



  
}
