<?php

namespace App\Http\Controllers\Admin;

use App\Events\GameResetEvent;
use App\Events\HideGalleryImageEvent;
use App\Events\LiveShowMessageEvent;
use App\Events\LiveShowQuizUserResponses;
use App\Events\RemoveLiveShowQuizQuestionEvent;
use App\Events\ResetChatEvent;
use App\Events\SetBroadcastRoomIdEvent;
use App\Events\ShowGalleryImageEvent;
use App\Events\ShowLiveShowQuizQuestionEvent;
use App\Events\ShowPlayerAsWinnerEvent;
use App\Events\UserBlockFromLiveShowEvent;
use App\Http\Controllers\Controller;
use App\Jobs\SendWinnerEmailJob;
use App\Models\GalleryMedia;
use App\Models\LiveShow;
use App\Models\LiveShowGalleryState;
use App\Models\LiveShowQuiz;
use App\Models\LiveShowWinnerPrize;
use App\Models\QuizOption;
use App\Models\UserQuiz;
use App\Models\UserQuizResponse;
use App\Services\LiveShowQuizService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class LiveShowController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:can-manage-live-shows');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $liveShows = LiveShow::orderBy('id', 'desc')->get();

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
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'scheduled_at' => 'required|date',

            'status' => 'required|in:scheduled,live,completed',
            'host_name' => 'nullable|string|max:255',
            'prize_amount' => 'required|numeric|min:0',
            'currency' => 'required|string|max:5',
            'max_winners' => 'required|integer|min:1|max:50',
            'winner_prizes' => 'nullable|array',
            'winner_prizes.*' => 'nullable|string|max:255',
            'is_test_show' => 'required|boolean',
        ]);

        $validated['created_by'] = Auth::id();
        $maxWinners = (int) $validated['max_winners'];
        $prizes = $request->input('winner_prizes', []);

        $show = LiveShow::create($validated);

        $this->syncWinnerPrizes($show->id, $maxWinners, $prizes);

        return redirect()->route('admin.live-shows.show', $show->id)->with('success', 'Live Show created successfully!');
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
        $liveShow->load(['creator', 'winnerPrizes', 'users' => function ($query) {
            $query->withPivot(['score', 'status', 'created_at', 'prize_won', 'is_winner', 'is_online', 'created_at']);
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, LiveShow $live_show)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'scheduled_at' => 'required|date',
            'status' => 'required|in:scheduled,live,completed',
            'host_name' => 'nullable|string|max:255',
            'prize_amount' => 'required|numeric|min:0',
            'currency' => 'required|string|max:5',
            'max_winners' => 'required|integer|min:1|max:50',
            'winner_prizes' => 'nullable|array',
            'winner_prizes.*' => 'nullable|string|max:255',
            'is_test_show' => 'required|boolean',
        ]);

        $validated['created_by'] = Auth::id();
        $maxWinners = (int) $validated['max_winners'];
        $prizes = $request->input('winner_prizes', []);

        $live_show->update($validated);

        $this->syncWinnerPrizes($live_show->id, $maxWinners, $prizes);

        return redirect()->route('admin.live-shows.show', $live_show->id)->with('success', 'Live Show updated successfully!');
    }

    /**
     * Validate that the first max_winners percentages sum to 100.
     */
    protected function validateWinnerPrizes(int $maxWinners, array $prizes): void
    {
        $sum = 0;
        for ($r = 1; $r <= $maxWinners; $r++) {
            $sum += (float) ($prizes[$r] ?? 0);
        }
        if (abs($sum - 100) > 0.01) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'winner_prizes' => ['The prize percentages for the first '.$maxWinners.' winner(s) must total 100%. Current total: '.round($sum, 1).'%.'],
            ]);
        }
    }

    /**
     * Sync winner percentage rows for a live show.
     */
    protected function syncWinnerPrizes(int $liveShowId, int $maxWinners, array $prizes): void
    {
        LiveShowWinnerPrize::where('live_show_id', $liveShowId)->delete();
        for ($rank = 1; $rank <= $maxWinners; $rank++) {
            $prize = (string) ($prizes[$rank] ?? 0);
            if ($prize) {
                LiveShowWinnerPrize::create([
                    'live_show_id' => $liveShowId,
                    'rank' => $rank,
                    'prize' => $prize,
                ]);
            }
        }
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
        $liveShow = LiveShow::with(['quizzes.options', 'users', 'winnerPrizes', 'galleryMedia'])->findOrFail($id);
        $allGalleryMedia = \App\Models\GalleryMedia::orderBy('created_at', 'desc')->get();

        return view('admin.live-shows.stream-management', compact('liveShow', 'allGalleryMedia'));
    }

    public function sendQuizQuestion(Request $request, $id, $quizId)
    {
        $request->validate([
            'seconds' => 'integer|min:2|max:120',
            'is_last' => 'nullable|boolean',
        ]);

        $liveShow = LiveShow::findOrFail($id);
        // $quiz = $liveShow->quizzes()

        //     ->where('id', $quizId)
        //     ->with(['options' => function ($query) {
        //         $query->select('id', 'quiz_id', 'option_text'); // exclude is_correct
        //     }])
        //     ->first();

        $quiz = LiveShowQuiz::where('id', $quizId)->first()->toArray();

        if (! $quiz) {
            return response()->json(['message' => 'Quiz not found for this live show.'], 404);
        }
        $quizOptions = QuizOption::where('quiz_id', $quizId)->select('id', 'quiz_id', 'option_text')->get()->toArray();

        // Attach quiz options directly to $quiz object for broadcasting
        $quiz['options'] = $quizOptions;

        // total quiz questions
        $totalQuizQuestions = $liveShow->quizzes()->count();
        $quiz['totalQuizQuestions'] = $totalQuizQuestions;
        // check this question is at what index in all quiz questions
        $quizQuestionIndex = $liveShow->quizzes()->get()->toArray();
        // from all quiz questions, get the index of this quiz question
        $quizQuestionIndex = array_search($quizId, array_column($quizQuestionIndex, 'id'));

        // Broadcast the quiz question to users
        ShowLiveShowQuizQuestionEvent::dispatch($quiz, (string) $liveShow->id, $request->seconds ?? 10, $request->is_last ?? false, $quizQuestionIndex+1);

        return response()->json(['message' => 'Quiz question sent successfully!']);
    }

    public function removeQuizQuestion(Request $request, $id, $quizId)
    {
        $liveShow = LiveShow::findOrFail($id);
        $quiz = $liveShow->quizzes()->where('id', $quizId)->first();

        if (! $quiz) {
            return response()->json(['message' => 'Quiz not found for this live show.'], 404);
        }

        // Broadcast an event to remove the quiz question from users
        RemoveLiveShowQuizQuestionEvent::dispatch($quiz->id, (string) $liveShow->id);

        return response()->json(['message' => 'Quiz question removed successfully!']);
    }

    public function updateWinners(Request $request, $liveShowId)
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['message' => 'unauthorized', 'authStatus' => Auth::check()], 401);
        }

        $liveShow = LiveShow::find($liveShowId);
        if (! $liveShow) {
            return response()->json(['message' => 'Live show not found.'], 404);
        }
        // make all users is_winner = false
        $liveShow->users()->update(['is_winner' => false]);

        $maxWinners = (int) $liveShow->max_winners;
        $topMaxWinnersByScore =
            $topMaxWinnersByScore = $liveShow->users()
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
                ->take($maxWinners);

        // Update pivot table to set is_winner = true for top three users
        foreach ($topMaxWinnersByScore as $winner) {
            $liveShow->users()->updateExistingPivot($winner['id'], ['is_winner' => true]);
        }

        // update each winner prize won
        foreach ($topMaxWinnersByScore as $index => $winner) {
            $prizeWon = $liveShow->winnerPrizes()->where('rank', $index + 1)->first()->prize ?? 'n/a';
            \Log::info("Winner {$winner['id']} prize won: {$prizeWon}");

            $liveShow->users()->updateExistingPivot($winner['id'], ['prize_won' => $prizeWon]);
            ShowPlayerAsWinnerEvent::dispatch($winner['id'], (string) $liveShowId);
            \Log::info("ShowPlayerAsWinnerEvent dispatched for user ID {$winner['id']}, live show ID {$liveShowId} and prize won: {$prizeWon}");
            // Dispatch job to send winner email after 30 minutes
            try {
                SendWinnerEmailJob::dispatch($winner['id'], $prizeWon, $liveShow)->delay(now()->addMinutes(2));
            } catch (\Exception $e) {
                // log the error
                \Log::error("Failed to dispatch SendWinnerEmailJob for user ID {$winner['id']}: ".$e->getMessage());
            }
        }

        return response()->json(['success' => true, 'message' => 'Users winner status updated.', 'winnerUsers' => $topMaxWinnersByScore]);
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
        if (! $user) {
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
            'event' => $event,
        ], 200);
    }

    public function resetChat($id): JsonResponse
    {
        $liveShow = LiveShow::findOrFail($id);
        $liveShow->messages()->delete();
        ResetChatEvent::dispatch((string) $liveShow->id);

        return response()->json([
            'success' => true,
            'message' => 'Chat reset successfully.',
        ]);
    }

    public function showGalleryImage(Request $request, $id): JsonResponse
    {
        $request->validate([
            'gallery_media_id' => 'required|integer|exists:gallery_media,id',
        ]);

        $liveShow = LiveShow::findOrFail($id);
        $media = GalleryMedia::findOrFail($request->input('gallery_media_id'));

        if (! $liveShow->galleryMedia()->where('gallery_media.id', $media->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'This media is not attached to this stream.',
            ], 422);
        }

        $playbackStartedAt = $media->type === 'video' ? now() : null;

        DB::transaction(function () use ($liveShow, $media, $playbackStartedAt) {
            LiveShowGalleryState::updateOrCreate(
                ['live_show_id' => $liveShow->id],
                [
                    'is_visible' => true,
                    'gallery_media_id' => $media->id,
                    'url' => $media->url,
                    'media_type' => $media->type,
                    'playback_started_at' => $playbackStartedAt,
                    'video_duration_seconds' => null,
                ]
            );
        });

        ShowGalleryImageEvent::dispatch(
            (string) $liveShow->id,
            $media->path,
            $media->type,
            $playbackStartedAt?->toIso8601String(),
            null
        );

        return response()->json([
            'success' => true,
            'message' => 'Image shown on stream.',
        ]);
    }

    public function hideGalleryImage($id): JsonResponse
    {
        $liveShow = LiveShow::findOrFail($id);
        $liveShow->galleryState?->update(['is_visible' => false]);
        HideGalleryImageEvent::dispatch((string) $liveShow->id);

        return response()->json([
            'success' => true,
            'message' => 'Gallery overlay hidden on stream.',
        ]);
    }

    public function apiGetLiveShowUsers($id)
    {
        $liveShow = LiveShow::findOrFail($id);

        $skip = request()->get('skip', 0);
        $take = request()->get('take', 100);

        $totalUsers = $liveShow->users()->count();

        $users = $liveShow->users()
            ->withPivot(['score', 'status', 'is_winner', 'created_at', 'last_active', 'is_online', 'prize_won'])
            ->orderByDesc('user_live_shows.score')
            ->skip($skip)
            ->take($take)
            ->get()
            ->map(function ($user) use ($id) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'is_online' => $user->pivot->is_online,
                    'is_winner' => $user->pivot->is_winner ?? null,
                    'status' => $user->pivot->status ?? null,
                    'score' => $user->pivot->score ?? null,
                    'prize_won' => $user->pivot->prize_won ?? null,
                    'is_blocked' => $user->blockedLiveShows()
                        ->where('live_show_id', $id)
                        ->exists(),
                ];
            })
            ->values();

        return response()->json(['users' => $users, 'totalUsers' => $totalUsers]);
    }

    public function extractYouTubeId(string $url): ?string
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

    public function calculateEachWinnerPrize(LiveShow $liveShow)
    {
        $winnersCount = $liveShow->users()->wherePivot('status', 'registered')->wherePivot('is_winner', true)->count();
        if ($winnersCount > 0) {
            return $liveShow->prize_amount / $winnersCount;
        }

        return 0;
    }

    public function toggleBlockStatusForPlayer(Request $request, $liveShowId, $userId)
    {
        $request->validate([
            'action' => 'required|string|in:block,unblock',
        ]);

        $action = $request->input('action');
        $liveShow = LiveShow::findOrFail($liveShowId);
        $user = $liveShow->users()->where('user_id', $userId)->first();

        if (! $user) {
            return response()->json(['success' => false, 'message' => 'User not found in this live show.'], 404);
        }

        if ($action === 'block') {
            $liveShow->blockedUsers()->syncWithoutDetaching($userId);
        } else {
            $liveShow->blockedUsers()->detach($userId);
        }

        // event to update the player block status
        $isBlocked = $action === 'block' ? true : false;
        UserBlockFromLiveShowEvent::dispatch($liveShowId, $userId, $isBlocked);

        return response()->json(['success' => true, 'message' => 'Player block status updated to '.($isBlocked ? 'blocked' : 'unblocked').'.', 'user' => $user]);
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
        \App\Events\UpdateLiveShowEvent::dispatch((string) $liveShow->id, $liveShow->status);

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
        if (! $liveShow) {
            return response()->json(['success' => false, 'message' => 'Live show not found.'], 404);
        }

        // Find the quiz and eager-load its options, as the service needs them
        $quiz = $liveShow->quizzes()->with('options')->find($quiz_id);
        if (! $quiz) {
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
        LiveShowQuizUserResponses::dispatch((string) $liveShow->id, (string) $quiz->id, $statistics, $correctOptionId);

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
        if (! $liveShow) {
            return response()->json(['message' => 'Live show not found.'], 404);
        }
        // remove all user quiz responses

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

    public function streamBroadcaster($id)
    {
        $liveShow = LiveShow::with(['quizzes.options'])->findOrFail($id);

        return view('admin.live-shows.stream-broadcaster', compact('liveShow'));
    }

    public function saveRoomID(Request $request, $id)
    {
        $request->validate([
            'room_id' => 'required|string|max:255',
        ]);

        $liveShow = LiveShow::findOrFail($id);
        $liveShow->stream_id = $request->input('room_id');
        $liveShow->save();

        // call event set broadcast room id
        event(new SetBroadcastRoomIdEvent($liveShow->id, $liveShow->stream_id));

        return response()->json(['message' => 'Room ID saved successfully!', 'room_id' => $liveShow->stream_id]);
    }

    public function exportAllChatsOfLiveShowAsCSV($id)
    {
        $liveShow = LiveShow::findOrFail($id);
        $chats = $liveShow->messages()->get();
        $csv = fopen('php://temp', 'w');
        fputcsv($csv, ['User', 'Message', 'Created At']);
        foreach ($chats as $chat) {
            fputcsv($csv, [$chat->user->name, $chat->message, $chat->created_at]);
        }

        rewind($csv);
        $csvContents = stream_get_contents($csv);
        $filename = 'chats_'.$liveShow->title.'.csv';
        fclose($csv);

        return response($csvContents)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="'.$filename.'"');
    }

    public function exportAllUsersOfLiveShowAsCSV($id)
    {
        $liveShow = LiveShow::findOrFail($id);
        // Fetch users with their UserQuiz results
        $users = $liveShow->users()->with(['quizzes' => function ($query) use ($id) {
            $query->where('live_show_id', $id);
        }])->get();
        $csv = fopen('php://temp', 'w');
        // Header: User info + summary of quizzes
        fputcsv($csv, ['User', 'Email', 'Created At', 'Total Quizzes', 'Total Correct Answers', 'Total Score']);
        foreach ($users as $user) {
            $userQuizzes = $user->quizzes;
            $totalQuizzes = $userQuizzes->count();
            fputcsv($csv, [$user->name, $user->email, $user->created_at, $totalQuizzes, $user->pivot->score, $user->pivot->is_winner ? 'Yes' : 'No', $user->pivot->prize_won]);
        }

        // download the csv file
        rewind($csv);
        $csvContents = stream_get_contents($csv);
        $filename = 'users_'.$liveShow->title.'.csv';
        fclose($csv);

        return response($csvContents)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="'.$filename.'"');
    }

    public function exportAllQuizzesOfLiveShowAsCSV($id)
    {
        $liveShow = LiveShow::findOrFail($id);
        $quizzes = $liveShow->quizzes()->get();
        $csv = fopen('php://temp', 'w');
        fputcsv($csv, ['Quiz', 'Question', 'Created At']);
        foreach ($quizzes as $quiz) {
            fputcsv($csv, [$quiz->question, $quiz->created_at]);
        }
        fclose($csv);

        // download the csv file
        return response()->download($csv, 'quizzes'.$liveShow->title.'.csv');
    }
}
