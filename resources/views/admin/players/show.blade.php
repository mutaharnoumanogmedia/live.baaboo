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

            {{-- KPI Tiles --}}
            <div class="row g-3 mb-4" id="kpiTiles">
                <div class="col-md-3 col-sm-6">
                    <div class="card text-bg-primary h-100 border-0 shadow-sm">
                        <div class="card-body d-flex align-items-center">
                            <i class="bi bi-controller fs-1 me-3 opacity-75"></i>
                            <div>
                                <div class="text-uppercase small">Games Played</div>
                                <h3 class="mb-0">{{ $stats['total_games'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="card text-bg-success h-100 border-0 shadow-sm">
                        <div class="card-body d-flex align-items-center">
                            <i class="bi bi-trophy-fill fs-1 me-3 opacity-75"></i>
                            <div>
                                <div class="text-uppercase small">Games Won</div>
                                <h3 class="mb-0">{{ $stats['games_won'] }}</h3>
                                @if ($stats['total_games'])
                                    <small class="opacity-75">
                                        {{ number_format(($stats['games_won'] / $stats['total_games']) * 100, 1) }}% win rate
                                    </small>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="card text-bg-warning h-100 border-0 shadow-sm">
                        <div class="card-body d-flex align-items-center">
                            <i class="bi bi-bar-chart-fill fs-1 me-3 opacity-75"></i>
                            <div>
                                <div class="text-uppercase small">Max Score</div>
                                <h3 class="mb-0">{{ number_format($stats['max_score'], 2) }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="card text-bg-info h-100 border-0 shadow-sm">
                        <div class="card-body d-flex align-items-center">
                            <i class="bi bi-cash-coin fs-1 me-3 opacity-75"></i>
                            <div>
                                <div class="text-uppercase small">Total Prize Won</div>
                                <h3 class="mb-0">{{ number_format($stats['total_prize_money'], 2) }}</h3>
                                <small class="opacity-75">across {{ $stats['games_won'] }} winning shows</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                {{-- Player Information --}}
                <div class="col-lg-6">
                    <div class="card mb-3 shadow-sm">
                        <div class="card-header bg-dark text-light">
                            <h5 class="mb-0"><i class="bi bi-person-badge me-2"></i>Player Information</h5>
                        </div>
                        <div class="card-body bg-dark text-light">
                            <table class="table table-borderless table-dark align-middle mb-0">
                                <tbody>
                                    <tr>
                                        <th style="width: 40%">ID</th>
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
                                    <tr>
                                        <td>Is Affiliate</td>
                                        <td>
                                            @if ($player->is_affiliate)
                                                <span class="badge bg-info">Yes</span>
                                            @else
                                                <span class="badge bg-secondary">No</span>
                                            @endif
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Referred By + Referred Users --}}
                <div class="col-lg-6">
                    {{-- Referred By --}}
                    <div class="card mb-3 shadow-sm">
                        <div class="card-header bg-dark text-light">
                            <h5 class="mb-0"><i class="bi bi-person-arms-up me-2"></i>Referred By</h5>
                        </div>
                        <div class="card-body bg-dark text-light">
                            @if ($player->referredBy)
                                <table class="table table-borderless table-dark align-middle mb-0">
                                    <tbody>
                                        <tr>
                                            <th style="width: 40%">ID</th>
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
                                            <td>{{ optional($player->referredBy->created_at)->format('Y-m-d H:i') ?? '-' }}
                                            </td>
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
                    <div class="card mb-3 shadow-sm">
                        <div class="card-header bg-dark text-light d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-people-fill me-2"></i>Referred Users</h5>
                            <span class="badge bg-primary">{{ $stats['referred_count'] }}</span>
                        </div>
                        <div class="card-body bg-dark text-light">
                            @if ($player->referredUsers->isEmpty())
                                <p class="text-muted mb-0">
                                    <i class="bi bi-info-circle me-1"></i>
                                    This player has not referred anyone yet.
                                </p>
                            @else
                                <div class="table-responsive">
                                    <table id="referredUsersTable"
                                        class="table table-striped table-borderless table-dark align-middle mb-0 w-100">
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

            {{-- Game Show Participation --}}
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-controller me-2"></i>Game Show Participation</h5>
                    <span class="badge bg-primary">{{ $stats['total_games'] }} shows</span>
                </div>
                <div class="card-body bg-dark text-light">
                    @if ($player->liveShows->isEmpty())
                        <p class="text-muted mb-0">
                            <i class="bi bi-info-circle me-1"></i>
                            No participation records found.
                        </p>
                    @else
                        <div class="table-responsive">
                            <table id="participationTable"
                                class="table table-striped table-borderless table-dark align-middle mb-0 w-100">
                                <thead>
                                    <tr>
                                        <th>Live Show</th>
                                        <th>Scheduled</th>
                                        <th>Show Status</th>
                                        <th>Joined At</th>
                                        <th>Player Status</th>
                                        <th class="text-center">Score</th>
                                        <th class="text-center">Won?</th>
                                        <th>Prize Won</th>
                                        <th>Show Prize Pool</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($player->liveShows as $show)
                                        @php
                                            $pivot = $show->pivot;
                                            $score = (float) ($pivot->score ?? 0);
                                            $isWinner = (bool) $pivot->is_winner;
                                            $prizeWon = $pivot->prize_won;
                                            $hasNumericPrize = $isWinner && is_numeric($prizeWon);
                                            $cashPrize = $hasNumericPrize
                                                ? ((float) $show->prize_amount) * ((float) $prizeWon / 100)
                                                : null;
                                        @endphp
                                        <tr>
                                            <td>
                                                <a class="text-warning text-decoration-none"
                                                    href="{{ route('admin.live-shows.view-details', $show->id) }}">
                                                    {{ $show->title ?? 'N/A' }}
                                                </a>
                                                @if ($show->is_test_show)
                                                    <span class="badge bg-secondary ms-1">Test</span>
                                                @endif
                                            </td>
                                            <td>{{ optional($show->scheduled_at)->format('Y-m-d H:i') ?? '-' }}</td>
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
                                                        <span
                                                            class="badge bg-light text-dark">{{ ucfirst($show->status ?? '-') }}</span>
                                                @endswitch
                                            </td>
                                            <td>{{ optional($pivot->created_at)->format('Y-m-d H:i') ?? '-' }}</td>
                                            <td>
                                                @if (!empty($pivot->status))
                                                    <span class="badge bg-info">{{ ucfirst($pivot->status) }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td class="text-center">{{ number_format($score, 2) }}</td>
                                            <td class="text-center">
                                                @if ($isWinner)
                                                    <span class="badge bg-warning text-dark">
                                                        <i class="bi bi-trophy-fill"></i> Won
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary">No</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($isWinner && !empty($prizeWon) && $prizeWon !== 'n/a')
                                                    @if ($hasNumericPrize)
                                                        <strong>{{ rtrim(rtrim(number_format((float) $prizeWon, 2), '0'), '.') }}%</strong>
                                                        <div class="small text-muted">
                                                            ≈ {{ number_format($cashPrize, 2) }} {{ $show->currency }}
                                                        </div>
                                                    @else
                                                        <strong>{{ $prizeWon }}</strong>
                                                    @endif
                                                @else
                                                    <span class="text-muted">-</span>
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
            .dataTables_wrapper .dataTables_filter input,
            .dataTables_wrapper .dataTables_length select {
                background-color: #2d2d2d;
                border: 1px solid #444;
                color: #fff;
            }

            .dataTables_wrapper .dataTables_info,
            .dataTables_wrapper .dataTables_paginate,
            .dataTables_wrapper .dataTables_length,
            .dataTables_wrapper .dataTables_filter {
                color: #fff;
            }

            .dataTables_wrapper .paginate_button {
                color: #fff !important;
            }

            .dataTables_wrapper .paginate_button.current,
            .dataTables_wrapper .paginate_button.current:hover {
                background: #0d6efd !important;
                border-color: #0d6efd !important;
                color: #fff !important;
            }

            .dataTables_wrapper .paginate_button:hover {
                background: #495057 !important;
                border-color: #495057 !important;
                color: #fff !important;
            }

            #kpiTiles .card, #kpiTiles .card-body {
                color: #fff !important;
            }
            
        </style>
    @endpush

    @push('scripts')
        <script>
            $(function() {
                if ($.fn.DataTable) {
                    if ($('#participationTable').length) {
                        $('#participationTable').DataTable({
                            pageLength: 10,
                            lengthMenu: [
                                [10, 25, 50, -1],
                                [10, 25, 50, 'All']
                            ],
                            order: [
                                [3, 'desc']
                            ],
                            columnDefs: [{
                                orderable: false,
                                targets: -1
                            }],
                        });
                    }

                    if ($('#referredUsersTable').length) {
                        $('#referredUsersTable').DataTable({
                            pageLength: 10,
                            lengthMenu: [
                                [10, 25, 50, -1],
                                [10, 25, 50, 'All']
                            ],
                            order: [
                                [3, 'desc']
                            ],
                            columnDefs: [{
                                orderable: false,
                                targets: -1
                            }],
                        });
                    }
                }
            });
        </script>
    @endpush
</x-app-dashboard-layout>
