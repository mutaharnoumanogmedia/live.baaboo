<?php

namespace App\Http\Controllers;

use App\Events\LiveShowMessageEvent;
use App\Events\LiveShowOnlineUsersEvent;
use Illuminate\Http\Request;
use App\Models\LiveShow;
use App\Models\LiveShowMessages;
use App\Models\QuizOption;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class GamePlayController extends Controller
{
    //
    public function liveShow($id)
    {
        $liveShow = LiveShow::with('quizzes.options')->findOrFail($id);
        return view('live-show', compact('liveShow'));
    }


    public function registerUser(Request $request, $liveShowId)
    {

        if (Auth::check()) {
            return response()->json(['success' => false, 'message' => 'User already logged in.', 'user' => Auth::user(), 'authStatus' => Auth::check()]);
        }

        //if user already registered , login it and update pivot table
        $existingUser = \App\Models\User::where('email', $request->email)->first();
        if ($existingUser) {
            //update pivot table
            $liveShow = LiveShow::find($liveShowId);

            if ($liveShow) {
                $liveShow->users()->syncWithoutDetaching(
                    [
                        $existingUser->id =>
                        [
                            'is_online' => 1,
                            'is_winner' => 0,
                            'created_at' => now(),
                            'updated_at' => now(),
                            'score' => 0,
                            'status' => 'registered',
                            'last_active_at' => now()
                        ]
                    ]
                );
            }

            Auth::login($existingUser);

            $request->session()->regenerate();
            $this->triggerOnlineUsersEvent($liveShowId);

            //Job : send email to user with login details 

            return response()->json(['success' => true, 'message' => 'User logged in successfully.', 'user' => $existingUser, 'authStatus' => Auth::check()]);
        }
        //end of existing user login




        $validator = Validator::make($request->all(), [
            'name'  => 'required|alpha_num|string|max:255|unique:users,name',
            'email' => 'required|email|max:255|unique:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 422);
        }

        $validated = $validator->validated();
        //add rand password
        $validated['password'] = bcrypt(\Str::random(8));

        $liveShow = LiveShow::find($liveShowId);
        if (!$liveShow) {
            return response()->json(['message' => 'Live show not found.'], 404);
        }

        // Create the user
        $user = \App\Models\User::create($validated);

        // Attach the user to the live show with default values
        $liveShow->users()->attach(
            $user->id,
            [
                'is_online' => 1,
                'is_winner' => 0,
                'created_at' => now(),
                'updated_at' => now(),
                'score' => 0,
                'status' => 'registered',
                'last_active_at' => now()
            ]
        );

        Auth::login($user);

        $request->session()->regenerate();

        $this->triggerOnlineUsersEvent($liveShowId);
        return response()->json(['success' => true, 'message' => 'User registered successfully.', 'user' => $user, 'authStatus' => Auth::check()]);
    }

    public function liveShowLogout(Request $request, $liveShow)
    {
        $liveShow = LiveShow::find($liveShow);
        if (!$liveShow) {
            return redirect()->back()->withErrors(['message' => 'Live show not found.']);
        }
        //dispatch event to update online users


        $user = Auth::user();
        if ($user) {
            //update pivot table
            $updateResult = $liveShow->users()->updateExistingPivot($user->id, ['is_online' => 0]);
        }

        $this->triggerOnlineUsersEvent($liveShow->id);

        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect()->back();
    }


    private function triggerOnlineUsersEvent($liveShowId)
    {
        $liveShow = LiveShow::find($liveShowId);
        if ($liveShow) {
            $activeUsers = $liveShow->users()
                ->wherePivot('is_online', 1)
                ->orderBy('pivot_is_online', 'desc')
                ->get()

                ->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'is_online' => $user->pivot->is_online,
                        'is_winner' => $user->pivot->is_winner ?? null,
                        'status' => $user->pivot->status ?? null,
                    ];
                })->toArray();

            LiveShowOnlineUsersEvent::dispatch($activeUsers, (string)$liveShowId);
        }
    }

    public function submitQuiz(Request $request, $liveShowId)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'unauthorized', 'authStatus' => Auth::check()], 401);
        }

        $liveShow = LiveShow::find($liveShowId);
        if (!$liveShow) {
            return response()->json(['message' => 'Live show not found.'], 404);
        }

        $quizId = $request->quiz_id;
        $option = $request->option;

        if (!$quizId || !$option) {
            return response()->json(['message' => 'Quiz ID and option are required.'], 422);
        }

        // Here you would typically check the option against the correct answer stored in the database.
        $quizOption = QuizOption::where('id', $option)->where('quiz_id', $quizId)->first();
        if (!$quizOption) {
            return response()->json(['success' => false, 'message' => 'Invalid quiz option.'], 422);
        }
        $currentScore = $liveShow->users()->where('user_id', $user->id)->first()->pivot->score ?? 0;

        if (!$quizOption->is_correct) {
            $correctOption = QuizOption::where('quiz_id', $quizId)->where('is_correct', 1)->first();
            return response()->json([
                'success' => true,
                'message' => 'Incorrect option selected.',
                'is_correct' => false,
                'selected_option_id' => $quizOption->id,
                'correct_option_id' => $correctOption->id,
                'score' => $currentScore

            ], 200);
        } else {
            $newScore =  $currentScore + 1;
            $liveShow->users()->updateExistingPivot($user->id, ['score' => $newScore]);
            return response()->json([
                'success' => true,
                'message' => 'Correct option selected.',
                'selected_option_id' => $quizOption->id,
                'correct_option_id' => $quizOption->id,
                'is_correct' => true,
                'score' => $newScore
            ], 200);
        }
    }

    public function updateEliminationStatus(Request $request, $liveShowId)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'unauthorized', 'authStatus' => Auth::check()], 401);
        }

        $liveShow = LiveShow::find($liveShowId);
        if (!$liveShow) {
            return response()->json(['message' => 'Live show not found.'], 404);
        }

        //update pivot table
        $updateResult = $liveShow->users()->updateExistingPivot($user->id, ['status' => 'eliminated']);

        $this->triggerOnlineUsersEvent($liveShowId);

        return response()->json(['success' => true, 'message' => 'User elimination status updated.', 'updateResult' => $updateResult]);
    }

    public function postMessage(Request $request, $liveShowId)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'unauthorized', 'authStatus' => Auth::check()], 401);
        }

        $liveShow = LiveShow::find($liveShowId);
        if (!$liveShow) {
            return response()->json(['message' => 'Live show not found.'], 404);
        }

        $messageText = $request->message;

        if (!$messageText || strlen(trim($messageText)) == 0) {
            return response()->json(['message' => 'Message cannot be empty.'], 422);
        }

        // Save the message to the database
        $message = new LiveShowMessages();
        $message->live_show_id = $liveShow->id;
        $message->user_id = $user->id;
        $message->message = trim($messageText);
        $message->is_removed = false;
        $message->created_at = now();
        $message->save();

        // Broadcast the message to other users (you can implement this using events and broadcasting)
        LiveShowMessageEvent::dispatch([
            'id' => $message->id,
            'live_show_id' => $liveShow->id,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'message' => $message->message,
            'created_at' => $message->created_at,
            'time_ago' => $message->time_ago,
        ]);
        // For simplicity, we'll just return the message in the response
        return response()->json([
            'success' => true,
            'message' => 'Message sent successfully.',
            'data' => $message
        ], 200);
    }


    public function fetchMessages()
    {
        $messages = LiveShowMessages::with('user')->where('is_removed', false)->orderBy('created_at', 'asc')->get();
        return response()->json(['messages' => $messages], 200);
    }

    public function reportUserMessage()
    {
        //code to report user message for admin review
        return response()->json(['message' => 'Message reported for review.'], 200);
    }
}
