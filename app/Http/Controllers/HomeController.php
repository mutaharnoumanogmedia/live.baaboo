<?php

namespace App\Http\Controllers;

use App\Mail\RegistrationWelcomeMail;
use App\Models\LiveShow;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

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

    public function registerUserViaFormSubmit(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
                'message' => 'Validation failed.',
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt(\Str::random(8)),
        ]);
        // create referral link
        $referredBy = $request->referred_by ?? null;
        if ($referredBy) {
            $referredByUser = User::find($referredBy);
            if ($referredByUser) {
                $user->referred_by = $referredByUser->id;
            }
        }
        $user->referral_link = $user->referralLink();
        $user->magic_link = $user->magicLink();
        $user->user_name = $user->getUserName();
        $user->save();
        $user->assignRole('user');

        Mail::to($user->email)->send(new RegistrationWelcomeMail($user));

        return response()->json([
            'success' => true,
            'message' => 'You have registed below is your referral link. Additonally it has been sent 
            to your email ',
            'referral_link' => $user->referral_link,
        ]);
    }

    public function registerUserViaForm($name)
    {
        $referredByUser = User::where('user_name', $name)->first();
        if (! $referredByUser) {
            return redirect()->route('index')->with('error', 'User not found');
        }
        

        return view('register-user-via-form', compact('referredByUser'));
    }

    public function liveShowMagicLink($name)
    {
        $user = User::where('user_name', $name)->first();
        if (! $user) {
            return redirect()->route('index')->with('error', 'User not found');
        }
        Auth::guard('web')->login($user);

        // take the lastest live show
        $liveShow = LiveShow::where('status', 'live')->orderBy('created_at', 'desc')->first();
        if (! $liveShow) {
            return redirect()->route('index')->with('error', 'No live show found');
        }

        return redirect()->route('live-show', ['id' => $liveShow->id])->with('success', 'You have been logged in successfully');
    }

    public function registerAffiliateUserViaAPI(Request $request)
    {
        // Check if the incoming request header contains a valid hash key
        $headerHashKey = request()->header('X-Hash-Key');
        $expectedHashKey = env('AFFILIATE_HASH_KEY'); // Make sure to set this in your .env

        if (! $headerHashKey || $headerHashKey !== $expectedHashKey) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Invalid or missing hash key.',
            ], 401);

        }


        $validator = \Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'user_name' => 'required|string|max:255|unique:users,user_name',
            'email' => 'required|email|max:255|unique:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
                'message' => 'Validation failed.',
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'user_name' => $request->user_name,
            'email' => $request->email,
            'password' => bcrypt(\Str::random(8)),
        ]);
        $user->referral_link = $user->referralLink();
        $user->magic_link = $user->magicLink();
       
        $user->save();
        $user->assignRole('user');

        return response()->json([
            'success' => true,
            'message' => 'User registered successfully',
            'user' => $user,
        ]);
    }

    public function getAffiliateUserViaAPI($userName)
    {
        // Check if the incoming request header contains a valid hash key
        $headerHashKey = request()->header('X-Hash-Key');
        $expectedHashKey = env('AFFILIATE_HASH_KEY'); // Make sure to set this in your .env

        if (! $headerHashKey || $headerHashKey !== $expectedHashKey) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Invalid or missing hash key.',
            ], 401);

        }

        
        $user = User::where('user_name', $userName)->with('referredUsers')->first();
        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'User found',
            'user' => $user,
        ]);
    }
}
