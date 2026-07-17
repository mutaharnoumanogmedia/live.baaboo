@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
@endpush

<x-app-dashboard-layout>
    @php
        $isGlobalView = $isGlobalView ?? false;
    @endphp
    <x-slot name="header">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 py-2">
            <div>
                <h2 class="h4 mb-0">{{ $isGlobalView ? 'All Live Show Participants' : 'All Players' }}</h2>
                @if ($isGlobalView)
                    <div class="text-muted small">All participations across live shows</div>
                @else
                    <div class="text-muted small">{{ $liveShow->title }}</div>
                @endif
            </div>
            @unless ($isGlobalView)
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.live-shows.stream-management', $liveShow->id) }}"
                        class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i> Stream Management
                    </a>
                    <a href="{{ route('admin.live-shows.export-all-users-as-csv', $liveShow->id) }}"
                        class="btn btn-primary btn-sm">
                        <i class="fas fa-file-export me-1"></i> Export Users
                    </a>
                    @if ($hasSpecialQuiz)
                        <a href="{{ route('admin.live-shows.export-special-winners-csv', $liveShow->id) }}"
                            class="btn btn-warning btn-sm text-dark">
                            <i class="fas fa-gift me-1"></i> Export Special Winners
                        </a>
                    @endif
                </div>
            @endunless
        </div>
    </x-slot>

    <div class="py-4">
        <div class="container-fluid">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    @php
                        $emailState = function ($status, $sentAt) {
                            if (
                                $sentAt &&
                                !\Illuminate\Support\Str::startsWith(strtolower((string) $status), 'failed')
                            ) {
                                return 'sent';
                            }
                            if (
                                $status &&
                                \Illuminate\Support\Str::startsWith(strtolower((string) $status), 'failed')
                            ) {
                                return 'failed';
                            }

                            return 'pending';
                        };
                    @endphp
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                        <div>
                            <div class="fw-bold">
                                {{ $isGlobalView ? 'Live Show Participations' : 'Participating Players' }}</div>
                            <div class="text-muted small">
                                Showing all {{ $totalPlayers }}
                                {{ $isGlobalView ? 'live show participations' : 'participating players' }} with
                                DataTable search,
                                sorting, and pagination.
                            </div>
                        </div>
                    </div>

                    <ul class="nav nav-tabs mb-3" id="allPlayersTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active fw-semibold" id="mainPlayersTabBtn" data-bs-toggle="tab"
                                data-bs-target="#mainPlayersTabPane" type="button" role="tab">
                                <i class="fas fa-trophy me-1"></i> Main Quiz Ranking
                            </button>
                        </li>
                        @if ($hasSpecialQuiz)
                            <li class="nav-item" role="presentation">
                                <button class="nav-link fw-semibold" id="specialPlayersTabBtn" data-bs-toggle="tab"
                                    data-bs-target="#specialPlayersTabPane" type="button" role="tab">
                                    <i class="fas fa-star me-1 text-warning"></i> Special Quiz Ranking
                                </button>
                            </li>
                        @endif
                    </ul>

                    <div class="tab-content" id="allPlayersTabContent">
                        <div class="tab-pane fade show active" id="mainPlayersTabPane" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-light table-striped align-middle data-table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            @if ($isGlobalView)
                                                <th>Live Show</th>
                                            @endif
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Score</th>
                                            <th>Status</th>
                                            <th>Online</th>
                                            <th>Winner</th>
                                            <th>Prize</th>
                                            <th>Joined</th>
                                            <th>Email Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($players as $index => $player)
                                            @php
                                                $pivot = $player->pivot;
                                                $rowLiveShowId = $isGlobalView
                                                    ? $player->participant_live_show_id
                                                    : $liveShow->id;
                                                $rowKey = $isGlobalView
                                                    ? "{$rowLiveShowId}_{$player->id}"
                                                    : (string) $player->id;
                                                $isVoucherWinner =
                                                    $pivot->winnerPrize && $pivot->winnerPrize->is_voucher;
                                                $isCashWinner =
                                                    $pivot->is_winner &&
                                                    $pivot->winnerPrize &&
                                                    !$pivot->winnerPrize->is_voucher;

                                                $emailTypes = [
                                                    'winner' => [
                                                        'label' => 'Winner Email',
                                                        'status' => $pivot->winner_email_sent_status,
                                                        'sent_at' => $pivot->winner_email_sent_at,
                                                        'show' => true,
                                                    ],
                                                    'voucher' => [
                                                        'label' => 'Voucher Email',
                                                        'status' => $pivot->winner_voucher_email_sent_status,
                                                        'sent_at' => $pivot->winner_voucher_email_sent_at,
                                                        'show' => (bool) $isVoucherWinner,
                                                    ],
                                                    'cash' => [
                                                        'label' => 'Cash Email',
                                                        'status' => $pivot->winner_cash_email_sent_status,
                                                        'sent_at' => $pivot->winner_cash_email_sent_at,
                                                        'show' => (bool) $isCashWinner,
                                                    ],
                                                ];

                                            @endphp
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                @if ($isGlobalView)
                                                    <td>
                                                        <div class="fw-semibold">
                                                            {{ $player->participant_live_show_title }}</div>
                                                        <a href="{{ route('admin.live-shows.stream-management', $rowLiveShowId) }}"
                                                            class="small text-muted">#{{ $rowLiveShowId }}</a>
                                                    </td>
                                                @endif
                                                <td>
                                                    <div class="fw-semibold">{{ $player->name }}</div>
                                                    <div class="small text-muted">{{ $player->user_name }}</div>
                                                </td>
                                                <td>{{ $player->email }}</td>
                                                <td>{{ $player->pivot->score ?? 0 }}</td>
                                                <td>{{ ucfirst($player->pivot->status ?? '--') }}</td>
                                                <td>
                                                    <span
                                                        class="badge {{ $player->pivot->is_online ? 'bg-success' : 'bg-secondary' }}">
                                                        {{ $player->pivot->is_online ? 'Online' : 'Offline' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @if ($player->pivot->is_winner)
                                                        <span class="badge bg-warning text-dark">Winner</span>
                                                    @else
                                                        <span class="text-muted">No</span>
                                                    @endif
                                                </td>
                                                <td>{{ $player->pivot->prize_won ?? '--' }}
                                                    <div>

                                                        {!! $player->pivot->winnerPrize
                                                            ? ($player->pivot->winnerPrize->is_voucher
                                                                ? '<span class="badge bg-warning text-dark">Voucher</span> <div>' .
                                                                    '<a target="_blank" href="https://admin.shopify.com/store/store-baaboo/discounts/' .
                                                                    ($player->pivot->winnerPrize->discountRule->shopify_id ?? '--') .
                                                                    '">' .
                                                                    ($player->pivot->discount_code ?? '--') .
                                                                    '</a>' .
                                                                    '</div>'
                                                                : '<span class="badge bg-success text-white">Cash</span>')
                                                            : '--' !!}
                                                    </div>

                                                </td>
                                                <td>
                                                    {{ $player->pivot->created_at ? \Carbon\Carbon::parse($player->pivot->created_at)->format('d M Y, H:i') : '--' }}
                                                </td>
                                                <td style="min-width: 190px;">
                                                    @foreach ($emailTypes as $key => $email)
                                                        @if ($email['show'])
                                                            @php $state = $emailState($email['status'], $email['sent_at']); @endphp
                                                            <div class="d-flex align-items-center gap-1 mb-1 small">
                                                                <span class="text-muted"
                                                                    style="min-width: 84px;">{{ $email['label'] }}:</span>
                                                                @if ($state === 'sent')
                                                                    <span class="badge bg-success"
                                                                        data-bs-toggle="tooltip"
                                                                        title="{{ \Carbon\Carbon::parse($email['sent_at'])->format('d M Y, H:i') }}">Sent</span>
                                                                @elseif ($state === 'failed')
                                                                    <span class="badge bg-danger"
                                                                        data-bs-toggle="tooltip"
                                                                        title="{{ $email['status'] }}">Failed</span>
                                                                @else
                                                                    <span class="badge bg-secondary">Not sent</span>
                                                                @endif
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                </td>
                                                <td class="text-end">
                                                    <div class="dropdown">
                                                        <button class="btn btn-sm btn-secondary dropdown-toggle"
                                                            type="button"
                                                            id="playerActionsDropdown{{ $rowKey }}"
                                                            data-bs-toggle="dropdown" aria-expanded="false">
                                                            <i class="bi bi-three-dots"></i>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-end"
                                                            aria-labelledby="playerActionsDropdown{{ $rowKey }}">
                                                            <li>
                                                                <a class="dropdown-item"
                                                                    href="{{ route('admin.players.show', $player->id) }}"
                                                                    target="_blank">
                                                                    <i class="fas fa-eye me-1"></i>
                                                                    View Details
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <a class="dropdown-item" href="javascript:void(0)"
                                                                    onclick="toggleBlockStatusForPlayer('{{ $player->id }}', '{{ $player->is_blocked_for_live_show ? 'unblock' : 'block' }}', '{{ $rowLiveShowId }}')">
                                                                    <i class="fas fa-ban me-1"></i>
                                                                    {{ $player->is_blocked_for_live_show ? 'Unblock Player' : 'Block Player' }}
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <a class="dropdown-item" href="javascript:void(0)"
                                                                    onclick="resetScore('{{ $player->id }}', '{{ $rowLiveShowId }}')">
                                                                    <i class="fas fa-sync me-1"></i>
                                                                    Reset Score
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <hr class="dropdown-divider">
                                                            </li>
                                                            @if ($isGlobalView)
                                                                <li>
                                                                    <a class="dropdown-item"
                                                                        href="{{ route('admin.live-shows.stream-management', $rowLiveShowId) }}">
                                                                        <i class="fas fa-broadcast-tower me-1"></i>
                                                                        Open Live Show
                                                                    </a>
                                                                </li>
                                                                <li>
                                                                    <hr class="dropdown-divider">
                                                                </li>
                                                            @endif
                                                            <li>
                                                                <a class="dropdown-item" href="javascript:void(0)"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#emailModal{{ $rowKey }}">
                                                                    <i class="fas fa-envelope me-1"></i>
                                                                    Manage Emails
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="{{ $isGlobalView ? 12 : 11 }}"
                                                    class="text-center text-muted py-4">No players found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        @if ($hasSpecialQuiz)
                            <div class="tab-pane fade" id="specialPlayersTabPane" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-light table-striped align-middle data-table-special">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                @if ($isGlobalView)
                                                    <th>Live Show</th>
                                                @endif
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Special Score</th>
                                                <th>Status</th>
                                                <th>Online</th>
                                                <th>Special Winner</th>
                                                <th>Gift / Prize</th>
                                                <th>Joined</th>
                                                <th>Email Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($specialPlayers as $index => $player)
                                                @php
                                                    $pivot = $player->pivot;
                                                    $rowLiveShowId = $isGlobalView
                                                        ? $player->participant_live_show_id
                                                        : $liveShow->id;
                                                    $rowKey = $isGlobalView
                                                        ? "{$rowLiveShowId}_{$player->id}"
                                                        : (string) $player->id;
                                                    $specialGift = $pivot->specialGift;
                                                    $isSpecialWinner = (bool) $pivot->is_special_winner;

                                                    $specialEmailTypes = [
                                                        'special_winner' => [
                                                            'label' => 'Special Winner Email',
                                                            'status' => $pivot->special_winner_email_sent_status,
                                                            'sent_at' => $pivot->special_winner_email_sent_at,
                                                            'show' => $isSpecialWinner,
                                                        ],
                                                        'special_type' => [
                                                            'label' => 'Special Type Email',
                                                            'status' => $pivot->special_type_email_sent_status,
                                                            'sent_at' => $pivot->special_type_email_sent_at,
                                                            'show' => $isSpecialWinner && $specialGift,
                                                        ],
                                                    ];
                                                @endphp
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    @if ($isGlobalView)
                                                        <td>
                                                            <div class="fw-semibold">
                                                                {{ $player->participant_live_show_title }}</div>
                                                            <a href="{{ route('admin.live-shows.stream-management', $rowLiveShowId) }}"
                                                                class="small text-muted">#{{ $rowLiveShowId }}</a>
                                                        </td>
                                                    @endif
                                                    <td>
                                                        <div class="fw-semibold">{{ $player->name }}</div>
                                                        <div class="small text-muted">{{ $player->user_name }}</div>
                                                    </td>
                                                    <td>{{ $player->email }}</td>
                                                    <td>{{ $pivot->special_score ?? 0 }}</td>
                                                    <td>{{ ucfirst($pivot->status ?? '--') }}</td>
                                                    <td>
                                                        <span
                                                            class="badge {{ $pivot->is_online ? 'bg-success' : 'bg-secondary' }}">
                                                            {{ $pivot->is_online ? 'Online' : 'Offline' }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        @if ($isSpecialWinner)
                                                            <span class="badge bg-warning text-dark">Special
                                                                Winner</span>
                                                        @else
                                                            <span class="text-muted">No</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        {{ $pivot->special_prize_won ?? '--' }}
                                                        @if ($specialGift)
                                                            <div>
                                                                <span
                                                                    class="badge bg-warning text-dark">{{ ucfirst($specialGift->type) }}</span>
                                                                @if ($pivot->special_discount_code)
                                                                    <div class="small">
                                                                        {{ $pivot->special_discount_code }}</div>
                                                                @endif
                                                            </div>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        {{ $pivot->created_at ? \Carbon\Carbon::parse($pivot->created_at)->format('d M Y, H:i') : '--' }}
                                                    </td>
                                                    <td style="min-width: 190px;">
                                                        @foreach ($specialEmailTypes as $key => $email)
                                                            @if ($email['show'])
                                                                @php $state = $emailState($email['status'], $email['sent_at']); @endphp
                                                                <div
                                                                    class="d-flex align-items-center gap-1 mb-1 small">
                                                                    <span class="text-muted"
                                                                        style="min-width: 110px;">{{ $email['label'] }}:</span>
                                                                    @if ($state === 'sent')
                                                                        <span class="badge bg-success">Sent</span>
                                                                    @elseif ($state === 'failed')
                                                                        <span class="badge bg-danger">Failed</span>
                                                                    @else
                                                                        <span class="badge bg-secondary">Not
                                                                            sent</span>
                                                                    @endif
                                                                </div>
                                                            @endif
                                                        @endforeach
                                                    </td>
                                                    <td class="text-end">
                                                        <div class="dropdown">
                                                            <button class="btn btn-sm btn-secondary dropdown-toggle"
                                                                type="button" data-bs-toggle="dropdown"
                                                                aria-expanded="false">
                                                                <i class="bi bi-three-dots"></i>
                                                            </button>
                                                            <ul class="dropdown-menu dropdown-menu-end">
                                                                <li>
                                                                    <a class="dropdown-item"
                                                                        href="{{ route('admin.players.show', $player->id) }}"
                                                                        target="_blank">
                                                                        <i class="fas fa-eye me-1"></i> View Details
                                                                    </a>
                                                                </li>
                                                                @if ($isSpecialWinner)
                                                                    <li>
                                                                        <a class="dropdown-item"
                                                                            href="javascript:void(0)"
                                                                            data-bs-toggle="modal"
                                                                            data-bs-target="#specialEmailModal{{ $rowKey }}">
                                                                            <i class="fas fa-envelope me-1"></i> Manage
                                                                            Special Emails
                                                                        </a>
                                                                    </li>
                                                                @endif
                                                                @if ($isGlobalView)
                                                                    <li>
                                                                        <a class="dropdown-item"
                                                                            href="{{ route('admin.live-shows.stream-management', $rowLiveShowId) }}">
                                                                            <i class="fas fa-broadcast-tower me-1"></i>
                                                                            Open Live Show
                                                                        </a>
                                                                    </li>
                                                                @endif
                                                            </ul>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="{{ $isGlobalView ? 12 : 11 }}"
                                                        class="text-center text-muted py-4">No special quiz players
                                                        found.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @foreach ($players as $player)
        @php
            $pivot = $player->pivot;
            $rowLiveShowId = $isGlobalView ? $player->participant_live_show_id : $liveShow->id;
            $rowKey = $isGlobalView ? "{$rowLiveShowId}_{$player->id}" : (string) $player->id;
            $isVoucherWinner = $pivot->winnerPrize && $pivot->winnerPrize->is_voucher;
            $isCashWinner = $pivot->is_winner && $pivot->winnerPrize && !$pivot->winnerPrize->is_voucher;

            $modalEmailTypes = [
                'winner' => [
                    'label' => 'Winner Email',
                    'desc' => 'Generic winner notification email.',
                    'status' => $pivot->winner_email_sent_status,
                    'sent_at' => $pivot->winner_email_sent_at,
                    'show' => true,
                ],
                'voucher' => [
                    'label' => 'Voucher Email',
                    'desc' => 'Voucher / discount code email (voucher winners).',
                    'status' => $pivot->winner_voucher_email_sent_status,
                    'sent_at' => $pivot->winner_voucher_email_sent_at,
                    'show' => (bool) $isVoucherWinner,
                ],
                'cash' => [
                    'label' => 'Cash Email',
                    'desc' => 'Cash prize email (cash winners).',
                    'status' => $pivot->winner_cash_email_sent_status,
                    'sent_at' => $pivot->winner_cash_email_sent_at,
                    'show' => (bool) $isCashWinner,
                ],
            ];

            $modalEmailState = function ($status, $sentAt) {
                if ($sentAt && !\Illuminate\Support\Str::startsWith(strtolower((string) $status), 'failed')) {
                    return 'sent';
                }
                if ($status && \Illuminate\Support\Str::startsWith(strtolower((string) $status), 'failed')) {
                    return 'failed';
                }

                return 'pending';
            };
        @endphp
        <div class="modal fade" id="emailModal{{ $rowKey }}" tabindex="-1"
            aria-labelledby="emailModalLabel{{ $rowKey }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-dark" id="emailModalLabel{{ $rowKey }}">
                            <i class="fas fa-envelope me-1"></i>
                            Manage Emails &mdash; {{ $player->name }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3 small text-muted">
                            Recipient: <span class="fw-semibold">{{ $player->email }}</span>
                        </div>
                        <div class="alert alert-info small py-2">
                            Re-sending clears the email's status first, then sends only that email again.
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0 text-dark">
                                <thead>
                                    <tr>
                                        <th>Email</th>
                                        <th>Status</th>
                                        <th>Sent At</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($modalEmailTypes as $key => $email)
                                        @if ($email['show'])
                                            @php $state = $modalEmailState($email['status'], $email['sent_at']); @endphp
                                            <tr>
                                                <td>
                                                    <div class="fw-semibold">{{ $email['label'] }}</div>
                                                    <div class="small text-muted">{{ $email['desc'] }}</div>
                                                </td>
                                                <td>
                                                    @if ($state === 'sent')
                                                        <span class="badge bg-success">Sent</span>
                                                    @elseif ($state === 'failed')
                                                        <span class="badge bg-danger" data-bs-toggle="tooltip"
                                                            title="{{ $email['status'] }}">Failed</span>
                                                    @else
                                                        <span class="badge bg-secondary">Not sent</span>
                                                    @endif
                                                </td>
                                                <td class="small">
                                                    {{ $email['sent_at'] ? \Carbon\Carbon::parse($email['sent_at'])->format('d M Y, H:i') : '--' }}
                                                </td>
                                                <td class="text-end">
                                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                                        onclick="resendPlayerEmail(this, '{{ $player->id }}', '{{ $key }}', '{{ $rowLiveShowId }}')">
                                                        <i class="fas fa-paper-plane me-1"></i>
                                                        {{ $state === 'pending' ? 'Send' : 'Re-send' }}
                                                    </button>
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm"
                            data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    @if ($hasSpecialQuiz)
        @foreach ($specialPlayers as $player)
            @php
                $pivot = $player->pivot;
                $rowLiveShowId = $isGlobalView ? $player->participant_live_show_id : $liveShow->id;
                $rowKey = $isGlobalView ? "{$rowLiveShowId}_{$player->id}" : (string) $player->id;
                $specialGift = $pivot->specialGift;
                $isSpecialWinner = (bool) $pivot->is_special_winner;

                $modalSpecialEmailTypes = [
                    'special_winner' => [
                        'label' => 'Special Winner Email',
                        'desc' => 'Generic special quiz winner notification.',
                        'status' => $pivot->special_winner_email_sent_status,
                        'sent_at' => $pivot->special_winner_email_sent_at,
                        'show' => $isSpecialWinner,
                    ],
                    'special_type' => [
                        'label' => 'Special Type Email',
                        'desc' => 'Type-specific email (' . ($specialGift->type ?? 'n/a') . ').',
                        'status' => $pivot->special_type_email_sent_status,
                        'sent_at' => $pivot->special_type_email_sent_at,
                        'show' => $isSpecialWinner && $specialGift,
                    ],
                ];
            @endphp
            <div class="modal fade" id="specialEmailModal{{ $rowKey }}" tabindex="-1"
                aria-labelledby="specialEmailModalLabel{{ $rowKey }}" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title text-dark" id="specialEmailModalLabel{{ $rowKey }}">
                                <i class="fas fa-gift me-1"></i>
                                Manage Special Emails &mdash; {{ $player->name }}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="table-responsive">
                                <table class="table table-sm align-middle mb-0 text-dark">
                                    <thead>
                                        <tr>
                                            <th>Email</th>
                                            <th>Status</th>
                                            <th>Sent At</th>
                                            <th class="text-end">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($modalSpecialEmailTypes as $key => $email)
                                            @if ($email['show'])
                                                @php $state = $emailState($email['status'], $email['sent_at']); @endphp
                                                <tr>
                                                    <td>
                                                        <div class="fw-semibold">{{ $email['label'] }}</div>
                                                        <div class="small text-muted">{{ $email['desc'] }}</div>
                                                    </td>
                                                    <td>
                                                        @if ($state === 'sent')
                                                            <span class="badge bg-success">Sent</span>
                                                        @elseif ($state === 'failed')
                                                            <span class="badge bg-danger">Failed</span>
                                                        @else
                                                            <span class="badge bg-secondary">Not sent</span>
                                                        @endif
                                                    </td>
                                                    <td class="small">
                                                        {{ $email['sent_at'] ? \Carbon\Carbon::parse($email['sent_at'])->format('d M Y, H:i') : '--' }}
                                                    </td>
                                                    <td class="text-end">
                                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                                            onclick="resendPlayerEmail(this, '{{ $player->id }}', '{{ $key }}', '{{ $rowLiveShowId }}')">
                                                            <i class="fas fa-paper-plane me-1"></i>
                                                            {{ $state === 'pending' ? 'Send' : 'Re-send' }}
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    @endif

    @push('scripts')
        <script>
            function toggleBlockStatusForPlayer(userId, action, liveShowId) {
                if (!confirm('Are you sure you want to ' + action + ' this player?')) {
                    return;
                }

                fetch(`{{ url('admin/live-shows/stream-management') }}/${liveShowId}/toggle-block-status-for-player/${userId}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            action: action
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        alert(data.message || 'Player action completed.');
                        if (data.success) {
                            window.location.reload();
                        }
                    })
                    .catch(error => {
                        console.error('Error updating player block status:', error);
                        alert('Error updating player block status.');
                    });
            }

            function resetScore(userId, liveShowId) {
                if (!confirm('Are you sure you want to reset this player score?')) {
                    return;
                }

                fetch(`{{ url('admin/live-shows/stream-management') }}/${liveShowId}/reset-score/${userId}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                    })
                    .then(response => response.json())
                    .then(data => {
                        alert(data.message || 'Player score updated.');
                        if (data.success) {
                            window.location.reload();
                        }
                    })
                    .catch(error => {
                        console.error('Error resetting player score:', error);
                        alert('Error resetting player score.');
                    });
            }

            function resendPlayerEmail(button, userId, type, liveShowId) {
                if (!confirm('Re-send the ' + type + ' email to this player?')) {
                    return;
                }

                const originalHtml = button.innerHTML;
                button.disabled = true;
                button.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Sending...';

                fetch(`{{ url('admin/live-shows/stream-management') }}/${liveShowId}/resend-email/${userId}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            type: type
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        alert(data.message || 'Email action completed.');
                        if (data.success) {
                            window.location.reload();
                        } else {
                            button.disabled = false;
                            button.innerHTML = originalHtml;
                        }
                    })
                    .catch(error => {
                        console.error('Error re-sending email:', error);
                        alert('Error re-sending email.');
                        button.disabled = false;
                        button.innerHTML = originalHtml;
                    });
            }

            document.addEventListener('DOMContentLoaded', function() {
                if (window.bootstrap && bootstrap.Tooltip) {
                    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function(el) {
                        new bootstrap.Tooltip(el);
                    });
                }

                if (window.jQuery && jQuery.fn.DataTable) {
                    const specialTable = jQuery('.data-table-special');
                    if (specialTable.length) {
                        specialTable.DataTable({
                            lengthChange: true,
                            pageLength: 20,
                        });
                    }
                }
            });
        </script>
    @endpush
</x-app-dashboard-layout>
