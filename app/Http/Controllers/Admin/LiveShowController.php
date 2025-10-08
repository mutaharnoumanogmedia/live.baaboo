<?php

namespace App\Http\Controllers\Admin;

use App\Events\RemoveLiveShowQuizQuestionEvent;
use App\Events\ShowLiveShowQuizQuestionEvent;
use App\Events\ShowPlayerAsWinnerEvent;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LiveShow;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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
        //take not eliminated users only
        $notEliminatedUsers = $liveShow->users()->wherePivot('status', '!=', 'eliminated')->get();
        if ($notEliminatedUsers->isEmpty()) {
            return response()->json(['message' => 'No users found for this live show.'], 404);
        }
        $prizeWon = $this->calculateEachWinnerPrize($liveShow);
        //update all  notEliminatedUsers to winner
        foreach ($notEliminatedUsers as $user) {
            $liveShow->users()->updateExistingPivot($user->id, ['is_winner' => 1, 'prize_won' => $prizeWon]);
            ShowPlayerAsWinnerEvent::dispatch($user->id, (string)$liveShowId);
        }



        return response()->json(['success' => true, 'message' => 'Users winner status updated.', 'winnerUsers' => $notEliminatedUsers]);
    }


    public function apiGetLiveShowMessages($id)
    {
        $liveShow = LiveShow::findOrFail($id);
        $messages = $liveShow->messages()->with('user')->orderBy('created_at', 'desc')->get()->reverse()->values();
        return response()->json($messages);
    }




    public function apiGetLiveShowUsers($id)
    {
        $liveShow = LiveShow::with(['users' => function ($query) {
            $query->withPivot(['score', 'status', 'is_winner', 'created_at', 'last_active', 'is_online']);
        }])

            ->findOrFail($id);


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
        $winnersCount = $liveShow->users()->wherePivot('status', 'registered')->count();
        if ($winnersCount > 0) {
            return $liveShow->prize_amount / $winnersCount;
        }
        return 0;
    }
}
