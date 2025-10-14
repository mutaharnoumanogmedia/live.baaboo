<?php

namespace App\Services;

use App\Models\LiveShowQuiz;

use App\Models\UserQuizResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;


class LiveShowQuizService
{


    public function calculateResponseStatistics(LiveShowQuiz $quiz): Collection
    {
        // Get statistics only for options that have at least one response
        $responsesWithStats = UserQuizResponse::query()
            ->select(
                'quiz_option_id',
                DB::raw('COUNT(*) as total_response_for_option'),
                DB::raw('(COUNT(*) * 100.0 / SUM(COUNT(*)) OVER()) as percentage')
            )
            ->where('quiz_id', $quiz->id)
            ->groupBy('quiz_option_id')
            ->get()
            ->keyBy('quiz_option_id'); // Key by option ID for efficient lookup

        // Map over all of the quiz's options to build the final, complete list
        return $quiz->options->map(function ($option) use ($responsesWithStats) {

            $stats = $responsesWithStats->get($option->id);

            return (object) [
                'quiz_option_id' => $option->id,
                'option_text'    => $option->option_text, // Include option text for context
                'total_response_for_option' => $stats ? (int) $stats->total_response_for_option : 0,
                'percentage'     => $stats ? round((float) $stats->percentage, 2) : 0.0,
            ];
        });
    }
}
