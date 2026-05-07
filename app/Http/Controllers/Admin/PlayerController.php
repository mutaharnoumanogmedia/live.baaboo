<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PlayerController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:can-manage-players');
    }

    public function index()
    {
        return view('admin.players.index');
    }

    /**
     * Server-side data endpoint for the players DataTable.
     *
     * Accepts standard DataTables 1.13 server-side params (draw, start, length,
     * search[value], order[*], columns[*]) plus the custom filter inputs:
     *  - filter_registered_from / filter_registered_to (Y-m-d)
     *  - filter_last_show_from  / filter_last_show_to  (Y-m-d)
     *  - filter_has_referrer    ('1' | '0' | '')
     *  - filter_min_games       (int)
     *  - filter_min_referred    (int)
     */
    public function data(Request $request): JsonResponse
    {
        $base = User::role('user')->where('is_active', 1);

        $totalRecords = (clone $base)->count();

        $query = (clone $base)
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

        $this->applyGlobalSearch($query, (string) $request->input('search.value', ''));
        $this->applyColumnSearch($query, (array) $request->input('columns', []));
        $this->applyCustomFilters($query, $request);

        $filteredRecords = (clone $query)->count();

        $this->applyOrdering($query, $request);

        $start = max(0, (int) $request->input('start', 0));
        $length = (int) $request->input('length', 20);
        if ($length > 0) {
            $query->offset($start)->limit($length);
        }

        $players = $query->get();

        $data = $players->map(fn ($p) => $this->transformPlayer($p))->all();

        return response()->json([
            'draw' => (int) $request->input('draw'),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data,
        ]);
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

    private function applyColumnSearch($query, array $columns): void
    {
        foreach ($columns as $column) {
            $value = trim((string) ($column['search']['value'] ?? ''));
            $name = $column['data'] ?? null;

            if ($value === '' || ! $name) {
                continue;
            }

            switch ($name) {
                case 'id':
                    if (is_numeric($value)) {
                        $query->where('users.id', (int) $value);
                    }
                    break;
                case 'name':
                    $query->where('users.name', 'like', "%{$value}%");
                    break;
                case 'email':
                    $query->where('users.email', 'like', "%{$value}%");
                    break;
                case 'user_name':
                    $query->where('users.user_name', 'like', "%{$value}%");
                    break;
                case 'referred_by_username':
                    $query->whereHas('referredBy', function ($q) use ($value) {
                        $q->where('user_name', 'like', "%{$value}%")
                            ->orWhere('name', 'like', "%{$value}%");
                    });
                    break;
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

        $columnIndex = (int) $request->input('order.0.column', 0);
        $dir = $request->input('order.0.dir') === 'asc' ? 'asc' : 'desc';
        $columnName = $request->input("columns.$columnIndex.data");

        if (isset($orderable[$columnName])) {
            $query->orderBy($orderable[$columnName], $dir);
        } else {
            $query->orderByDesc('users.id');
        }
    }

    private function transformPlayer(User $player): array
    {
        $referredByLabel = '-';
        if ($player->referredBy) {
            $referredByLabel = $player->referredBy->user_name
                ?: ($player->referredBy->name ?: '#'.$player->referredBy->id);
        }

        $lastPlayed = $player->last_game_played_at
            ? Carbon::parse($player->last_game_played_at)->format('Y-m-d H:i')
            : 'Never';

        return [
            'id' => $player->id,
            'name' => e($player->name ?? '-'),
            'email' => e($player->email),
            'user_name' => e($player->user_name ?? '-'),
            'created_at' => optional($player->created_at)->format('Y-m-d H:i') ?? '-',
            'live_games_played' => (int) $player->live_games_played,
            'last_game_played_at' => $lastPlayed,
            'referred_users_count' => (int) $player->referred_users_count,
            'referred_by_username' => e($referredByLabel),
            'actions' => $this->renderActions($player),
        ];
    }

    private function renderActions(User $player): string
    {
        $url = e(route('admin.players.show', $player->id));

        return <<<HTML
            <div class="dropdown">
                <button class="btn btn-secondary btn-sm dropdown-toggle" type="button"
                    data-bs-toggle="dropdown" aria-expanded="false">
                    Actions
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="{$url}">View Player</a></li>
                </ul>
            </div>
            HTML;
    }
}
