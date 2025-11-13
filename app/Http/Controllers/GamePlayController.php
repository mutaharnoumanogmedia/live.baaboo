<?php

namespace App\Http\Controllers;

use App\Events\LiveShowMessageEvent;
use App\Events\LiveShowOnlineUsersEvent;
use Illuminate\Http\Request;
use App\Models\LiveShow;
use App\Models\LiveShowMessages;
use App\Models\QuizOption;
use App\Models\UserQuiz;
use App\Models\UserQuizResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class GamePlayController extends Controller
{
    //
    public function liveShow($id)
    {
        $liveShow = LiveShow::with('quizzes.options')->findOrFail($id);



        $isEliminated = $this->getEliminationStatus($id);

        return view('live-show', compact('liveShow', 'isEliminated'));
    }


    public function registerUser(Request $request, $liveShowId)
    {

        if (Auth::guard('web')->check()) {
            return response()->json(['success' => false, 'messages' => ['User already logged in.'], 'user' => Auth::guard('web')->user(), 'authStatus' => Auth::guard('web')->check()]);
        }

        //if user already registered , login it and update pivot table
        $existingUser = \App\Models\User::where('email', $request->email)->first();
        if ($existingUser) {
            //match name also
            if ($existingUser->name !== $request->name) {
                return response()->json(['success' => false, 'messages' => ['The email is already registered with a different username. Please use the correct username or register with a different email.'], 'authStatus' => Auth::guard('web')->check()], 422);
            }
            //update pivot table
            $liveShow = LiveShow::live()->find($liveShowId);

            if ($liveShow) {

                $userPivot = $liveShow->users()->where('user_id', $existingUser->id)->first();

                if ($userPivot && $userPivot->pivot->status === 'eliminated') {
                    // Do not update if eliminated, just update online status
                    $liveShow->users()->updateExistingPivot($existingUser->id, ['is_online' => 1, 'last_active_at' => now()]);
                } else {
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
            }

            Auth::guard('web')->login($existingUser);

            $request->session()->regenerate();
            $this->triggerOnlineUsersEvent($liveShowId);

            //Job : send email to user with login details 

            return response()->json(['success' => true, 'message' => 'User logged in successfully.', 'user' => $existingUser, 'authStatus' => Auth::guard('web')->check(), 'isEliminated' => $this->getEliminationStatus($liveShowId)]);
        }
        //end of existing user login

        $validator = Validator::make($request->all(), [
            'name'  => 'required|alpha_num|string|max:255|unique:users,name',
            'email' => 'required|email|max:255|unique:users,email',
        ], [
            'name.unique' => 'The username has already been taken. Please choose a different one.',
            'email.unique' => 'The email has already been registered. Please use a different email.',
        ]);

        if ($validator->fails()) {
            return response()->json(['messages' => $validator->errors()->all()], 422);
        }

        $validated = $validator->validated();
        //add rand password
        $validated['password'] = bcrypt(\Str::random(8));

        $liveShow = LiveShow::live()->find($liveShowId);
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

        Auth::guard('web')->login($user);

        $request->session()->regenerate();

        $this->triggerOnlineUsersEvent($liveShowId);
        return response()->json(['success' => true, 'message' => 'User registered successfully.', 'user' => $user, 'authStatus' => Auth::guard('web')->check()]);
    }

    public function liveShowLogout(Request $request, $liveShow)
    {
        $liveShow = LiveShow::find($liveShow);
        if (!$liveShow) {
            return redirect()->back()->withErrors(['message' => 'Live show not found.']);
        }
        //dispatch event to update online users


        $user = Auth::guard('web')->user();
        if ($user) {
            //update pivot table
            $updateResult = $liveShow->users()->updateExistingPivot($user->id, ['is_online' => 0]);
        }

        $this->triggerOnlineUsersEvent($liveShow->id);

        Auth::logout();

        return redirect(route('live-show', [$liveShow->id]))->with('success', 'You have been logged out successfully.');
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
        $user = Auth::guard('web')->user();
        if (!$user) {
            return response()->json(['message' => 'unauthorized', 'authStatus' => Auth::guard('web')->check()], 401);
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

        //check if liveshow has the user
        $userPivot = $liveShow->users()->where('user_id', $user->id)->first();
        if (!$userPivot) {
            //add user to liveshow
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
        }

        $userQuiz = UserQuiz::firstOrCreate(
            [
                'user_id' => $user->id,
                'live_show_id' => $liveShow->id,
                'quiz_id' => $quizId,
            ],
            [
                'created_at' => now(),
            ]
        );
        //if user already eliminated , do not process quiz
        if ($userPivot && $userPivot->pivot->status === 'eliminated') {
            return response()->json(['success' => false, 'message' => 'User is eliminated and cannot submit quiz.'], 403);
        }

        // Here you would typically check the option against the correct answer stored in the database.
        $quizOption = QuizOption::where('id', $option)->where('quiz_id', $quizId)->first();
        //store in user_quiz_responses table
        if ($quizOption) {
            UserQuizResponse::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'quiz_id' => $quizId,
                    'user_quiz_id' => $userQuiz->id,
                ],
                [
                    'quiz_option_id' => $quizOption->id,
                    'is_correct' => $quizOption->is_correct,
                    'user_response' => $quizOption->option_text,
                    'created_at' => now(),
                ]
            );
        }
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
        $user = Auth::guard('web')->user();
        if (!$user) {
            return response()->json(['message' => 'unauthorized', 'authStatus' => Auth::guard('web')->check()], 401);
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
        $user = Auth::guard('web')->user();
        if (!$user) {
            return response()->json(['message' => 'unauthorized', 'authStatus' => Auth::guard('web')->check()], 401);
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


    public function getEliminationStatus($liveShowId): bool
    {
        if (!Auth::guard('web')->check()) {
            return false;
        }


        $user = Auth::guard('web')->user();
        if (!$user) {
            return false;
        }

        $liveShow = LiveShow::find($liveShowId);
        if (!$liveShow) {
            return false;
        }

        $status = $liveShow->users()->where('user_id', $user->id)->first()->pivot->status ?? 'registered';

        return $status == 'eliminated' ? true : false;
    }
}
