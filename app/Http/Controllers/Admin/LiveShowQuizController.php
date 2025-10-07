<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LiveShowQuiz;
use App\Models\LiveShow;

class LiveShowQuizController extends Controller
{
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
        $validated = $request->validate([
            'live_show_id' => 'required|exists:live_shows,id',
            'questions' => 'required|array|min:1',
            'questions.*.question' => 'required|string',
            'questions.*.options' => 'required|array|min:2', // at least 2 options
            'questions.*.options.*.option_text' => 'required|string',
            'questions.*.options.*.is_correct' => 'nullable|boolean',
        ]);

        foreach ($validated['questions'] as $qData) {
            // Create the quiz question
            $quiz = LiveShowQuiz::create([
                'live_show_id' => $validated['live_show_id'],
                'question' => $qData['question'],
            ]);

            // Attach its options
            foreach ($qData['options'] as $optData) {
                $quiz->options()->create([
                    'option_text' => $optData['option_text'],
                    'is_correct' => isset($optData['is_correct']) ? 1 : 0,
                ]);
            }
        }

        return redirect()
            ->route('admin.live-show-quizzes.index', ['live_show_id' => $validated['live_show_id']])
            ->with('success', 'Quiz created successfully!');
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
        $quiz = LiveShowQuiz::findOrFail($id);

        $request->validate([
            'live_show_id' => 'required|exists:live_shows,id',
            'question' => 'required|string|max:255',
            'options.*.option_text' => 'required|string|max:255',
            'options.*.is_correct' => 'nullable|boolean',
        ]);

        $quiz->update($request->only('live_show_id', 'question'));
        $quiz->options()->delete();

        foreach ($request->options as $option) {
            $quiz->options()->create([
                'option_text' => $option['option_text'],
                'is_correct' => $option['is_correct'] ?? false,
            ]);
        }

        return redirect()->route('admin.live-show-quizzes.index')->with('success', 'Quiz updated successfully.');
    }

    public function destroy($id)
    {
        $quiz = LiveShowQuiz::findOrFail($id);
        $quiz->delete();
        return redirect()->route('admin.live-show-quizzes.index')->with('success', 'Quiz deleted successfully.');
    }
}
