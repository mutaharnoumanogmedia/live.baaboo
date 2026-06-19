<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LiveShow;
use App\Models\UserQuizResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:can-manage-analytics');
    }

    public function index()
    {
        $liveShows = LiveShow::notTestShow()->orderBy('scheduled_at', 'desc')->get();

        $totalShows = LiveShow::notTestShow()->count();
        $completedShows = LiveShow::notTestShow()->where('status', 'completed')->count();

        $totalUniquePlayers = DB::table('user_live_shows')
            ->join('live_shows', 'live_shows.id', '=', 'user_live_shows.live_show_id')
            ->where('live_shows.is_test_show', false)
            ->distinct('user_live_shows.user_id')
            ->count('user_live_shows.user_id');

        $totalParticipants = DB::table('user_live_shows')
            ->join('live_shows', 'live_shows.id', '=', 'user_live_shows.live_show_id')
            ->where('live_shows.is_test_show', false)
            ->where(function ($q) {
                $q->where('user_live_shows.score', '>', 0)
                    ->orWhere('user_live_shows.is_online', '>', 0);
            })
            ->distinct('user_live_shows.user_id')
            ->count('user_live_shows.user_id');

        $avgParticipationRate = $totalUniquePlayers > 0
            ? round(($totalParticipants / $totalUniquePlayers) * 100, 1)
            : 0;

        $totalWinners = DB::table('user_live_shows')
            ->join('live_shows', 'live_shows.id', '=', 'user_live_shows.live_show_id')
            ->where('live_shows.is_test_show', false)
            ->where('user_live_shows.is_winner', true)
            ->count();

        $totalPrizesAwarded = DB::table('user_live_shows')
            ->join('live_shows', 'live_shows.id', '=', 'user_live_shows.live_show_id')
            ->where('live_shows.is_test_show', false)
            ->where('user_live_shows.is_winner', true)
            ->sum('user_live_shows.prize_won');

        return view('admin.analytics.index', compact(
            'liveShows',
            'totalShows',
            'completedShows',
            'totalUniquePlayers',
            'totalParticipants',
            'avgParticipationRate',
            'totalWinners',
            'totalPrizesAwarded'
        ));
    }

    public function chartData(Request $request)
    {
        $query = LiveShow::notTestShow()->orderBy('scheduled_at', 'asc');

        if ($request->filled('show_id')) {
            $query->where('id', $request->show_id);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('scheduled_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('scheduled_at', '<=', $request->end_date);
        }

        $shows = $query->get();

        $labels = [];
        $totalUsersData = [];
        $participatedData = [];
        $notParticipatedData = [];

        foreach ($shows as $show) {
            $labels[] = $show->title.' ('.($show->scheduled_at ? $show->scheduled_at->format('d M Y') : 'N/A').')';

            $totalUsers = DB::table('user_live_shows')
                ->where('live_show_id', $show->id)
                ->count();

            $participated = DB::table('user_live_shows')
                ->where('live_show_id', $show->id)
                ->where(function ($q) {
                    $q->where('score', '>', 0);
                })
                ->count();

            $notParticipated = $totalUsers - $participated;

            $totalUsersData[] = $totalUsers;
            $participatedData[] = $participated;
            $notParticipatedData[] = $notParticipated;
        }

        return response()->json([
            'labels' => $labels,
            'totalUsers' => $totalUsersData,
            'participated' => $participatedData,
            'notParticipated' => $notParticipatedData,
        ]);
    }

    public function quizAccuracyData(Request $request)
    {
        $query = LiveShow::notTestShow()
            ->whereHas('quizzes')
            ->orderBy('scheduled_at', 'asc');

        if ($request->filled('show_id')) {
            $query->where('id', $request->show_id);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('scheduled_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('scheduled_at', '<=', $request->end_date);
        }

        $shows = $query->get();

        $labels = [];
        $correctData = [];
        $incorrectData = [];
        $accuracyData = [];

        foreach ($shows as $show) {
            $quizIds = $show->quizzes()->pluck('id');

            $totalResponses = UserQuizResponse::whereIn('quiz_id', $quizIds)->count();
            $correctResponses = UserQuizResponse::whereIn('quiz_id', $quizIds)->where('is_correct', true)->count();
            $incorrectResponses = $totalResponses - $correctResponses;

            $labels[] = $show->title;
            $correctData[] = $correctResponses;
            $incorrectData[] = $incorrectResponses;
            $accuracyData[] = $totalResponses > 0 ? round(($correctResponses / $totalResponses) * 100, 1) : 0;
        }

        return response()->json([
            'labels' => $labels,
            'correct' => $correctData,
            'incorrect' => $incorrectData,
            'accuracy' => $accuracyData,
        ]);
    }

    public function responseTimeData(Request $request)
    {
        $query = LiveShow::notTestShow()
            ->whereHas('quizzes')
            ->orderBy('scheduled_at', 'asc');

        if ($request->filled('show_id')) {
            $query->where('id', $request->show_id);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('scheduled_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('scheduled_at', '<=', $request->end_date);
        }

        $shows = $query->get();

        $labels = [];
        $avgTimeData = [];

        foreach ($shows as $show) {
            $quizIds = $show->quizzes()->pluck('id');

            $avgTime = UserQuizResponse::whereIn('quiz_id', $quizIds)
                ->where('seconds_to_submit', '>', 0)
                ->avg('seconds_to_submit');

            $labels[] = $show->title;
            $avgTimeData[] = round($avgTime ?? 0, 1);
        }

        return response()->json([
            'labels' => $labels,
            'avgResponseTime' => $avgTimeData,
        ]);
    }

    public function topPerformers(Request $request)
    {
        $query = DB::table('user_live_shows')
            ->join('users', 'users.id', '=', 'user_live_shows.user_id')
            ->join('live_shows', 'live_shows.id', '=', 'user_live_shows.live_show_id')
            ->where('live_shows.is_test_show', false)
            ->select(
                'users.id',
                'users.name',
                'users.email',
                DB::raw('COUNT(DISTINCT user_live_shows.live_show_id) as shows_joined'),
                DB::raw('SUM(user_live_shows.score) as total_score'),
                DB::raw('AVG(user_live_shows.score) as avg_score'),
                DB::raw('SUM(CASE WHEN user_live_shows.is_winner = 1 THEN 1 ELSE 0 END) as wins')
            )
            ->groupBy('users.id', 'users.name', 'users.email');

        if ($request->filled('show_id')) {
            $query->where('user_live_shows.live_show_id', $request->show_id);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('live_shows.scheduled_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('live_shows.scheduled_at', '<=', $request->end_date);
        }

        $performers = $query->orderByDesc('total_score')->limit(10)->get();

        return response()->json($performers);
    }

    public function showSummary(Request $request)
    {
        $query = LiveShow::notTestShow()->orderBy('scheduled_at', 'desc');

        if ($request->filled('start_date')) {
            $query->whereDate('scheduled_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('scheduled_at', '<=', $request->end_date);
        }

        $shows = $query->get()->map(function ($show) {
            $totalUsers = DB::table('user_live_shows')->where('live_show_id', $show->id)->count();

            $participated = DB::table('user_live_shows')
                ->where('live_show_id', $show->id)
                ->where(function ($q) {
                    $q->where('score', '>', 0)
                        ->orWhere('is_online', '>', 0);
                })
                ->count();

            $winners = DB::table('user_live_shows')
                ->where('live_show_id', $show->id)
                ->where('is_winner', true)
                ->count();

            $avgScore = DB::table('user_live_shows')
                ->where('live_show_id', $show->id)
                ->where('score', '>', 0)
                ->avg('score');

            $quizIds = $show->quizzes()->pluck('id');
            $quizCount = $quizIds->count();

            $avgResponseTime = 0;
            if ($quizCount > 0) {
                $avgResponseTime = UserQuizResponse::whereIn('quiz_id', $quizIds)
                    ->where('seconds_to_submit', '>', 0)
                    ->avg('seconds_to_submit');
            }

            $duration = null;
            if ($show->start_time && $show->end_time) {
                $duration = $show->start_time->diffInMinutes($show->end_time);
            }

            return [
                'id' => $show->id,
                'title' => $show->title,
                'scheduled_at' => $show->scheduled_at ? $show->scheduled_at->format('d M Y H:i') : 'N/A',
                'status' => $show->status,
                'total_users' => $totalUsers,
                'participated' => $participated,
                'participation_rate' => $totalUsers > 0 ? round(($participated / $totalUsers) * 100, 1) : 0,
                'winners' => $winners,
                'avg_score' => round($avgScore ?? 0, 1),
                'quiz_count' => $quizCount,
                'avg_response_time' => round($avgResponseTime ?? 0, 1),
                'duration_minutes' => $duration,
            ];
        });

        return response()->json($shows);
    }
}
