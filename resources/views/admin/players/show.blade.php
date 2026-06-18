<x-app-dashboard-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-0">
                {{ __('Player Details') }}
                <small class="text-muted">— {{ $player->name ?? $player->email }}</small>
            </h2>
            <a href="{{ route('admin.players.index') }}" class="btn btn-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Back to Players
            </a>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="container-fluid">

            {{-- Row 1 KPIs: participation --}}
            <div class="row g-3 mb-3" id="kpiTiles">
                <div class="col-xl-3 col-md-6 col-sm-6">
                    <div class="card text-bg-primary h-100 border-0 shadow-sm">
                        <div class="card-body d-flex align-items-center">
                            <i class="bi bi-controller fs-1 me-3"></i>
                            <div>
                                <div class="text-uppercase small">Games Played</div>
                                <h3 class="mb-0">{{ $stats['total_games'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 col-sm-6">
                    <div class="card text-bg-success h-100 border-0 shadow-sm">
                        <div class="card-body d-flex align-items-center">
                            <i class="bi bi-trophy-fill fs-1 me-3"></i>
                            <div>
                                <div class="text-uppercase small">Games Won</div>
                                <h3 class="mb-0">{{ $stats['games_won'] }}</h3>
                                <small class="opacity-75">
                                    {{ number_format($stats['win_rate'], 1) }}% win rate
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 col-sm-6">
                    <div class="card text-bg-warning h-100 border-0 shadow-sm">
                        <div class="card-body d-flex align-items-center">
                            <i class="bi bi-bar-chart-fill fs-1 me-3"></i>
                            <div>
                                <div class="text-uppercase small">Best Score</div>
                                <h3 class="mb-0">{{ number_format($stats['max_score'], 2) }}</h3>
                                <small class="opacity-75">avg {{ number_format($stats['avg_score'], 2) }} per game</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 col-sm-6">
                    <div class="card text-bg-info h-100 border-0 shadow-sm">
                        <div class="card-body d-flex align-items-center">
                            <i class="bi bi-cash-coin fs-1 me-3"></i>
                            <div>
                                <div class="text-uppercase small">Total Prize Won</div>
                                <h3 class="mb-0">{{ number_format($stats['total_prize_money'], 2) }}</h3>
                                <small class="opacity-75">across {{ $stats['games_won'] }} winning shows</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Row 2 KPIs: quiz performance --}}
            <div class="row g-3 mb-4">
                <div class="col-xl-3 col-md-6 col-sm-6">
                    <div class="card h-100 border-0 shadow-sm" style="background:#2c3e50;color:#fff;">
                        <div class="card-body d-flex align-items-center">
                            <i class="bi bi-question-circle-fill fs-1 me-3"></i>
                            <div>
                                <div class="text-uppercase small">Questions Answered</div>
                                <h3 class="mb-0">{{ number_format($stats['total_answers']) }}</h3>
                                <small class="opacity-75">{{ number_format($stats['total_correct']) }} correct</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 col-sm-6">
                    <div class="card h-100 border-0 shadow-sm" style="background:#27ae60;color:#fff;">
                        <div class="card-body d-flex align-items-center">
                            <i class="bi bi-bullseye fs-1 me-3 text-white"></i>
                            <div>
                                <div class="text-uppercase small text-white">Overall Accuracy</div>
                                <h3 class="mb-0 text-white">{{ number_format($stats['accuracy'], 1) }}%</h3>
                                <small class="text-white">correct answers ratio</small>
                            </div>  
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 col-sm-6">
                    <div class="card h-100 border-0 shadow-sm" style="background:#8e44ad;color:#fff;">
                        <div class="card-body d-flex align-items-center">
                            <i class="bi bi-people-fill fs-1 me-3"></i>
                            <div>
                                <div class="text-uppercase small text-white">Players Referred</div>
                                <h3 class="mb-0">{{ $stats['referred_count'] }}</h3>
                                <small class="text-white">total referrals</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 col-sm-6">
                    <div class="card h-100 border-0 shadow-sm" style="background:#c0392b;color:#fff;">
                        <div class="card-body d-flex align-items-center">
                            <i class="bi bi-gift-fill fs-1 me-3"></i>
                            <div>
                                <div class="text-uppercase small">Vouchers / Codes</div>
                                @php
                                    $vouchersCount = $player->liveShows
                                        ->filter(fn($s) => $s->pivot->is_winner && !empty($s->pivot->discount_code))
                                        ->count();
                                @endphp
                                <h3 class="mb-0">{{ $vouchersCount }}</h3>
                                <small class="opacity-75">discount codes earned</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Profile row --}}
            <div class="row g-3 mb-4">
                {{-- Player Information --}}
                <div class="col-lg-6">
                    <div class="card h-100 shadow-sm">
                        <div class="card-header bg-dark text-light">
                            <h5 class="mb-0"><i class="bi bi-person-badge me-2"></i>Player Information</h5>
                        </div>
                        <div class="card-body bg-dark text-light">
                            <table class="table table-borderless table-dark align-middle mb-0">
                                <tbody>
                                    <tr>
                                        <th style="width:40%">ID</th>
                                        <td>{{ $player->id }}</td>
                                    </tr>
                                    <tr>
                                        <th>Name</th>
                                        <td>{{ $player->name ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Username</th>
                                        <td>{{ $player->user_name ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Email</th>
                                        <td>
                                            {{ $player->email }}
                                            @if ($player->email_verified_at)
                                                <span class="badge bg-success ms-1">Verified</span>
                                            @else
                                                <span class="badge bg-secondary ms-1">Unverified</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Role</th>
                                        <td>{{ $player->role ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Active</th>
                                        <td>
                                            @if ($player->is_active)
                                                <span class="badge bg-success">Yes</span>
                                            @else
                                                <span class="badge bg-secondary">No</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Affiliate</th>
                                        <td>
                                            @if ($player->is_affiliate)
                                                <span class="badge bg-info">Yes</span>
                                            @else
                                                <span class="badge bg-secondary">No</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Registered At</th>
                                        <td>{{ optional($player->created_at)->format('Y-m-d H:i') ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Agreed Terms</th>
                                        <td>{{ $player->agree_for_terms ? 'Yes' : 'No' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Agreed Email</th>
                                        <td>{{ $player->agree_for_email ? 'Yes' : 'No' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Referral Link</th>
                                        <td class="text-break">
                                            <code class="text-warning">{{ $player->referralLink() }}</code>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Referred By + Referred Users --}}
                <div class="col-lg-6 d-flex flex-column gap-3">
                    {{-- Referred By --}}
                    <div class="card shadow-sm">
                        <div class="card-header bg-dark text-light">
                            <h5 class="mb-0"><i class="bi bi-person-arms-up me-2"></i>Referred By</h5>
                        </div>
                        <div class="card-body bg-dark text-light">
                            @if ($player->referredBy)
                                <table class="table table-borderless table-dark align-middle mb-0">
                                    <tbody>
                                        <tr>
                                            <th style="width:40%">ID</th>
                                            <td>{{ $player->referredBy->id }}</td>
                                        </tr>
                                        <tr>
                                            <th>Name</th>
                                            <td>
                                                <a href="{{ route('admin.players.show', $player->referredBy->id) }}"
                                                    class="text-warning text-decoration-none">
                                                    {{ $player->referredBy->name ?? '-' }}
                                                </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Username</th>
                                            <td>{{ $player->referredBy->user_name ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Email</th>
                                            <td>{{ $player->referredBy->email }}</td>
                                        </tr>
                                        <tr>
                                            <th>Joined At</th>
                                            <td>{{ optional($player->referredBy->created_at)->format('Y-m-d H:i') ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Affiliate</th>
                                            <td>
                                                @if ($player->referredBy->is_affiliate)
                                                    <span class="badge bg-info">Yes</span>
                                                @else
                                                    <span class="badge bg-secondary">No</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Referral Link</th>
                                            <td class="text-break">
                                                <code class="text-warning">{{ $player->referredBy->referralLink() }}</code>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            @else
                                <p class="text-muted mb-0">
                                    <i class="bi bi-info-circle me-1"></i>
                                    This player was not referred by anyone.
                                </p>
                            @endif
                        </div>
                    </div>

                    {{-- Referred Users --}}
                    <div class="card shadow-sm flex-grow-1">
                        <div class="card-header bg-dark text-light d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-people-fill me-2"></i>Referred Users</h5>
                            <span class="badge bg-primary">{{ $stats['referred_count'] }}</span>
                        </div>
                        <div class="card-body bg-dark text-light p-0">
                            @if ($player->referredUsers->isEmpty())
                                <p class="text-muted mb-0 p-3">
                                    <i class="bi bi-info-circle me-1"></i>
                                    This player has not referred anyone yet.
                                </p>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-striped table-borderless table-dark align-middle mb-0 w-100">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Username</th>
                                                <th>Email</th>
                                                <th>Joined At</th>
                                                <th class="text-end">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($player->referredUsers as $ref)
                                                <tr>
                                                    <td>{{ $ref->name ?? '-' }}</td>
                                                    <td>{{ $ref->user_name ?? '-' }}</td>
                                                    <td>{{ $ref->email }}</td>
                                                    <td>{{ optional($ref->created_at)->format('Y-m-d H:i') ?? '-' }}</td>
                                                    <td class="text-end">
                                                        <a href="{{ route('admin.players.show', $ref->id) }}"
                                                            class="btn btn-sm btn-outline-light">
                                                            View
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Prizes Won --}}
            @if ($stats['games_won'] > 0)
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-dark text-light d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-trophy-fill text-warning me-2"></i>Prizes Won</h5>
                        <span class="badge bg-warning text-dark">{{ $stats['games_won'] }} win{{ $stats['games_won'] > 1 ? 's' : '' }}</span>
                    </div>
                    <div class="card-body bg-dark text-light p-0">
                        <div class="table-responsive">
                            <table class="table table-borderless table-dark align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Show</th>
                                        <th>Date</th>
                                        <th class="text-center">Rank</th>
                                        <th>Prize</th>
                                        <th>Prize %</th>
                                        <th>Cash Value</th>
                                        <th>Voucher</th>
                                        <th>Discount Code</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($player->liveShows->filter(fn($s) => $s->pivot->is_winner) as $show)
                                        @php
                                            $pivot      = $show->pivot;
                                            $prizeWon   = $pivot->prize_won;
                                            $hasNumeric = is_numeric($prizeWon);
                                            $cashValue  = $hasNumeric && $show->prize_amount
                                                ? ((float) $show->prize_amount) * ((float) $prizeWon / 100)
                                                : null;
                                            $wp = $winnerPrizes->get($pivot->winner_prize_id);
                                        @endphp
                                        <tr>
                                            <td>
                                                <a class="text-warning text-decoration-none fw-semibold"
                                                    href="{{ route('admin.live-shows.view-details', $show->id) }}">
                                                    {{ \Illuminate\Support\Str::limit($show->title ?? 'N/A', 35) }}
                                                </a>
                                                @if ($show->is_test_show)
                                                    <span class="badge bg-secondary ms-1">Test</span>
                                                @endif
                                            </td>
                                            <td class="text-nowrap">
                                                {{ optional($show->scheduled_at)->format('d M Y') ?? '-' }}
                                            </td>
                                            <td class="text-center">
                                                @if ($wp)
                                                    @if ($wp->rank === 1)
                                                        <i class="bi bi-trophy-fill text-warning"></i>
                                                    @elseif ($wp->rank === 2)
                                                        <i class="bi bi-trophy-fill text-secondary"></i>
                                                    @elseif ($wp->rank === 3)
                                                        <i class="bi bi-trophy-fill" style="color:#cd7f32"></i>
                                                    @else
                                                        <span class="badge bg-secondary">#{{ $wp->rank }}</span>
                                                    @endif
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($wp)
                                                    <span class="badge bg-{{ $wp->is_voucher ? 'info' : 'success' }}">
                                                        {{ $wp->is_voucher ? 'Voucher' : 'Cash' }}
                                                    </span>
                                                    <div class="small text-muted">{{ $wp->prize }}</div>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($hasNumeric && $prizeWon !== 'n/a')
                                                    {{ rtrim(rtrim(number_format((float) $prizeWon, 2), '0'), '.') }}%
                                                @else
                                                    <span class="text-muted">{{ $prizeWon ?? '—' }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($cashValue !== null)
                                                    <strong>{{ number_format($cashValue, 2) }}</strong>
                                                    <small class="text-muted">{{ $show->currency }}</small>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($wp && $wp->is_voucher)
                                                    <span class="badge bg-info">
                                                        {{ number_format($wp->voucher_amount, 2) }} {{ $show->currency }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if (!empty($pivot->discount_code))
                                                    <code class="text-warning">{{ $pivot->discount_code }}</code>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                <a href="{{ route('admin.live-shows.player-responses', [$show->id, $player->id]) }}"
                                                    class="btn btn-sm btn-outline-light"
                                                    title="View quiz responses">
                                                    <i class="bi bi-list-check"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Game Show Participation --}}
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-controller me-2"></i>Game Show Participation</h5>
                    <span class="badge bg-primary">{{ $stats['total_games'] }} show{{ $stats['total_games'] !== 1 ? 's' : '' }}</span>
                </div>
                <div class="card-body bg-dark text-light p-0">
                    @if ($player->liveShows->isEmpty())
                        <p class="text-muted mb-0 p-3">
                            <i class="bi bi-info-circle me-1"></i>
                            No participation records found.
                        </p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-striped table-borderless table-dark align-middle mb-0 w-100">
                                <thead>
                                    <tr>
                                        <th>Live Show</th>
                                        <th class="text-nowrap">Scheduled</th>
                                        <th>Show Status</th>
                                        <th class="text-nowrap">Joined At</th>
                                        <th>Player Status</th>
                                        <th class="text-center">Online</th>
                                        <th class="text-center">Score</th>
                                        <th class="text-center">Correct / Total</th>
                                        <th class="text-center">Avg. Time (s)</th>
                                        <th class="text-center">Won?</th>
                                        <th>Prize Won</th>
                                        <th>Show Pool</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($player->liveShows as $show)
                                        @php
                                            $pivot      = $show->pivot;
                                            $score      = (float) ($pivot->score ?? 0);
                                            $isWinner   = (bool) $pivot->is_winner;
                                            $prizeWon   = $pivot->prize_won;
                                            $hasNumeric = $isWinner && is_numeric($prizeWon);
                                            $cashPrize  = $hasNumeric
                                                ? ((float) $show->prize_amount) * ((float) $prizeWon / 100)
                                                : null;
                                            $qs         = $quizStats->get($show->id);
                                            $wp         = $winnerPrizes->get($pivot->winner_prize_id);
                                        @endphp
                                        <tr>
                                            <td>
                                                <a class="text-warning text-decoration-none"
                                                    href="{{ route('admin.live-shows.view-details', $show->id) }}">
                                                    {{ \Illuminate\Support\Str::limit($show->title ?? 'N/A', 30) }}
                                                </a>
                                                @if ($show->is_test_show)
                                                    <span class="badge bg-secondary ms-1">Test</span>
                                                @endif
                                            </td>
                                            <td class="text-nowrap">
                                                {{ optional($show->scheduled_at)->format('d M Y H:i') ?? '-' }}
                                            </td>
                                            <td>
                                                @switch($show->status)
                                                    @case('completed')
                                                        <span class="badge bg-success">Completed</span>
                                                    @break
                                                    @case('live')
                                                        <span class="badge bg-danger">Live</span>
                                                    @break
                                                    @case('scheduled')
                                                        <span class="badge bg-secondary">Scheduled</span>
                                                    @break
                                                    @default
                                                        <span class="badge bg-light text-dark">{{ ucfirst($show->status ?? '-') }}</span>
                                                @endswitch
                                            </td>
                                            <td class="text-nowrap">
                                                {{ optional($pivot->created_at)->format('d M Y H:i') ?? '-' }}
                                            </td>
                                            <td>
                                                @if (!empty($pivot->status))
                                                    <span class="badge bg-info">{{ ucfirst($pivot->status) }}</span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if ($pivot->is_online)
                                                    <span class="badge bg-success">Yes</span>
                                                @else
                                                    <span class="badge bg-secondary">No</span>
                                                @endif
                                            </td>
                                            <td class="text-center fw-semibold">
                                                {{ number_format($score, 2) }}
                                            </td>
                                            <td class="text-center">
                                                @if ($qs)
                                                    <span class="text-success">{{ $qs->correct_answers }}</span>
                                                    <span class="text-muted">/</span>
                                                    <span>{{ $qs->total_answers }}</span>
                                                    @if ($qs->total_answers > 0)
                                                        <div class="small text-muted">
                                                            {{ number_format(($qs->correct_answers / $qs->total_answers) * 100, 0) }}%
                                                        </div>
                                                    @endif
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if ($qs && $qs->avg_response_time !== null)
                                                    {{ $qs->avg_response_time }}s
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if ($isWinner)
                                                    <span class="badge bg-warning text-dark">
                                                        <i class="bi bi-trophy-fill"></i>
                                                        @if ($wp) #{{ $wp->rank }} @endif
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary">No</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($isWinner && !empty($prizeWon) && $prizeWon !== 'n/a')
                                                    @if ($hasNumeric)
                                                        <strong>{{ rtrim(rtrim(number_format((float) $prizeWon, 2), '0'), '.') }}%</strong>
                                                        <div class="small text-muted">
                                                            ≈ {{ number_format($cashPrize, 2) }} {{ $show->currency }}
                                                        </div>
                                                    @else
                                                        <strong>{{ $prizeWon }}</strong>
                                                    @endif
                                                    @if (!empty($pivot->discount_code))
                                                        <div class="small">
                                                            <code class="text-warning">{{ $pivot->discount_code }}</code>
                                                        </div>
                                                    @endif
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td>
                                                {{ number_format((float) $show->prize_amount, 2) }}
                                                <small class="text-muted">{{ $show->currency }}</small>
                                            </td>
                                            <td class="text-end">
                                                <a href="{{ route('admin.live-shows.player-responses', [$show->id, $player->id]) }}"
                                                    class="btn btn-sm btn-outline-light"
                                                    title="View quiz responses">
                                                    <i class="bi bi-list-check"></i> Responses
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>

    @push('styles')
        <style>
            #kpiTiles .card,
            #kpiTiles .card-body {
                color: #fff !important;
            }
        </style>
    @endpush
</x-app-dashboard-layout>
