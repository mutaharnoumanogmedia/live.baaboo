<?php

namespace App\Http\Controllers;

use App\Mail\RegistrationWelcomeMail;
use App\Models\LiveShow;
use App\Models\User;
use App\Services\LeadGenerationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class HomeController extends Controller
{
    //

    public function index()
    {

        $currentLiveShow = LiveShow::query()
            ->where(function ($query) {
                $query->where('status', 'live')
                    ->orWhere(function ($q) {
                        $q->where('status', 'scheduled')
                            ->whereDate('scheduled_at', '>=', now()->toDateString());
                    });
            })
            ->orderBy('scheduled_at', 'asc')
            ->notTestShow()
            ->with('users')
            ->first();

        $scheduleShows = LiveShow::query()
            ->whereIn('status', ['live', 'scheduled'])
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '>=', now())
            ->orderBy('scheduled_at', 'asc')
            ->notTestShow()
            ->get();

        $defaultCarouselDescription = 'Teste Dein Wissen und Deine Schnelligkeit und sichere Dir die Chance auf echte Gewinne!';

        $scheduleData = [
            'carousel_items' => $scheduleShows->map(function (LiveShow $show) use ($defaultCarouselDescription) {
                $dt = $show->scheduled_at->copy()->timezone(config('app.timezone'));

                return [
                    'badge' => $show->status === 'live' ? 'LIVE' : 'BALD',
                    'date' => $dt->format('d.m.Y').' um '.$dt->format('H:i').' Uhr',
                    'title' => $show->title ?: 'Quiz&Speed Game Show',
                    'description' => $show->description ?: $defaultCarouselDescription,
                    'meta' => [
                        ['icon' => 'bulls-eye-icon.png', 'label' => 'Wissen + Speed'],
                        ['icon' => 'gift-icon.png', 'label' => 'Geldpreise & Gutscheine'],
                    ],
                ];
            })->values()->all(),
        ];

        return view('index', compact('currentLiveShow', 'scheduleData'));
    }

    /**
     * AJAX endpoint that decides whether the live-show banner should be visible.
     *
     * The decision is made entirely server-side using the application's
     * Europe/Berlin timezone, so it stays correct regardless of the visitor's
     * local clock/timezone. The banner is shown from 30 minutes before the
     * scheduled start until 1 hour after the scheduled start (or while live).
     */
    public function liveShowBannerStatus(Request $request)
    {
        $show = LiveShow::query()
            ->where(function ($query) {
                $query->where('status', 'live')
                    ->orWhere(function ($q) {
                        $q->where('status', 'scheduled')
                            ->whereDate('scheduled_at', '>=', now()->toDateString());
                    });
            })
            ->orderBy('scheduled_at', 'asc')
            ->notTestShow()
            ->first();

        $now = now(); // Europe/Berlin (config app.timezone)

        if (! $show) {
            return response()->json([
                'show' => false,
                'server_time' => $now->toIso8601String(),
            ]);
        }

        // A show that is explicitly marked live is always shown.
        if ($show->status === 'live') {
            return response()->json([
                'show' => true,
                'reason' => 'live',
                'live_show_id' => $show->id,
                'server_time' => $now->toIso8601String(),
            ]);
        }

        if (! $show->scheduled_at) {
            return response()->json([
                'show' => false,
                'live_show_id' => $show->id,
                'server_time' => $now->toIso8601String(),
            ]);
        }

        $windowStart = $show->scheduled_at->copy()->subMinutes(30);
        $windowEnd = $show->scheduled_at->copy()->addHour();

        $visible = $now->betweenIncluded($windowStart, $windowEnd);

        return response()->json([
            'show' => $visible,
            'live_show_id' => $show->id,
            'scheduled_at' => $show->scheduled_at->toIso8601String(),
            'window_start' => $windowStart->toIso8601String(),
            'window_end' => $windowEnd->toIso8601String(),
            'server_time' => $now->toIso8601String(),
        ]);
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
            if ($this->getLiveShowDetails($user_name)) {
                // create a new user
                // Fetch user details from affiliate API (as per provided response structure)
                $liveShowDetails = $this->getLiveShowDetails($user_name);

                $fullName = '';
                $email = '';
                if ($liveShowDetails && isset($liveShowDetails['user'])) {
                    $firstName = trim($liveShowDetails['user']['first_name'] ?? '');
                    $lastName = trim($liveShowDetails['user']['last_name'] ?? '');
                    $fullName = trim($firstName.' '.$lastName);
                    $email = $liveShowDetails['user']['email'] ?? ($user_name.'@partner.baaboo.com');
                } else {
                    $fullName = $user_name;
                    $email = $user_name.'@partner.baaboo.com';
                }

                $user = User::create([
                    'name' => $fullName,
                    'email' => $email,
                    'password' => bcrypt(\Str::random(8)),
                    'user_name' => $user_name,
                    'agree_for_terms' => 1,
                    'agree_for_email' => 1,
                    'is_affiliate' => 1,
                ]);
                $user->referral_link = $user->referralLink();
                $user->magic_link = $user->magicLink();
                $user->user_name = $user->getUserName();
                $user->save();
                $user->assignRole('user');
                // Mail::to($user->email)->send(new RegistrationWelcomeMail($user));
                $leadGenerationPayload = [
                    'name' => $user->name,
                    'email' => $user->email,
                    'partner_username' => $user->user_name,
                    'magic_link' => $user->magic_link,
                    'referral_link' => $user->referral_link,
                ];
                $leadGenerationResponse = $this->leadGeneration($leadGenerationPayload);
                \Log::info('Lead generation request sent successfully', $leadGenerationPayload);

                $referredByUser = $user;
            } else {
                return redirect()->route('index')->with('error', 'User not found');
            }
        }

        $currentLiveShow = LiveShow::whereIn('status', ['live', 'scheduled'])
            ->where('scheduled_at', '>=', now())
            ->orderBy('scheduled_at', 'asc')
            ->notTestShow()
            ->with('users')
            ->first();

        return view('index', compact('referredByUser', 'currentLiveShow'));
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
                'partner_username' => User::where('id', $user->referred_by)->where('is_affiliate', 1)->first()->user_name ?? null,
                'magic_link' => $user->magic_link,
                'referral_link' => $user->referral_link,
            ];
            $leadGenerationResponse = $this->leadGeneration($leadGenerationPayload);
            \Log::info('Lead generation request sent successfully', $leadGenerationResponse);

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
        $liveShow = LiveShow::query()
            ->where(function ($query) {
                $query->where('status', 'live')
                    ->orWhere(function ($q) {
                        $q->where('status', 'scheduled')
                            ->whereDate('scheduled_at', '>=', now()->toDateString());
                    });
            })
            ->orderBy('scheduled_at', 'asc')

            ->notTestShow()
            ->first();
        if (! $liveShow) {
            return redirect()->route('index')->with('error', 'No live show found');
        }

        // add user to live show users
        // logic of max players reached
        if ($liveShow->users()->count() >= $liveShow->max_players) {
            return redirect()->route('index')->with('error', 'Die maximale Anzahl an Teilnehmern wurde erreicht.');
        }

        $leadGenerationPayload = [
            'name' => $user->name,
            'email' => $user->email,

            'magic_link' => $user->magic_link,
            'referral_link' => $user->referral_link,
            'is_joined' => 1,
        ];
        $leadGenerationResponse = (new LeadGenerationService())->leadGeneration($leadGenerationPayload);
        \Log::info('Lead generation request sent successfully', $leadGenerationPayload);
        \Log::info('Lead generation response', $leadGenerationResponse);

        $liveShow->users()->syncWithoutDetaching([
            $user->id => [
                'is_online' => 1,

                'status' => 'registered',
                'last_active_at' => now(),
            ],
        ]);

        // add to active campaign

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
            'is_affiliate' => 'required|in:1,0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
                'user' => User::where('email', $request->email)->first(),
                'message' => 'Validation failed. '.$validator->errors()->first(),
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'user_name' => $request->user_name,
            'email' => $request->email,
            'password' => bcrypt(\Str::random(8)),
            'is_affiliate' => $request->is_affiliate ?? 0,
        ]);
        $user->referral_link = $user->referralLink();
        $user->magic_link = $user->magicLink();

        $user->save();
        $user->assignRole('user');
        $responseLeadGeneration = $this->leadGeneration([
            'name' => $user->name,
            'email' => $user->email,

            'magic_link' => $user->magic_link,
            'referral_link' => $user->referral_link,
        ]);
        Mail::to($user->email)->send(new RegistrationWelcomeMail($user));

        return response()->json([
            'success' => true,
            'message' => 'Du bist jetzt angemeldet!',
            'user' => $user,
            'response_lead_generation' => $responseLeadGeneration,
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

    public function agreementTerms()
    {
        return view('agreement-terms');
    }

    public function participationTerms()
    {
        return view('participation-terms');
    }

    public function privacyPolicy()
    {
        return view('privacy-policy');
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
            'partner_username' => $requestPayload['partner_username'] ?? null,
            'magic_link' => $requestPayload['magic_link'],
            'referral_link' => $requestPayload['referral_link'],
        ]);

        return $response->json();
    }

    // Get LiveShow Details (GET)
    public function getLiveShowDetails($username)
    {

        $response = Http::withHeaders([
            'Authorization' => 'f0a97e7aaa3291487316d9c3d9e67b96c32dee09ad2c573af6c272341edb70e7',
            'Origin' => env('FRONTEND_URL'),
        ])->get(env('AFFILIATE_API_ENDPOINT').'/api/user-status', [
            'username' => $username,
        ]);

        // if the response has status : treu and affiliated: true, then return the response
        if ($response->json()['status'] == true && $response->json()['affiliated'] == true) {
            // return respnse as array
            return $response->json();
        } else {
            return false;
        }
    }
}
