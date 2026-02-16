<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LiveShowQuiz;
use App\Models\LiveShow;

class LiveShowQuizController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index()
    {
        $quizzes = LiveShowQuiz::with('liveShow')
            ->when(request('live_show_id'), function ($query) {
                $query->where('live_show_id', request('live_show_id'));
            })
            ->get();
        return view('admin.live-show-quizzes.index', compact('quizzes'));
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

            'questions.*.options' => 'required|array|min:2',
            'questions.*.options.*.option_text' => 'required|string|max:255',

            'questions.*.correct' => 'nullable|integer',
        ]);

        foreach ($request->questions as $qIndex => $qData) {

            $quiz = LiveShowQuiz::create([
                'live_show_id' => $request->live_show_id,
                'question'     => $qData['question'],
            ]);

            $correctIndex = $qData['correct'] ?? null;

            foreach ($qData['options'] as $oIndex => $opt) {
                $quiz->options()->create([
                    'option_text' => $opt['option_text'],
                    'is_correct'  => ($oIndex == $correctIndex) ? 1 : 0,
                ]);
            }
        }

        return redirect()
            ->route('admin.live-show-quizzes.index')
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
            'question'     => 'required|string|max:255',

            'questions' => 'required|array',
            'questions.0.options' => 'required|array|min:2',

            'questions.0.options.*.option_text' => 'required|string|max:255',
            'questions.0.correct'  => 'nullable|boolean',
        ]);

        // Update main quiz fields
        $quiz->update([
            'live_show_id' => $request->live_show_id,
            'question'     => $request->question,
        ]);

        // Remove old options
        $quiz->options()->delete();

        // Extract options array
        $options = $request->questions[0]['options'];
        $correctIndex = $request->questions[0]['correct'] ?? null;

        // Loop and save new options
        foreach ($options as $index => $option) {
            $quiz->options()->create([
                'option_text' => $option['option_text'],
                'is_correct'  => $index == $correctIndex ? 1 : 0,
            ]);
        }

        return redirect()
            ->route('admin.live-show-quizzes.index')
            ->with('success', 'Quiz updated successfully.');
    }


    public function destroy($id)
    {
        $quiz = LiveShowQuiz::findOrFail($id);
        $quiz->options()->delete();
        $quiz->delete();
        return redirect()->route('admin.live-show-quizzes.index')->with('success', 'Quiz deleted successfully.');
    }
}
