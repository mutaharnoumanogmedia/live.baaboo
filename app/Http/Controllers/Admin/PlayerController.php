<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LiveShowWinnerPrize;
use App\Models\User;
use App\Models\UserQuizResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PlayerController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:can-manage-players');
    }

    /**
     * Server-rendered, paginated players list.
     *
     * Recognised query string params:
     *  - q                          (string)  global search (name/email/username)
     *  - id, name, email, user_name (string)  per-column search inputs
     *  - referred_by_username       (string)  per-column search on referrer
     *  - sort                       (string)  one of the orderable columns
     *  - direction                  (asc|desc)
     *  - per_page                   (25|50|100)
     *  - filter_registered_from / filter_registered_to  (Y-m-d)
     *  - filter_last_show_from  / filter_last_show_to   (Y-m-d)
     *  - filter_has_referrer        ('1' | '0' | '')
     *  - filter_min_games           (int)
     *  - filter_min_referred        (int)
     *  - page                       (int)     paginator page
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 50);
        if (! in_array($perPage, [25, 50, 100], true)) {
            $perPage = 50;
        }

        $liveShowStats = DB::table('user_live_shows')
            ->select('user_id')
            ->selectRaw('COUNT(*) as live_games_played')
            ->selectRaw('MAX(created_at) as last_game_played_at')
            ->groupBy('user_id');

        $referralStats = DB::table('users')
            ->select('referred_by')
            ->selectRaw('COUNT(*) as referred_users_count')
            ->whereNotNull('referred_by')
            ->groupBy('referred_by');

        $query = User::role('user')
            ->where('users.is_active', 1)
            ->leftJoinSub($liveShowStats, 'live_show_stats', 'users.id', '=', 'live_show_stats.user_id')
            ->leftJoinSub($referralStats, 'referral_stats', 'users.id', '=', 'referral_stats.referred_by')
            ->select('users.*')
            ->selectRaw('COALESCE(live_show_stats.live_games_played, 0) as live_games_played')
            ->selectRaw('live_show_stats.last_game_played_at')
            ->selectRaw('COALESCE(referral_stats.referred_users_count, 0) as referred_users_count')
            ->with(['referredBy:id,name,user_name']);

        $this->applyGlobalSearch($query, (string) $request->input('q', ''));
        $this->applyColumnSearch($query, $request);
        $this->applyCustomFilters($query, $request);
        $this->applyOrdering($query, $request);

        $players = $query->paginate($perPage)->withQueryString();

        return view('admin.players.index', compact('players', 'perPage'));
    }

    public function show($id)
    {
        $player = User::with([
            'referredBy:id,name,user_name,email,created_at,is_affiliate',
            'referredUsers' => function ($q) {
                $q->orderByDesc('created_at');
            },
        ])->findOrFail($id);

        // Load participation with all pivot fields we want to display.
        $player->setRelation(
            'liveShows',
            $player->liveShows()
                ->withPivot([
                    'score', 'status', 'created_at', 'prize_won', 'game_joined_at',
                    'is_winner', 'is_online', 'winner_prize_id', 'discount_code',
                ])
                ->orderByPivot('created_at', 'desc')
                ->get()
        );

        // Per-show quiz performance for this player.
        $quizStats = UserQuizResponse::where('user_quiz_responses.user_id', $player->id)
            ->join('live_show_quizzes', 'user_quiz_responses.quiz_id', '=', 'live_show_quizzes.id')
            ->select('live_show_quizzes.live_show_id')
            ->selectRaw('COUNT(*) as total_answers')
            ->selectRaw('SUM(user_quiz_responses.is_correct) as correct_answers')
            ->selectRaw('ROUND(AVG(user_quiz_responses.seconds_to_submit), 2) as avg_response_time')
            ->selectRaw('ROUND(SUM(user_quiz_responses.response_score), 2) as total_score')
            ->groupBy('live_show_quizzes.live_show_id')
            ->get()
            ->keyBy('live_show_id');

        // Load LiveShowWinnerPrize records for every prize this player claimed.
        $winnerPrizeIds = $player->liveShows
            ->filter(fn ($s) => $s->pivot->is_winner && $s->pivot->winner_prize_id)
            ->pluck('pivot.winner_prize_id')
            ->filter()
            ->unique();

        $winnerPrizes = LiveShowWinnerPrize::whereIn('id', $winnerPrizeIds)->get()->keyBy('id');

        // Aggregate stats. Score comes from the UserLiveShow accessor (SUM of
        // response_score), which is the single source of truth.
        $totalGames = $player->liveShows->count();
        $gamesWon = 0;
        $maxScore = 0.0;
        $totalScoreSum = 0.0;
        $totalPrizeMoney = 0.0;
        $totalAnswers = 0;
        $totalCorrect = 0;

        foreach ($player->liveShows as $show) {
            $pivot = $show->pivot;
            $score = (float) ($pivot->score ?? 0);
            $totalScoreSum += $score;

            if ($score > $maxScore) {
                $maxScore = $score;
            }

            $qs = $quizStats->get($show->id);
            if ($qs) {
                $totalAnswers += (int) $qs->total_answers;
                $totalCorrect += (int) $qs->correct_answers;
            }

            if ($pivot->is_winner) {
                $gamesWon++;
                $prizeWon = $pivot->prize_won;
                // prize_won is stored as a percent string (e.g. "50.00" = 50%)
                // applied to live_shows.prize_amount. Skip non-numeric values
                // such as the legacy default "n/a".
                if (is_numeric($prizeWon) && $show->prize_amount) {
                    $totalPrizeMoney += ((float) $show->prize_amount) * ((float) $prizeWon / 100);
                }
            }
        }

        $stats = [
            'total_games'      => $totalGames,
            'games_won'        => $gamesWon,
            'max_score'        => $maxScore,
            'avg_score'        => $totalGames > 0 ? $totalScoreSum / $totalGames : 0,
            'total_prize_money'=> $totalPrizeMoney,
            'referred_count'   => $player->referredUsers->count(),
            'total_answers'    => $totalAnswers,
            'total_correct'    => $totalCorrect,
            'accuracy'         => $totalAnswers > 0 ? ($totalCorrect / $totalAnswers) * 100 : 0,
            'win_rate'         => $totalGames > 0 ? ($gamesWon / $totalGames) * 100 : 0,
        ];

        return view('admin.players.show', compact('player', 'stats', 'quizStats', 'winnerPrizes'));
    }

    public function winners()
    {
        $winners = User::whereIn('id', function ($query) {
            $query->select('user_id')
                ->from('user_quizzes')
                ->where('score_percentage', '=', 100);
        })->with('liveShows')->get();

        return view('admin.players.winners', compact('winners'));
    }

    private function applyGlobalSearch($query, string $search): void
    {
        $search = trim($search);
        if ($search === '') {
            return;
        }

        $query->where(function ($q) use ($search) {
            $q->where('users.name', 'like', "%{$search}%")
                ->orWhere('users.email', 'like', "%{$search}%")
                ->orWhere('users.user_name', 'like', "%{$search}%");
        });
    }

    private function applyColumnSearch($query, Request $request): void
    {
        $columnFilters = [
            'id' => function ($q, $v) {
                if (is_numeric($v)) {
                    $q->where('users.id', (int) $v);
                }
            },
            'name' => fn ($q, $v) => $q->where('users.name', 'like', "%{$v}%"),
            'email' => fn ($q, $v) => $q->where('users.email', 'like', "%{$v}%"),
            'user_name' => fn ($q, $v) => $q->where('users.user_name', 'like', "%{$v}%"),
            'referred_by_username' => fn ($q, $v) => $q->whereHas('referredBy', function ($sub) use ($v) {
                $sub->where('user_name', 'like', "%{$v}%")
                    ->orWhere('name', 'like', "%{$v}%");
            }),
        ];

        foreach ($columnFilters as $field => $apply) {
            $value = trim((string) $request->input($field, ''));
            if ($value !== '') {
                $apply($query, $value);
            }
        }
    }

    private function applyCustomFilters($query, Request $request): void
    {
        if ($v = $request->input('filter_registered_from')) {
            $query->whereDate('users.created_at', '>=', $v);
        }
        if ($v = $request->input('filter_registered_to')) {
            $query->whereDate('users.created_at', '<=', $v);
        }

        $hasReferrer = $request->input('filter_has_referrer');
        if ($hasReferrer === '1') {
            $query->whereNotNull('users.referred_by');
        } elseif ($hasReferrer === '0') {
            $query->whereNull('users.referred_by');
        }

        if (is_numeric($request->input('filter_min_referred'))) {
            $query->whereRaw('COALESCE(referral_stats.referred_users_count, 0) >= ?', [
                (int) $request->input('filter_min_referred'),
            ]);
        }

        if (is_numeric($request->input('filter_min_games'))) {
            $query->whereRaw('COALESCE(live_show_stats.live_games_played, 0) >= ?', [
                (int) $request->input('filter_min_games'),
            ]);
        }

        if ($v = $request->input('filter_last_show_from')) {
            $query->whereDate('live_show_stats.last_game_played_at', '>=', $v);
        }
        if ($v = $request->input('filter_last_show_to')) {
            $query->whereDate('live_show_stats.last_game_played_at', '<=', $v);
        }
    }

    private function applyOrdering($query, Request $request): void
    {
        $orderable = [
            'id' => 'users.id',
            'name' => 'users.name',
            'email' => 'users.email',
            'user_name' => 'users.user_name',
            'created_at' => 'users.created_at',
            'live_games_played' => 'live_games_played',
            'last_game_played_at' => 'last_game_played_at',
            'referred_users_count' => 'referred_users_count',
        ];

        $sort = $request->input('sort', 'id');
        $dir = $request->input('direction') === 'asc' ? 'asc' : 'desc';

        if (isset($orderable[$sort])) {
            $query->orderBy($orderable[$sort], $dir);
        } else {
            $query->orderByDesc('users.id');
        }
    }
}
