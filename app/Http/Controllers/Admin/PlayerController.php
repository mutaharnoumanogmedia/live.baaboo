<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;

class PlayerController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (!auth()->check() || auth()->user()->email !== 'admin@baaboo.com') {
                abort(403, 'Unauthorized');
            }
            return $next($request);
        });
    }

    public function index()
    {
        $players = User::role('user')->where('is_active', 1)
            ->with('liveShows')
            ->get();

        return view('admin.players.index', compact('players'));
    }

    public function show($id)
    {
        $player = User::with('liveShows')->findOrFail($id);

        return view('admin.players.show', compact('player'));
    }

    public function winners()
    {
        $winners = User::whereIn('id', function ($query) {
            $query->select('user_id')
                ->from('user_quizzes')
                ->where('score_percentage', '=', 100);
        })->with('liveShows')->get();

        return view('admin.players.winners', compact('winners'));
    }
}
