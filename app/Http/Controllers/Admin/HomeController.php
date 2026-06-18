<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LiveShow;
use App\Models\LiveShowQuiz;
use App\Models\User;
use App\Models\UserLiveShow;
use App\Models\UserQuizResponse;
use App\Models\Viewer;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('permission:can-manage-dashboard');
    // }

    public function home()
    {
        $activePlayers = User::role('user')->where('is_active', 1)->count();
        $rocOfPlayersFromLastWeek = User::role('user')
            ->where('is_active', 1)
            ->whereBetween('updated_at', [now()->subWeek(), now()])
            ->count();
        // calculate percentage
        if ($rocOfPlayersFromLastWeek == 0) {
            $rocOfPlayersFromLastWeekPercentage = $activePlayers * 100;
        } else {
            $rocOfPlayersFromLastWeekPercentage = (($activePlayers - $rocOfPlayersFromLastWeek) / $rocOfPlayersFromLastWeek) * 100;
        }

        $totalViewers = Viewer::count();
        $rocOfViewersFromLastWeek = Viewer::whereBetween('created_at', [now()->subWeek(), now()])->count();
        // calculate percentage
        if ($rocOfViewersFromLastWeek == 0) {
            $rocOfViewersFromLastWeekPercentage = $totalViewers * 100;
        } else {
            $rocOfViewersFromLastWeekPercentage = (($totalViewers - $rocOfViewersFromLastWeek) / $rocOfViewersFromLastWeek) * 100;
        }

        $totalLiveQuizShows = LiveShow::count();
        $totalScheduledLiveQuizShows = LiveShow::where('status', 'scheduled')->count();

        // ---- Additional stat widgets ----
        $totalWinners = UserLiveShow::where('is_winner', 1)->count();
        $winnersThisWeek = UserLiveShow::where('is_winner', 1)
            ->whereBetween('updated_at', [now()->subWeek(), now()])
            ->count();

        $totalRegisteredUsers = User::role('user')->count();
        $newUsersThisWeek = User::role('user')
            ->whereBetween('created_at', [now()->subWeek(), now()])
            ->count();

        $totalQuizQuestions = LiveShowQuiz::count();
        $totalQuizResponses = UserQuizResponse::count();
        $correctQuizResponses = UserQuizResponse::where('is_correct', 1)->count();
        $quizAccuracy = $totalQuizResponses > 0
            ? ($correctQuizResponses / $totalQuizResponses) * 100
            : 0;

        $totalPrizePool = LiveShow::notTestShow()->sum('prize_amount');
        $liveNowShows = LiveShow::where('status', 'live')->count();
        $completedShows = LiveShow::where('status', 'completed')->count();
        $showsToday = LiveShow::whereDate('scheduled_at', today())->count();

        // ---- List widgets ----
        $upcomingShows = LiveShow::where('status', 'scheduled')
            ->orderBy('scheduled_at')
            ->withCount('quizzes')
            ->take(5)
            ->get();

        $recentShows = LiveShow::whereIn('status', ['live', 'completed'])
            ->withCount('users')
            ->orderByDesc('scheduled_at')
            ->take(5)
            ->get();

        $recentPlayers = User::role('user')
            ->latest()
            ->take(6)
            ->get();

        $topPlayers = User::role('user')
            ->join('user_quiz_responses', 'users.id', '=', 'user_quiz_responses.user_id')
            ->select('users.id', 'users.name', 'users.email')
            ->selectRaw('ROUND(SUM(user_quiz_responses.response_score), 2) as total_score')
            ->selectRaw('COUNT(user_quiz_responses.id) as total_answers')
            ->selectRaw('SUM(user_quiz_responses.is_correct) as correct_answers')
            ->groupBy('users.id', 'users.name', 'users.email')
            ->orderByDesc('total_score')
            ->take(5)
            ->get();

        // ---- Pending tasks feed ----
        $showsMissingQuizzes = LiveShow::where('status', 'scheduled')
            ->whereDoesntHave('quizzes')
            ->count();
        $showsAwaitingWinners = LiveShow::where('status', 'completed')
            ->where('winners_announced', 0)
            ->count();
        $inactivePlayers = User::role('user')->where('is_active', 0)->count();

        $pendingTasks = collect([
            [
                'label' => 'Live show(s) running right now — manage the stream',
                'count' => $liveNowShows,
                'icon' => 'bi-broadcast',
                'color' => 'danger',
                'url' => route('admin.live-shows.index'),
            ],
            [
                'label' => 'Show(s) scheduled for today — prepare the stream',
                'count' => $showsToday,
                'icon' => 'bi-calendar-event',
                'color' => 'info',
                'url' => route('admin.live-shows.index'),
            ],
            [
                'label' => 'Scheduled show(s) with no quiz questions attached',
                'count' => $showsMissingQuizzes,
                'icon' => 'bi-question-circle',
                'color' => 'warning',
                'url' => route('admin.live-show-quizzes.create'),
            ],
            [
                'label' => 'Completed show(s) with winners not announced yet',
                'count' => $showsAwaitingWinners,
                'icon' => 'bi-trophy',
                'color' => 'warning',
                'url' => route('admin.live-shows.index'),
            ],
            [
                'label' => 'Inactive player account(s) to review',
                'count' => $inactivePlayers,
                'icon' => 'bi-person-x',
                'color' => 'secondary',
                'url' => route('admin.players.index'),
            ],
        ])->filter(fn ($task) => $task['count'] > 0)->values();

        // Extract signups grouped by month for current year
        $year = Carbon::now()->year;

        $signups = User::select(
            DB::raw('MONTH(created_at) as month'),
            DB::raw('COUNT(*) as total')
        )
            ->whereYear('created_at', $year)
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Create 12-month array (fill 0 for empty months)
        $monthlyUserData = array_fill(1, 12, 0);
        foreach ($signups as $row) {
            $monthlyUserData[$row->month] = $row->total;
        }

        $visits = Viewer::select(
            DB::raw('MONTH(created_at) as month'),
            DB::raw('COUNT(*) as total')
        )
            ->whereYear('created_at', $year)
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Prepare 12-month array
        $monthlyViewerData = array_fill(1, 12, 0);
        foreach ($visits as $row) {
            $monthlyViewerData[$row->month] = $row->total;
        }

        $labels = json_encode([
            'Jan',
            'Feb',
            'Mar',
            'Apr',
            'May',
            'Jun',
            'Jul',
            'Aug',
            'Sep',
            'Oct',
            'Nov',
            'Dec',
        ]);
        $dataUsers = json_encode(array_values($monthlyUserData));
        $dataViewers = json_encode(array_values($monthlyViewerData));

        return view('admin.dashboard', compact(
            'activePlayers',
            'rocOfPlayersFromLastWeekPercentage',
            'totalViewers',
            'rocOfViewersFromLastWeekPercentage',
            'totalLiveQuizShows',
            'totalScheduledLiveQuizShows',
            'totalWinners',
            'winnersThisWeek',
            'totalRegisteredUsers',
            'newUsersThisWeek',
            'totalQuizQuestions',
            'totalQuizResponses',
            'quizAccuracy',
            'totalPrizePool',
            'liveNowShows',
            'completedShows',
            'showsToday',
            'upcomingShows',
            'recentShows',
            'recentPlayers',
            'topPlayers',
            'pendingTasks',
            'labels',
            'dataUsers',
            'dataViewers',
            'year'
        ));
    }
}
