<?php

namespace App\Services;

use App\Models\LiveShow;
use App\Models\LiveShowQuiz;
use App\Models\UserQuizResponse;
use App\Models\UserSpecialQuizResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

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
            ->whereNotNull('quiz_option_id')
            ->groupBy('quiz_option_id')
            ->get()
            ->keyBy('quiz_option_id'); // Key by option ID for efficient lookup

        // Map over all of the quiz's options to build the final, complete list
        return $quiz->options->map(function ($option) use ($responsesWithStats) {

            $stats = $responsesWithStats->get($option->id);

            return (object) [
                'quiz_option_id' => $option->id,
                'option_text' => $option->option_text, // Include option text for context
                'total_response_for_option' => $stats ? (int) $stats->total_response_for_option : 0,
                'percentage' => $stats ? round((float) $stats->percentage, 2) : 0.0,
            ];
        });
    }

    /**
     * Get sorted players for a live show, with pivot data.
     */
    public function getSortedPlayers(LiveShow $liveShow): Collection
    {
        return $liveShow->users()
            ->withPivot(['score', 'status', 'is_winner', 'prize_won', 'is_online', 'created_at', 'game_joined_at'])
            ->get()
            ->when(
                $liveShow->winners_announced,
                fn ($collection) => $collection->sortByDesc(fn ($user) => $user->pivot->score),
                fn ($collection) => $collection->sortBy(fn ($user) => $user->pivot->created_at)
            )

            ->values();
    }

    public function getSortedByScore(LiveShow $liveShow): Collection
    {
        return $liveShow->users()
            ->withPivot(['score', 'status', 'is_winner', 'prize_won', 'is_online', 'created_at', 'game_joined_at'])
            ->get()
            ->sortByDesc(fn ($user) => $user->pivot->score)
            ->values();
    }

    /**
     * Players sorted by their Special Quiz score (independent ranking).
     */
    public function getSortedSpecialPlayers(LiveShow $liveShow): Collection
    {
        return $liveShow->users()
            ->withPivot([
                'special_score', 'status', 'is_online', 'is_special_winner', 'special_prize_won', 'created_at',
                'special_gift_id', 'special_discount_code', 'game_joined_at',
                'special_winner_email_sent_status', 'special_winner_email_sent_at',
                'special_type_email_sent_status', 'special_type_email_sent_at',
            ])
            ->get()
            ->sortByDesc(fn ($user) => $user->pivot->special_score)
            ->values();
    }

    /**
     * Per-option response statistics for a Special Quiz question, computed from
     * the dedicated special responses table.
     */
    public function calculateSpecialResponseStatistics(LiveShowQuiz $quiz): Collection
    {
        $responsesWithStats = UserSpecialQuizResponse::query()
            ->select(
                'quiz_option_id',
                DB::raw('COUNT(*) as total_response_for_option'),
                DB::raw('(COUNT(*) * 100.0 / SUM(COUNT(*)) OVER()) as percentage')
            )
            ->where('quiz_id', $quiz->id)
            ->whereNotNull('quiz_option_id')
            ->groupBy('quiz_option_id')
            ->get()
            ->keyBy('quiz_option_id');

        return $quiz->options->map(function ($option) use ($responsesWithStats) {
            $stats = $responsesWithStats->get($option->id);

            return (object) [
                'quiz_option_id' => $option->id,
                'option_text' => $option->option_text,
                'total_response_for_option' => $stats ? (int) $stats->total_response_for_option : 0,
                'percentage' => $stats ? round((float) $stats->percentage, 2) : 0.0,
            ];
        });
    }
}
