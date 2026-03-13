<?php

namespace App\Http\Controllers;

use App\Mail\RegistrationWelcomeMail;
use App\Models\LiveShow;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class HomeController extends Controller
{
    //

    public function index()
    {

        $currentLiveShow = LiveShow::where('status', 'live')->with('users')->orderBy('created_at', 'desc')->first();

        return view('index', compact('currentLiveShow'));
    }

    public function thankYouForYourParticipation($userName)
    {
        $user = User::where('user_name', $userName)->first();
        if (! $user) {
            return redirect()->route('index')->with('error', 'User not found');
        }

        return view('thank-you-for-your-participation', compact('user'));
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

    public function registerUserViaForm($user_name)
    {

        $referredByUser = User::where('user_name', $user_name)->first();
        if (! $referredByUser) {
            return redirect()->route('index')->with('error', 'Referred by user not found');
        }
         

        return view('index', compact('referredByUser'));
    }

    public function registerUserViaFormSubmit(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'agree_for_terms' => 'required|in:1,0',
            'agree_for_email' => 'sometimes',
        ], [
            'name.required' => 'Dein Name ist erforderlich',
            'email.required' => 'Deine E-Mail-Adresse ist erforderlich',
            'email.email' => 'Deine E-Mail-Adresse ist nicht gültig',
            'email.unique' => 'Deine E-Mail-Adresse ist bereits registriert',
            'agree_for_terms.required' => 'Du musst die Allgemeinen Geschäftsbedigungen akzeptieren',
        ]);

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt(\Str::random(8)),
                'agree_for_terms' => $request->agree_for_terms ? 1 : 0,
                'agree_for_email' => $request->agree_for_email ? 1 : 0,
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
            $leadGenerationPayload = [
                'name' => $user->name,
                'email' => $user->email,
                'user_name' => $user->user_name,
                'magic_link' => $user->magic_link,
                'referral_link' => $user->referral_link,
            ];
            $leadGenerationResponse = $this->leadGeneration($leadGenerationPayload);
            \Log::info('Lead generation request sent successfully', $leadGenerationPayload);

            \Log::info('User created successfully', $user->toArray());
        } catch (\Exception $e) {
            \Log::error('Error sending lead generation request: '.$e->getMessage(), $e->getTrace());

            return redirect()->route('index')->with('error', 'Error sending lead generation request: '.$e->getMessage());
        }

        return redirect()->route('thank-you-for-your-participation', ['user_name' => $user->user_name]);
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
                'message' => 'Validation failed. '.$validator->errors()->first(),
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
            'message' => 'Du bist jetzt angemeldet!',
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

    // Lead Generation (POST)
    public function leadGeneration($requestPayload)
    {
        $response = Http::withHeaders([
            'Authorization' => 'f0a97e7aaa3291487316d9c3d9e67b96c32dee09ad2c573af6c272341edb70e7',
            'Origin' => env('FRONTEND_URL'),

        ])->asForm()->post(env('AFFILIATE_API_ENDPOINT').'/api/lead-generation', [
            'name' => $requestPayload['name'],
            'email' => $requestPayload['email'],
            // 'partner_username' => $requestPayload['user_name'],
            'magic_link' => $requestPayload['magic_link'],
            'referral_link' => $requestPayload['referral_link'],
        ]);

        return $response->json();
    }

    // Get LiveShow Details (GET)
    public function getLiveShowDetails(Request $request)
    {
        $username = $request->input('username', 'ogmuth');
        $response = Http::withHeaders([
            'Authorization' => 'f0a97e7aaa3291487316d9c3d9e67b96c32dee09ad2c573af6c272341edb70e7',
            'Origin' => env('FRONTEND_URL'),
        ])->get(env('AFFILIATE_API_ENDPOINT').'/api/user-status', [
            'username' => $username,
        ]);

        return $response->json();
    }
}
