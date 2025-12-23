<x-app-dashboard-layout>
    <div class="container">
        <x-slot name="header">
            <h2 class="mb-3 font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Dashboard') }}
            </h2>
        </x-slot>

        <div class="row g-4 mb-5">
            <div class="col-xl-4 col-md-6">
                <div class="stat-card card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon me-3">
                                <i class="bi bi-people-fill"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="stat-number">
                                    {{ $activePlayers }}
                                </div>
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

            <div class="col-xl-4 col-md-6">
                <div class="stat-card card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon me-3">
                                <i class="bi bi-eye-fill"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="stat-number">{{ $totalViewers }}</div>
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

            <div class="col-xl-4 col-md-6">
                <div class="stat-card card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon me-3">
                                <i class="bi bi-question-circle-fill"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="stat-number">
                                    {{ $totalLiveQuizShows }}
                                </div>
                                <div class="stat-label">Live Shows</div>
                                <div class="stat-change text-info">
                                    <i class="bi bi-clock"></i> {{ $totalScheduledLiveQuizShows }} pending
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- <div class="col-xl-3 col-md-6">
            <div class="stat-card card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon me-3">
                            <i class="bi bi-trophy-fill"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="stat-number">89</div>
                            <div class="stat-label">Winners Today</div>
                            <div class="stat-change text-warning">
                                <i class="bi bi-dash"></i> -2.1% from yesterday
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div> --}}
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

            {{-- <div class="col-xl-2 col-md-4 col-6">
                <a href="{{ route('admin.live-shows.index') }}" class="text-decoration-none">
                    <div class="quick-action">
                        <i class="bi bi-bar-chart-fill"></i>
                        <div style="color: var(--text-primary); font-weight: 600;">View Analytics</div>
                    </div>
                </a>
            </div>
            <div class="col-xl-2 col-md-4 col-6">
                <div class="quick-action">
                    <i class="bi bi-download"></i>
                    <div style="color: var(--text-primary); font-weight: 600;">Export Data</div>
                </div>
            </div> --}}
            <div class="col-xl-2 col-md-4 col-6">
                <a href="{{ route('admin.password.form') }}" class="text-decoration-none">
                    <div class="quick-action">
                        <i class="bi bi-gear-fill"></i>
                        <div style="color: var(--text-primary); font-weight: 600;">Settings</div>
                    </div>
                </a>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="row g-4 mb-5">
            <div class="col-lg-6">
                <h4 class="mb-3">User Signups Over Time</h4>
                <div class="content-card card p-3">
                    <canvas id="signupChart" style="display: none; height: 350px;"></canvas>
                </div>
            </div>
            <div class="col-lg-6">
                <h4 class="mb-3">Views Over Time</h4>
                <div class="card p-3">

                    <canvas id="visitsChart" style="display: none; height: 350px;"></canvas>

                </div>
            </div>


        </div>
    </div>
    {{-- <!-- Recent Activity -->
    <div class="row">
        <div class="col-12">
            <div class="content-card card">
                <div class="card-header">
                    <h4 class="card-title">Real-time Activity Feed</h4>
                </div>
                <div class="card-body">
                    <div class="activity-item">
                        <h5><i class="bi bi-person-plus-fill me-2" style="color: var(--success);"></i>New
                            Player Registration</h5>
                        <p>john.doe@example.com just joined the game show</p>
                        <div class="activity-time">2 minutes ago</div>
                    </div>

                    <div class="activity-item">
                        <h5><i class="bi bi-check-circle-fill me-2" style="color: var(--success);"></i>Question
                            Answered Correctly</h5>
                        <p>sarah.smith@example.com answered "What is the capital of France?" correctly in
                            8.2 seconds</p>
                        <div class="activity-time">5 minutes ago</div>
                    </div>

                    <div class="activity-item">
                        <h5><i class="bi bi-trophy-fill me-2" style="color: var(--warning);"></i>Winner
                            Declared</h5>
                        <p>mike.wilson@example.com won Round #12 with a score of 950 points</p>
                        <div class="activity-time">15 minutes ago</div>
                    </div>

                    <div class="activity-item">
                        <h5><i class="bi bi-camera-video-fill me-2" style="color: var(--youtube-red);"></i>Live Stream
                            Update</h5>
                        <p>Stream reached 5,000+ concurrent viewers milestone</p>
                        <div class="activity-time">30 minutes ago</div>
                    </div>

                    <div class="activity-item">
                        <h5><i class="bi bi-x-circle-fill me-2" style="color: var(--danger);"></i>Incorrect
                            Answer</h5>
                        <p>alex.brown@example.com answered incorrectly - eliminated from current round</p>
                        <div class="activity-time">45 minutes ago</div>
                    </div>
                </div>
            </div>
        </div>
    </div> --}}


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
