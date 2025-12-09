<?php

namespace App\Http\Controllers\Admin;

use App\Events\GameResetEvent;
use App\Events\LiveShowMessageEvent;
use App\Events\LiveShowQuizUserResponses;
use App\Events\RemoveLiveShowQuizQuestionEvent;
use App\Events\ShowLiveShowQuizQuestionEvent;
use App\Events\ShowPlayerAsWinnerEvent;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LiveShow;
use App\Models\LiveShowMessages;
use App\Models\UserQuiz;
use App\Models\UserQuizResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Services\LiveShowService;
use App\Services\LiveShowQuizService;
use Illuminate\Http\JsonResponse;


class LiveShowController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('auth');
    // }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $liveShows = LiveShow::orderBy('id', 'asc')->get();
        return view('admin.live-shows.index', compact('liveShows'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        return view('admin.live-shows.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'        => 'required|string|max:255',
            'description'  => 'nullable|string',
            'scheduled_at' => 'required|date',
            'stream_link'  => 'nullable|string',
            'status'       => 'required|in:scheduled,live,completed',
            'host_name'    => 'nullable|string|max:255',
            'prize_amount' => 'required|numeric|min:0',
            'currency'     => 'required|string|max:5',
            // 'thumbnail'    => 'nullable|file|image',
            // 'banner'       => 'nullable|file|image',
        ]);

        // if ($request->hasFile('thumbnail')) {
        //     $path = $request->file('thumbnail')->store('thumbnails', 'public');
        //     $validated['thumbnail'] = asset('storage/' . $path);
        // }
        // if ($request->hasFile('banner')) {
        //     $path = $request->file('banner')->store('banners', 'public');
        //     $validated['banner'] = asset('storage/' . $path);
        // }

        $validated['created_by'] = Auth::id();
        $videoId = $this->extractYouTubeId($validated['stream_link'] ?? '');

        $thumbnailUrl = 'https://img.youtube.com/vi/' . $videoId . '/hqdefault.jpg';
        $validated['thumbnail'] = $thumbnailUrl;



        $show = LiveShow::create($validated);

        return redirect()->route('live-shows.show', $show->id)->with('success', 'Live Show created successfully!');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(LiveShow $liveShow)
    {
        // eager-load users from pivot table
        $liveShow->load(['creator', 'users' => function ($query) {
            $query->withPivot(['score', 'status', 'created_at']);
        }]);
        return view('admin.live-shows.show', compact('liveShow'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(LiveShow $liveShow)
    {
        //
        return view('admin.live-shows.edit', compact('liveShow'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, LiveShow $live_show)
    {
        $validated = $request->validate([
            'title'        => 'required|string|max:255',
            'description'  => 'nullable|string',
            'scheduled_at' => 'required|date',
            'stream_link'  => 'nullable|string',
            'status'       => 'required|in:scheduled,live,completed',
            'host_name'    => 'nullable|string|max:255',
            'prize_amount' => 'required|numeric|min:0',
            'currency'     => 'required|string|max:5',

        ]);

        $validated['created_by'] = Auth::id();
        $videoId = $this->extractYouTubeId($validated['stream_link'] ?? '');


        $thumbnailUrl = 'https://img.youtube.com/vi/' . $videoId . '/hqdefault.jpg';
        $validated['thumbnail'] = $thumbnailUrl;


        $live_show->update($validated);

        return redirect()->route('admin.live-shows.show', $live_show->id)->with('success', 'Live Show updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(LiveShow $live_show)
    {
        $live_show->delete();
        if ($live_show->thumbnail) {
            $thumbnailPath = str_replace(asset('storage/'), '', $live_show->thumbnail);
            Storage::disk('public')->delete($thumbnailPath);
        }
        if ($live_show->banner) {
            $bannerPath = str_replace(asset('storage/'), '', $live_show->banner);
            Storage::disk('public')->delete($bannerPath);
        }
        return redirect()->route('admin.live-shows.index')->with('success', 'Live Show deleted!');
    }


    public function streamManagement($id)
    {
        $liveShow = LiveShow::with(['quizzes.options'])->findOrFail($id);
        return view('admin.live-shows.stream-management', compact('liveShow'));
    }

    public function sendQuizQuestion(Request $request, $id, $quizId)
    {
        $request->validate([
            'seconds' => 'required|integer|min:2|max:120',
            'is_last' => 'nullable|boolean',
        ]);

        $liveShow = LiveShow::findOrFail($id);
        $quiz = $liveShow->quizzes()
            ->with(['options' => function ($query) {
                $query->select('id', 'quiz_id', 'option_text'); // exclude is_correct
            }])
            ->where('id', $quizId)
            ->first();

        if (!$quiz) {
            return response()->json(['message' => 'Quiz not found for this live show.'], 404);
        }

        // Broadcast the quiz question to users
        ShowLiveShowQuizQuestionEvent::dispatch($quiz, (string)$liveShow->id, $request->seconds, $request->is_last ?? false);


        return response()->json(['message' => 'Quiz question sent successfully!']);
    }


    public function removeQuizQuestion(Request $request, $id, $quizId)
    {
        $liveShow = LiveShow::findOrFail($id);
        $quiz = $liveShow->quizzes()->where('id', $quizId)->first();

        if (!$quiz) {
            return response()->json(['message' => 'Quiz not found for this live show.'], 404);
        }

        // Broadcast an event to remove the quiz question from users
        RemoveLiveShowQuizQuestionEvent::dispatch($quiz->id, (string)$liveShow->id);

        return response()->json(['message' => 'Quiz question removed successfully!']);
    }



    public function updateWinners(Request $request, $liveShowId)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'unauthorized', 'authStatus' => Auth::check()], 401);
        }

        $liveShow = LiveShow::find($liveShowId);
        if (!$liveShow) {
            return response()->json(['message' => 'Live show not found.'], 404);
        }
        $topThreeUsersByScore =
            $topThreeUsersByScore = $liveShow->users()
            ->with(['quizResponses' => function ($query) use ($liveShowId) {
                $query->whereHas('userQuiz.quiz', function ($q) use ($liveShowId) {
                    $q->where('live_show_id', $liveShowId);
                });
            }])
            ->wherePivot('status', 'registered')
            ->get()
            ->map(function ($user) use ($liveShowId) {
                // Calculate total score
                $score = $user->pivot->score ?? 0;
                // Find the user's quiz responses for this live show and calculate total seconds_to_submit
                $userQuizResponses = $user->quizResponses()
                    ->whereHas('userQuiz', function ($q) use ($liveShowId) {
                        $q->where('live_show_id', $liveShowId);
                    })
                    ->get();

                $totalSecondsToSubmit = $userQuizResponses->sum('seconds_to_submit');
                $firstResponseTime = $userQuizResponses->min('created_at') ?? now();

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'score' => $score,
                    'total_seconds_to_submit' => $totalSecondsToSubmit,
                    'first_response_time' => $firstResponseTime,
                ];
            })
            ->sort(function ($a, $b) {
                // Sort by score descending, then by total_seconds_to_submit ascending
                if ($a['score'] === $b['score']) {
                    return $a['total_seconds_to_submit'] <=> $b['total_seconds_to_submit'];
                }
                return $b['score'] <=> $a['score'];
            })
            ->values()
            ->take(3);

        // Update pivot table to set is_winner = true for top three users
        foreach ($topThreeUsersByScore as $winner) {
            $liveShow->users()->updateExistingPivot($winner['id'], ['is_winner' => true]);
        }



        $prizeWon = $this->calculateEachWinnerPrize($liveShow);
        //update each winner prize won
        foreach ($topThreeUsersByScore as $winner) {
            $liveShow->users()->updateExistingPivot($winner['id'], ['prize_won' => $prizeWon]);
            ShowPlayerAsWinnerEvent::dispatch($winner['id'], (string)$liveShowId, $prizeWon);
        }




        return response()->json(['success' => true, 'message' => 'Users winner status updated.', 'winnerUsers' => $topThreeUsersByScore]);
    }


    public function apiGetLiveShowMessages($id)
    {
        $liveShow = LiveShow::findOrFail($id);
        $messages = $liveShow->messages()->with('user')->orderBy('created_at', 'desc')->get()->reverse()->values();
        return response()->json($messages);
    }

    public function sendMessage(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $liveShow = LiveShow::findOrFail($id);
        $user = Auth::guard('admin')->user();
        if (!$user) {
            return response()->json(['message' => 'unauthorized', 'authStatus' => Auth::check()], 401);
        }
        $messageText = $request->input('message');


        // saving the message to the database
        $message = $liveShow->messages()->create([
            'user_id' => $user->id,
            'message' => $messageText,
        ]);

        // Broadcast the new message to users


        // Broadcast the message to other users (you can implement this using events and broadcasting)
        $event = LiveShowMessageEvent::dispatch([
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

        $messageResponse = [
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
        ];
        // For simplicity, we'll just return the message in the response
        return response()->json([
            'success' => true,
            'message' => 'Message sent successfully.',
            'data' => $messageResponse,
            'user' => $user,
            'event' => $event
        ], 200);
    }







    public function apiGetLiveShowUsers($id)
    {
        $liveShow = LiveShow::with(['users' => function ($query) {
            $query->withPivot(['score', 'status', 'is_winner', 'created_at', 'last_active', 'is_online']);
        }])->findOrFail($id);


        $liveShow->users = $liveShow->users->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_online' => $user->pivot->is_online,
                'is_winner' => $user->pivot->is_winner ?? null,
                'status' => $user->pivot->status ?? null,
            ];
        });

        return response()->json($liveShow->users);
    }


    function extractYouTubeId(string $url): ?string
    {
        // Handle HTML entities like &amp; in the URL
        $url = html_entity_decode($url);

        // Match multiple possible YouTube URL formats
        $pattern = '%(?:youtube\.com/(?:.*v=|(?:embed|shorts)/)|youtu\.be/)([^?&/]+)%i';

        if (preg_match($pattern, $url, $matches)) {
            return $matches[1];
        }

        return null;
    }


    function calculateEachWinnerPrize(LiveShow $liveShow)
    {
        $winnersCount = $liveShow->users()->wherePivot('status', 'registered')->wherePivot('is_winner', true)->count();
        if ($winnersCount > 0) {
            return $liveShow->prize_amount / $winnersCount;
        }
        return 0;
    }


    function blockUser(Request $request, $id, $userId)
    {
        $liveShow = LiveShow::findOrFail($id);
        $user = $liveShow->users()->where('user_id', $userId)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found in this live show.'], 404);
        }



        return response()->json(['message' => 'User has been blocked from the live show.']);
    }

    public function updateLiveShow(Request $request, $id)
    {
        $liveShow = LiveShow::findOrFail($id);

        $request->validate([
            'status' => 'required|in:scheduled,live,completed',
        ]);

        $liveShow->status = $request->input('status');
        $liveShow->save();

        // Broadcast the update live show event
        \App\Events\UpdateLiveShowEvent::dispatch((string)$liveShow->id, $liveShow->status);

        return response()->json(['message' => 'Live show has been updated successfully.']);
    }




    public function getUsersQuizResponses(
        Request $request,
        LiveShowQuizService $quizService, //  Inject the service via method injection
        $id,
        $quiz_id
    ): JsonResponse {

        // 1. VALIDATION & DATA FETCHING
        $liveShow = LiveShow::find($id);
        if (!$liveShow) {
            return response()->json(['success' => false, 'message' => 'Live show not found.'], 404);
        }

        // Find the quiz and eager-load its options, as the service needs them
        $quiz = $liveShow->quizzes()->with('options')->find($quiz_id);
        if (!$quiz) {
            return response()->json(['success' => false, 'message' => 'Quiz not found for this live show.'], 404);
        }


        // Delegate the complex calculation logic to the service
        $statistics = $quizService->calculateResponseStatistics($quiz);

        // Fetch any other data needed for the response
        $userQuizzes = $quiz->userQuizzes()->with('userQuizResponses')->get();
        //  take the correct answer id
        $correctOption = $quiz->options->firstWhere('is_correct', true);
        $correctOptionId = $correctOption ? $correctOption->id : null;

        // 2. BROADCASTING
        LiveShowQuizUserResponses::dispatch((string)$liveShow->id, (string)$quiz->id, $statistics, $correctOptionId);



        // The controller's job is to format the final JSON response
        return response()->json([
            'success' => true,
            'statistics' => $statistics,
            'correct_option_id' => $correctOptionId,
        ]);
    }




    public function resetGame(Request $request, $liveShowId)
    {
        $liveShow = LiveShow::find($liveShowId);
        if (!$liveShow) {
            return response()->json(['message' => 'Live show not found.'], 404);
        }
        //remove all user quiz responses

        UserQuizResponse::whereIn('user_quiz_id', function ($query) use ($liveShowId) {
            $query->select('id')
                ->from('user_quizzes')
                ->where('live_show_id', $liveShowId);
        })->delete();
        UserQuiz::where('live_show_id', $liveShowId)->delete();
        // Detach all users from the live show
        $liveShow->users()->detach();

        event(new GameResetEvent($liveShowId));


        return response()->json(['success' => true, 'message' => 'Game has been reset successfully.']);
    }
}
