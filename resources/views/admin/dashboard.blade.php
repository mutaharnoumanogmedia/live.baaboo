<x-app-dashboard-layout>
    <div class="container">
        <x-slot name="header">
            <h2 class="mb-3 font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Dashboard') }}
            </h2>
        </x-slot>

        <!-- Primary Stats -->
        <div class="row g-4 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="stat-card card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon me-3">
                                <i class="bi bi-people-fill"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="stat-number">{{ number_format($activePlayers) }}</div>
                                <div class="stat-label">Active Players</div>
                                <div
                                    class="stat-change {{ $rocOfPlayersFromLastWeekPercentage >= 0 ? 'text-success' : 'text-danger' }}">
                                    <i
                                        class="bi {{ $rocOfPlayersFromLastWeekPercentage >= 0 ? 'bi-arrow-up' : 'bi-arrow-down' }}"></i>
                                    {{ number_format($rocOfPlayersFromLastWeekPercentage, 2) }}% from last week
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="stat-card card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon me-3">
                                <i class="bi bi-eye-fill"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="stat-number">{{ number_format($totalViewers) }}</div>
                                <div class="stat-label">Live Viewers</div>
                                <div
                                    class="stat-change {{ $rocOfViewersFromLastWeekPercentage >= 0 ? 'text-success' : 'text-danger' }}">
                                    <i
                                        class="bi {{ $rocOfViewersFromLastWeekPercentage >= 0 ? 'bi-arrow-up' : 'bi-arrow-down' }}"></i>
                                    {{ number_format($rocOfViewersFromLastWeekPercentage, 2) }}% from last week
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="stat-card card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon me-3">
                                <i class="bi bi-camera-video-fill"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="stat-number">{{ number_format($totalLiveQuizShows) }}</div>
                                <div class="stat-label">Live Shows</div>
                                <div class="stat-change text-info">
                                    <i class="bi bi-clock"></i> {{ $totalScheduledLiveQuizShows }} scheduled
                                    @if ($liveNowShows > 0)
                                        &middot; <span class="text-danger"><i class="bi bi-broadcast"></i>
                                            {{ $liveNowShows }} live now</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="stat-card card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon me-3">
                                <i class="bi bi-trophy-fill"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="stat-number">{{ number_format($totalWinners) }}</div>
                                <div class="stat-label">Total Winners</div>
                                <div class="stat-change text-success">
                                    <i class="bi bi-arrow-up"></i> {{ $winnersThisWeek }} this week
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Secondary Stats -->
        <div class="row g-4 mb-5">
            <div class="col-xl-3 col-md-6">
                <div class="stat-card card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon me-3">
                                <i class="bi bi-person-plus-fill"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="stat-number">{{ number_format($totalRegisteredUsers) }}</div>
                                <div class="stat-label">Registered Players</div>
                                <div class="stat-change text-success">
                                    <i class="bi bi-arrow-up"></i> {{ $newUsersThisWeek }} new this week
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="stat-card card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon me-3">
                                <i class="bi bi-question-circle-fill"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="stat-number">{{ number_format($totalQuizQuestions) }}</div>
                                <div class="stat-label">Quiz Questions</div>
                                <div class="stat-change text-info">
                                    <i class="bi bi-chat-left-text"></i> {{ number_format($totalQuizResponses) }}
                                    answers submitted
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="stat-card card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon me-3">
                                <i class="bi bi-bullseye"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="stat-number">{{ number_format($quizAccuracy, 1) }}%</div>
                                <div class="stat-label">Quiz Accuracy</div>
                                <div class="stat-change text-muted">
                                    <i class="bi bi-check2-circle"></i> correct answers overall
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="stat-card card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon me-3">
                                <i class="bi bi-gift-fill"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="stat-number">{{ number_format($totalPrizePool) }}</div>
                                <div class="stat-label">Total Prize Pool</div>
                                <div class="stat-change text-muted">
                                    <i class="bi bi-flag"></i> {{ $completedShows }} shows completed
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Tasks -->
        <div class="row g-4 mb-5">
            <div class="col-12">
                <div class="content-card card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h4 class="card-title mb-0"><i class="bi bi-list-check me-2"></i>Pending Tasks</h4>
                        <span class="badge bg-{{ $pendingTasks->isEmpty() ? 'success' : 'warning' }}">
                            {{ $pendingTasks->count() }} open
                        </span>
                    </div>
                    <div class="card-body p-0">
                        @forelse ($pendingTasks as $task)
                            <a href="{{ $task['url'] }}"
                                class="d-flex align-items-center justify-content-between text-decoration-none border-bottom px-3 py-3">
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-{{ $task['color'] }} me-3">
                                        <i class="bi {{ $task['icon'] }}"></i>
                                    </span>
                                    <span style="color: var(--text-primary);">{{ $task['label'] }}</span>
                                </div>
                                <span class="badge rounded-pill bg-{{ $task['color'] }}">{{ $task['count'] }}</span>
                            </a>
                        @empty
                            <div class="px-3 py-4 text-center text-muted">
                                <i class="bi bi-check2-all me-1"></i> All caught up — no pending tasks.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row g-3 mb-5">
            <div class="col-xl-2 col-md-4 col-6">
                <a href="{{ route('admin.live-shows.index') }}" class="text-decoration-none">
                    <div class="quick-action">
                        <i class="bi bi-play-circle-fill"></i>
                        <div style="color: var(--text-primary); font-weight: 600;">Start Stream</div>
                    </div>
                </a>
            </div>

            <div class="col-xl-2 col-md-4 col-6">
                <a href="{{ route('admin.live-show-quizzes.create') }}" class="text-decoration-none">
                    <div class="quick-action">
                        <i class="bi bi-plus-circle-fill"></i>
                        <div style="color: var(--text-primary); font-weight: 600;">Add Question</div>
                    </div>
                </a>
            </div>

            <div class="col-xl-2 col-md-4 col-6">
                <a href="{{ route('admin.live-shows.create') }}" class="text-decoration-none">
                    <div class="quick-action">
                        <i class="bi bi-calendar-plus-fill"></i>
                        <div style="color: var(--text-primary); font-weight: 600;">Schedule Show</div>
                    </div>
                </a>
            </div>

            <div class="col-xl-2 col-md-4 col-6">
                <a href="{{ route('admin.players.index') }}" class="text-decoration-none">
                    <div class="quick-action">
                        <i class="bi bi-people-fill"></i>
                        <div style="color: var(--text-primary); font-weight: 600;">Players</div>
                    </div>
                </a>
            </div>

            <div class="col-xl-2 col-md-4 col-6">
                <a href="{{ route('admin.analytics.index') }}" class="text-decoration-none">
                    <div class="quick-action">
                        <i class="bi bi-bar-chart-fill"></i>
                        <div style="color: var(--text-primary); font-weight: 600;">Analytics</div>
                    </div>
                </a>
            </div>

            <div class="col-xl-2 col-md-4 col-6">
                <a href="{{ route('admin.settings.index') }}" class="text-decoration-none">
                    <div class="quick-action">
                        <i class="bi bi-sliders"></i>
                        <div style="color: var(--text-primary); font-weight: 600;">App Settings</div>
                    </div>
                </a>
            </div>
        </div>

        <!-- Shows Lists -->
        <div class="row g-4 mb-5">
            <div class="col-lg-6">
                <div class="content-card card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h4 class="card-title mb-0"><i class="bi bi-calendar-event me-2"></i>Upcoming Shows</h4>
                        <a href="{{ route('admin.live-shows.index') }}" class="small">View all</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Show</th>
                                        <th>Scheduled At</th>
                                        <th>Prize</th>
                                        <th>Questions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($upcomingShows as $show)
                                        <tr>
                                            <td>
                                                <a href="{{ route('admin.live-shows.show', $show->id) }}"
                                                    class="text-decoration-none fw-semibold">
                                                    {{ \Illuminate\Support\Str::limit($show->title, 30) }}
                                                </a>
                                                @if ($show->is_test_show)
                                                    <span class="badge bg-secondary ms-1">Test</span>
                                                @endif
                                            </td>
                                            <td class="text-nowrap">
                                                {{ $show->scheduled_at?->format('d M, h:i A') ?? '—' }}
                                            </td>
                                            <td>{{ $show->currency }} {{ number_format($show->prize_amount) }}</td>
                                            <td>
                                                <span
                                                    class="badge bg-{{ $show->quizzes_count > 0 ? 'success' : 'danger' }}">
                                                    {{ $show->quizzes_count }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">No upcoming shows
                                                scheduled.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="content-card card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h4 class="card-title mb-0"><i class="bi bi-clock-history me-2"></i>Recent Shows</h4>
                        <a href="{{ route('admin.live-shows.index') }}" class="small">View all</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Show</th>
                                        <th>Status</th>
                                        <th>Players</th>
                                        <th>Winners</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($recentShows as $show)
                                        <tr>
                                            <td>
                                                <a href="{{ route('admin.live-shows.show', $show->id) }}"
                                                    class="text-decoration-none fw-semibold">
                                                    {{ \Illuminate\Support\Str::limit($show->title, 30) }}
                                                </a>
                                            </td>
                                            <td>
                                                <span
                                                    class="badge bg-{{ $show->status === 'live' ? 'danger' : 'success' }}">
                                                    {{ ucfirst($show->status) }}
                                                </span>
                                            </td>
                                            <td>{{ number_format($show->users_count) }}</td>
                                            <td>
                                                @if ($show->winners_announced)
                                                    <span class="badge bg-success">Announced</span>
                                                @else
                                                    <span class="badge bg-warning text-dark">Pending</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">No shows have run
                                                yet.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Players Lists -->
        <div class="row g-4 mb-5">
            <div class="col-lg-6">
                <div class="content-card card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h4 class="card-title mb-0"><i class="bi bi-award-fill me-2"></i>Top Players</h4>
                        <a href="{{ route('admin.players.index') }}" class="small">View all</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Player</th>
                                        <th>Score</th>
                                        <th>Correct</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($topPlayers as $player)
                                        <tr>
                                            <td>
                                                @if ($loop->iteration === 1)
                                                    <i class="bi bi-trophy-fill text-warning"></i>
                                                @else
                                                    {{ $loop->iteration }}
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.players.show', $player->id) }}"
                                                    class="text-decoration-none fw-semibold">
                                                    {{ $player->name }}
                                                </a>
                                                <div class="small text-muted">{{ $player->email }}</div>
                                            </td>
                                            <td class="fw-semibold">{{ number_format($player->total_score, 2) }}</td>
                                            <td>{{ $player->correct_answers }} / {{ $player->total_answers }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">No quiz responses
                                                yet.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="content-card card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h4 class="card-title mb-0"><i class="bi bi-person-plus-fill me-2"></i>Recent Signups</h4>
                        <a href="{{ route('admin.players.index') }}" class="small">View all</a>
                    </div>
                    <div class="card-body p-0">
                        @forelse ($recentPlayers as $player)
                            <a href="{{ route('admin.players.show', $player->id) }}"
                                class="d-flex align-items-center justify-content-between text-decoration-none border-bottom px-3 py-2">
                                <div>
                                    <div class="fw-semibold" style="color: var(--text-primary);">{{ $player->name }}
                                    </div>
                                    <div class="small text-muted">{{ $player->email }}</div>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-{{ $player->is_active ? 'success' : 'secondary' }}">
                                        {{ $player->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                    <div class="small text-muted">{{ $player->created_at->diffForHumans() }}</div>
                                </div>
                            </a>
                        @empty
                            <div class="px-3 py-4 text-center text-muted">No players registered yet.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="row g-4 mb-5">
            <div class="col-lg-6">
                <h4 class="mb-3">User Signups Over Time ({{ $year }})</h4>
                <div class="content-card card p-3">
                    <canvas id="signupChart" style="display: none; height: 350px;"></canvas>
                </div>
            </div>
            <div class="col-lg-6">
                <h4 class="mb-3">Views Over Time ({{ $year }})</h4>
                <div class="card p-3">
                    <canvas id="visitsChart" style="display: none; height: 350px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

        <script>
            document.addEventListener("DOMContentLoaded", function() {
                const labels = {!! $labels !!};


                const dataUsers = {!! $dataUsers !!};


                let ctx = document.getElementById('signupChart').getContext('2d');
                document.getElementById("signupChart").style.display = "block";

                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: "User Signups",
                            data: dataUsers,
                            borderWidth: 3,
                            borderColor: '#ff0000',
                            backgroundColor: '#ff000044',
                            tension: 0.4,
                            fill: true,
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        },
                        plugins: {
                            legend: {
                                display: true,
                                labels: {
                                    color: '#000'
                                }
                            }
                        }
                    }
                });




                const dataViewers = {!! $dataViewers !!};

                ctx = document.getElementById("visitsChart").getContext("2d");
                document.getElementById("visitsChart").style.display = "block";

                new Chart(ctx, {
                    type: "line",
                    data: {
                        labels: labels,
                        datasets: [{
                            label: "Monthly Visits",
                            data: dataViewers,
                            borderWidth: 3,
                            borderColor: "#ff0000",
                            backgroundColor: "rgba(255, 0, 0, 0.25)",
                            fill: true,
                            tension: 0.4,
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });

            });
        </script>
    @endpush
</x-app-dashboard-layout>
