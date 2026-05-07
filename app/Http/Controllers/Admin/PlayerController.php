<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
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
     *  - filter_registered_from / filter_registered_to  (Y-m-d)
     *  - filter_last_show_from  / filter_last_show_to   (Y-m-d)
     *  - filter_has_referrer        ('1' | '0' | '')
     *  - filter_min_games           (int)
     *  - filter_min_referred        (int)
     *  - page                       (int)     paginator page
     */
    public function index(Request $request)
    {
        $query = User::role('user')->where('is_active', 1)
            ->select('users.*')
            ->withCount([
                'liveShows as live_games_played',
                'referredUsers as referred_users_count',
            ])
            ->with(['referredBy:id,name,user_name'])
            ->addSelect([
                'last_game_played_at' => DB::table('user_live_shows')
                    ->select('created_at')
                    ->whereColumn('user_id', 'users.id')
                    ->orderByDesc('created_at')
                    ->limit(1),
            ]);

        $this->applyGlobalSearch($query, (string) $request->input('q', ''));
        $this->applyColumnSearch($query, $request);
        $this->applyCustomFilters($query, $request);
        $this->applyOrdering($query, $request);

        // 100 records per page, links keep all current filters/sort.
        $players = $query->paginate(50)->withQueryString();

        return view('admin.players.index', compact('players'));
    }

    public function show($id)
    {
        $player = User::with([
            'referredBy:id,name,user_name,email,created_at',
            'referredUsers' => function ($q) {
                $q->orderByDesc('created_at');
            },
        ])->findOrFail($id);

        // Order participation by most-recently joined first
        $player->setRelation(
            'liveShows',
            $player->liveShows()
                ->orderByPivot('created_at', 'desc')
                ->get()
        );

        // Aggregate stats. Score is computed via UserLiveShow accessor (live SUM
        // from user_quiz_responses), so we iterate to honour that single source
        // of truth instead of reading a possibly-stale pivot column.
        $totalGames = $player->liveShows->count();
        $gamesWon = 0;
        $maxScore = 0.0;
        $totalPrizeMoney = 0.0;

        foreach ($player->liveShows as $show) {
            $pivot = $show->pivot;
            $score = (float) ($pivot->score ?? 0);
            if ($score > $maxScore) {
                $maxScore = $score;
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
            'total_games' => $totalGames,
            'games_won' => $gamesWon,
            'max_score' => $maxScore,
            'total_prize_money' => $totalPrizeMoney,
            'referred_count' => $player->referredUsers->count(),
        ];

        return view('admin.players.show', compact('player', 'stats'));
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
            $query->has('referredUsers', '>=', (int) $request->input('filter_min_referred'));
        }

        if (is_numeric($request->input('filter_min_games'))) {
            $query->has('liveShows', '>=', (int) $request->input('filter_min_games'));
        }

        if ($v = $request->input('filter_last_show_from')) {
            $query->whereHas('liveShows', function ($q) use ($v) {
                $q->whereDate('user_live_shows.created_at', '>=', $v);
            });
        }
        if ($v = $request->input('filter_last_show_to')) {
            $query->whereHas('liveShows', function ($q) use ($v) {
                $q->whereDate('user_live_shows.created_at', '<=', $v);
            });
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
