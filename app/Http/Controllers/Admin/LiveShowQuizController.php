<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LiveShow;
use App\Models\LiveShowQuiz;
use App\Models\QuizOption;
use App\Models\UserQuizResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LiveShowQuizController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:can-manage-quiz-questions');
    }

    public function index()
    {
        $quizzes = LiveShowQuiz::with(['liveShow', 'options'])
            ->when(request('live_show_id'), function ($query) {
                $query->where('live_show_id', request('live_show_id'));
            })
            ->when(request('live_show_id'), function ($query) {
                $query->orderBy('sorting_order');
            }, function ($query) {
                $query->orderBy('live_show_id', 'desc')->orderBy('sorting_order');
            })
            ->orderBy('id')
            ->get();

        $liveShows = LiveShow::query()
            ->orderByDesc('id')
            ->get(['id', 'title']);

        return view('admin.live-show-quizzes.index', compact('quizzes', 'liveShows'));
    }

    public function create()
    {
        $liveShows = LiveShow::pluck('title', 'id');

        return view('admin.live-show-quizzes.create', compact('liveShows'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'live_show_id' => 'required|exists:live_shows,id',

            'questions' => 'required|array|min:1',
            'questions.*.question' => 'required|string|max:255',
            'questions.*.is_special' => 'nullable|boolean',

            'questions.*.options' => 'required|array|min:2',
            'questions.*.options.*.option_text' => 'required|string|max:255',

            'questions.*.correct' => 'nullable|integer',
        ]);

        $nextOrder = (int) LiveShowQuiz::where('live_show_id', $request->live_show_id)->max('sorting_order') + 1;

        foreach ($request->questions as $qIndex => $qData) {

            $quiz = LiveShowQuiz::create([
                'live_show_id' => $request->live_show_id,
                'question' => $qData['question'],
                'is_special' => ! empty($qData['is_special']),
                'sorting_order' => $nextOrder + $qIndex,
                'created_by' => auth()->user()->id,
            ]);

            $correctIndex = $qData['correct'] ?? null;

            foreach ($qData['options'] as $oIndex => $opt) {
                $quiz->options()->create([
                    'option_text' => $opt['option_text'],
                    'is_correct' => ($oIndex == $correctIndex) ? 1 : 0,
                ]);
            }
        }

        return redirect()
            ->route('admin.live-show-quizzes.index', ['live_show_id' => $request->live_show_id])
            ->with('success', 'Quiz created successfully.');
    }

    public function show($id)
    {
        $quiz = LiveShowQuiz::with('options', 'liveShow')->findOrFail($id);

        return view('admin.live-show-quizzes.show', compact('quiz'));
    }

    public function edit($id)
    {
        $quiz = LiveShowQuiz::with('options')->findOrFail($id);
        $liveShows = LiveShow::pluck('title', 'id');

        return view('admin.live-show-quizzes.form', compact('quiz', 'liveShows'));
    }

    public function update(Request $request, $id)
    {
        // Capture raw submission for debugging
        // dd($request->all());

        $quiz = LiveShowQuiz::findOrFail($id);

        // Validate based on nested structure: questions[0][options]
        $request->validate([
            'live_show_id' => 'required|exists:live_shows,id',
            'question' => 'required|string|max:255',
            'is_special' => 'nullable|boolean',

            'questions' => 'required|array',
            'questions.0.options' => 'required|array|min:2',

            'questions.0.options.*.option_text' => 'required|string|max:255',
            'questions.0.correct' => 'nullable|integer',
        ]);

        // Remove old options and any responses in both response tables so a
        // quiz-type switch never leaves stale answers in the wrong scope.
        UserQuizResponse::where('quiz_id', $quiz->id)->delete();
        \App\Models\UserSpecialQuizResponse::where('quiz_id', $quiz->id)->delete();
        QuizOption::where('quiz_id', $quiz->id)->delete();
        // Update main quiz fields
        $quiz->update([
            'live_show_id' => $request->live_show_id,
            'question' => $request->question,
            'is_special' => $request->boolean('is_special'),
            'created_by' => auth()->user()->id,
        ]);

        // Extract options array
        $options = $request->questions[0]['options'];
        $correctIndex = $request->questions[0]['correct'] ?? null;

        // Loop and save new options
        foreach ($options as $index => $option) {
            $quiz->options()->create([
                'option_text' => $option['option_text'],
                'is_correct' => $index == $correctIndex ? 1 : 0,
            ]);
        }

        return redirect()
            ->route('admin.live-show-quizzes.index')
            ->with('success', 'Quiz updated successfully.');
    }

    public function destroy($id)
    {
        $quiz = LiveShowQuiz::findOrFail($id);
        UserQuizResponse::where('quiz_id', $quiz->id)->delete();
        \App\Models\UserSpecialQuizResponse::where('quiz_id', $quiz->id)->delete();
        QuizOption::where('quiz_id', $quiz->id)->delete();

        LiveShowQuiz::where('id', $id)->delete();

        return redirect()->back()->with('success', 'Quiz deleted successfully.');
    }

    public function reorder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'live_show_id' => 'required|exists:live_shows,id',
            'order' => 'required|array',
            'order.*' => 'integer|exists:live_show_quizzes,id',
        ]);

        $liveShowId = (int) $validated['live_show_id'];
        $order = array_map('intval', $validated['order']);

        $quizCount = LiveShowQuiz::where('live_show_id', $liveShowId)
            ->whereIn('id', $order)
            ->count();

        if ($quizCount !== count($order)) {
            return response()->json([
                'success' => false,
                'message' => 'One or more quizzes do not belong to this live show.',
            ], 422);
        }

        DB::transaction(function () use ($order) {
            foreach ($order as $position => $quizId) {
                LiveShowQuiz::where('id', $quizId)->update(['sorting_order' => $position]);
            }
        });

        return response()->json(['success' => true, 'message' => 'Quiz order updated.']);
    }
}
