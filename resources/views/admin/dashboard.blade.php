<x-app-dashboard-layout>
    <x-slot name="header">
        <h2 class="mb-3 font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="row g-4 mb-5">
        <div class="col-xl-3 col-md-6">
            <div class="stat-card card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon me-3">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="stat-number">1,258</div>
                            <div class="stat-label">Active Players</div>
                            <div class="stat-change text-success">
                                <i class="bi bi-arrow-up"></i> +12.5% from yesterday
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="stat-card card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon me-3">
                            <i class="bi bi-eye-fill"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="stat-number">5,231</div>
                            <div class="stat-label">Live Viewers</div>
                            <div class="stat-change text-success">
                                <i class="bi bi-arrow-up"></i> +8.3% from last hour
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="stat-card card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon me-3">
                            <i class="bi bi-question-circle-fill"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="stat-number">42</div>
                            <div class="stat-label">Questions Today</div>
                            <div class="stat-change text-info">
                                <i class="bi bi-clock"></i> 3 pending
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
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
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row g-3 mb-5">
        <div class="col-xl-2 col-md-4 col-6">
            <div class="quick-action">
                <i class="bi bi-play-circle-fill"></i>
                <div style="color: var(--text-primary); font-weight: 600;">Start Stream</div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="quick-action">
                <i class="bi bi-plus-circle-fill"></i>
                <div style="color: var(--text-primary); font-weight: 600;">Add Question</div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="quick-action">
                <i class="bi bi-person-plus-fill"></i>
                <div style="color: var(--text-primary); font-weight: 600;">New Player</div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="quick-action">
                <i class="bi bi-bar-chart-fill"></i>
                <div style="color: var(--text-primary); font-weight: 600;">View Analytics</div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="quick-action">
                <i class="bi bi-download"></i>
                <div style="color: var(--text-primary); font-weight: 600;">Export Data</div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="quick-action">
                <i class="bi bi-gear-fill"></i>
                <div style="color: var(--text-primary); font-weight: 600;">Settings</div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row g-4 mb-5">
        <div class="col-lg-8">
            <div class="content-card card">
                <div class="card-header">
                    <h4 class="card-title">Player Engagement Over Time</h4>
                </div>
                <div class="card-body">
                    <div class="chart-placeholder">
                        <div class="text-center">
                            <i class="bi bi-graph-up" style="font-size: 3rem; color: var(--youtube-red);"></i>
                            <div class="mt-2">Real-time Player Engagement Chart</div>
                            <small style="color: var(--text-secondary);">Chart data will appear
                                here</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="content-card card">
                <div class="card-header">
                    <h4 class="card-title">Answer Accuracy</h4>
                </div>
                <div class="card-body">
                    <div class="chart-placeholder">
                        <div class="text-center">
                            <i class="bi bi-pie-chart-fill" style="font-size: 3rem; color: var(--youtube-red);"></i>
                            <div class="mt-2">Correct vs Incorrect</div>
                            <small style="color: var(--text-secondary);">73% accuracy rate</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
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
    </div>
</x-app-dashboard-layout>
