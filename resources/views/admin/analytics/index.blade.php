<x-app-dashboard-layout>
    <div class="container-fluid">
        <x-slot name="header">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="mb-3 font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Analytics') }}
                </h2>
            </div>
        </x-slot>

        {{-- Filters --}}
        <div class="card mb-4">
            <div class="card-body">
                <form id="analytics-filters" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label for="show_id" class="form-label text-white">Filter by Show</label>
                        <select id="show_id" class="form-select">
                            <option value="">All Shows</option>
                            @foreach ($liveShows as $show)
                                <option value="{{ $show->id }}">
                                    {{ $show->title }}
                                    ({{ $show->scheduled_at ? $show->scheduled_at->format('d M Y') : 'N/A' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="start_date" class="form-label text-white">Start Date</label>
                        <input type="date" id="start_date" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label for="end_date" class="form-label text-white">End Date</label>
                        <input type="date" id="end_date" class="form-control">
                    </div>
                    <div class="col-md-2">
                        <button type="button" id="apply-filters" class="btn btn-youtube w-100">
                            <i class="bi bi-funnel-fill me-1"></i> Apply
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Stat Cards --}}
        <div class="row g-4 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="stat-card card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon me-3">
                                <i class="bi bi-camera-video-fill"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="stat-number">{{ $totalShows }}</div>
                                <div class="stat-label">Total Shows</div>
                                <div class="stat-change text-info">
                                    <i class="bi bi-check-circle"></i> {{ $completedShows }} completed
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
                                <i class="bi bi-people-fill"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="stat-number">{{ $totalUniquePlayers }}</div>
                                <div class="stat-label">Unique Players</div>
                                <div class="stat-change text-success">
                                    <i class="bi bi-person-check"></i> {{ $totalParticipants }} participated
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
                                <i class="bi bi-graph-up-arrow"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="stat-number">{{ $avgParticipationRate }}%</div>
                                <div class="stat-label">Avg Participation</div>
                                <div class="stat-change text-warning">
                                    <i class="bi bi-bar-chart"></i> across all shows
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
                                <div class="stat-number">{{ $totalWinners }}</div>
                                <div class="stat-label">Total Winners</div>
                                <div class="stat-change text-success">
                                    <i class="bi bi-cash-coin"></i> {{ number_format($totalPrizesAwarded, 2) }} awarded
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main Chart: Total Users vs Participated --}}
        <div class="row g-4 mb-4">
            <div class="col-12">
                <div class="card p-4">
                    <h5 class="text-white mb-3">
                        <i class="bi bi-graph-up me-2"></i>Total Users vs Participated per Show
                    </h5>
                    <div style="position: relative; height: 400px;">
                        <canvas id="participationChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Quiz Accuracy + Avg Response Time --}}
        <div class="row g-4 mb-4">
            <div class="col-lg-7">
                <div class="card p-4">
                    <h5 class="text-white mb-3">
                        <i class="bi bi-check2-square me-2"></i>Quiz Accuracy per Show
                    </h5>
                    <div style="position: relative; height: 350px;">
                        <canvas id="quizAccuracyChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card p-4">
                    <h5 class="text-white mb-3">
                        <i class="bi bi-stopwatch me-2"></i>Avg Response Time (seconds)
                    </h5>
                    <div style="position: relative; height: 350px;">
                        <canvas id="responseTimeChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Top Performers --}}
        <div class="row g-4 mb-4">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0 text-white">
                            <i class="bi bi-star-fill me-2 text-warning"></i>Top 10 Performers
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-dark table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Player</th>
                                        <th>Shows</th>
                                        <th>Total Score</th>
                                        <th>Avg Score</th>
                                        <th>Wins</th>
                                    </tr>
                                </thead>
                                <tbody id="topPerformersBody">
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <div class="spinner-border spinner-border-sm text-danger" role="status"></div>
                                            Loading...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Show Performance Summary --}}
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0 text-white">
                            <i class="bi bi-clipboard-data me-2"></i>Show Performance Summary
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 420px; overflow-y: auto;">
                            <table class="table table-dark table-hover mb-0">
                                <thead style="position: sticky; top: 0; z-index: 1;">
                                    <tr>
                                        <th>Show</th>
                                        <th>Date</th>
                                        <th>Users</th>
                                        <th>Participated</th>
                                        <th>Rate</th>
                                        <th>Winners</th>
                                    </tr>
                                </thead>
                                <tbody id="showSummaryBody">
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <div class="spinner-border spinner-border-sm text-danger" role="status"></div>
                                            Loading...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Participation Rate Doughnut + Score Distribution --}}
        <div class="row g-4 mb-4">
            <div class="col-lg-4">
                <div class="card p-4">
                    <h5 class="text-white mb-3">
                        <i class="bi bi-pie-chart-fill me-2"></i>Overall Participation Breakdown
                    </h5>
                    <div style="position: relative; height: 300px;">
                        <canvas id="participationDoughnut"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="card p-4">
                    <h5 class="text-white mb-3">
                        <i class="bi bi-bar-chart-fill me-2"></i>Average Score per Show
                    </h5>
                    <div style="position: relative; height: 300px;">
                        <canvas id="avgScoreChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            .table-dark {
                --bs-table-bg: var(--card-bg);
                --bs-table-border-color: var(--border-color);
                --bs-table-hover-bg: var(--hover-bg);
                --bs-table-color: var(--text-secondary);
            }

            .table-dark thead th {
                background: var(--darker-bg);
                color: var(--text-primary);
                font-weight: 600;
                font-size: 0.85rem;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                border-bottom: 2px solid var(--youtube-red);
            }

            .badge-rank {
                width: 28px;
                height: 28px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 50%;
                font-weight: 700;
                font-size: 0.8rem;
            }

            .badge-rank.gold { background: linear-gradient(135deg, #FFD700, #FFA500); color: #000; }
            .badge-rank.silver { background: linear-gradient(135deg, #C0C0C0, #A0A0A0); color: #000; }
            .badge-rank.bronze { background: linear-gradient(135deg, #CD7F32, #A0522D); color: #fff; }
            .badge-rank.default { background: var(--hover-bg); color: var(--text-secondary); }

            .participation-bar {
                height: 6px;
                background: var(--hover-bg);
                border-radius: 3px;
                overflow: hidden;
            }

            .participation-bar-fill {
                height: 100%;
                border-radius: 3px;
                transition: width 0.6s ease;
            }
        </style>
    @endpush

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const ROUTES = {
                    chartData: "{{ route('admin.analytics.chart-data') }}",
                    quizAccuracy: "{{ route('admin.analytics.quiz-accuracy') }}",
                    responseTime: "{{ route('admin.analytics.response-time') }}",
                    topPerformers: "{{ route('admin.analytics.top-performers') }}",
                    showSummary: "{{ route('admin.analytics.show-summary') }}",
                };

                let participationChart, quizAccuracyChart, responseTimeChart, participationDoughnut, avgScoreChart;

                function getFilters() {
                    return {
                        show_id: document.getElementById('show_id').value,
                        start_date: document.getElementById('start_date').value,
                        end_date: document.getElementById('end_date').value,
                    };
                }

                function buildQuery(filters) {
                    const params = new URLSearchParams();
                    for (const [key, val] of Object.entries(filters)) {
                        if (val) params.append(key, val);
                    }
                    return params.toString();
                }

                function chartColors() {
                    return {
                        red: '#FF0000',
                        redAlpha: 'rgba(255, 0, 0, 0.15)',
                        green: '#00D25B',
                        greenAlpha: 'rgba(0, 210, 91, 0.15)',
                        blue: '#0078D4',
                        blueAlpha: 'rgba(0, 120, 212, 0.15)',
                        orange: '#FFB900',
                        orangeAlpha: 'rgba(255, 185, 0, 0.15)',
                        gray: '#AAAAAA',
                        grayAlpha: 'rgba(170, 170, 170, 0.15)',
                    };
                }

                const defaultOptions = {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { labels: { color: '#fff', font: { size: 13 } } }
                    },
                    scales: {
                        x: { ticks: { color: '#aaa', maxRotation: 45 }, grid: { color: 'rgba(255,255,255,0.05)' } },
                        y: { beginAtZero: true, ticks: { color: '#aaa' }, grid: { color: 'rgba(255,255,255,0.05)' } }
                    }
                };

                // ===== 1. Participation Chart =====
                function loadParticipationChart() {
                    const q = buildQuery(getFilters());
                    fetch(`${ROUTES.chartData}?${q}`)
                        .then(r => r.json())
                        .then(data => {
                            console.log(data);
                            const c = chartColors();
                            const ctx = document.getElementById('participationChart').getContext('2d');
                            if (participationChart) participationChart.destroy();

                            participationChart = new Chart(ctx, {
                                type: 'line',
                                data: {
                                    labels: data.labels,
                                    datasets: [
                                        {
                                            label: 'Total Users',
                                            data: data.totalUsers,
                                            borderColor: c.blue,
                                            backgroundColor: c.blueAlpha,
                                            borderWidth: 3,
                                            tension: 0.4,
                                            fill: true,
                                            pointRadius: 5,
                                            pointHoverRadius: 8,
                                        },
                                        {
                                            label: 'Participated',
                                            data: data.participated,
                                            borderColor: c.green,
                                            backgroundColor: c.greenAlpha,
                                            borderWidth: 3,
                                            tension: 0.4,
                                            fill: true,
                                            pointRadius: 5,
                                            pointHoverRadius: 8,
                                        },
                                        {
                                            label: 'Not Participated',
                                            data: data.notParticipated,
                                            borderColor: c.red,
                                            backgroundColor: c.redAlpha,
                                            borderWidth: 2,
                                            tension: 0.4,
                                            fill: true,
                                            borderDash: [5, 5],
                                            pointRadius: 4,
                                            pointHoverRadius: 7,
                                        }
                                    ]
                                },
                                options: defaultOptions
                            });

                            // Update doughnut with totals
                            const totalAll = data.totalUsers.reduce((a, b) => a + b, 0);
                            const totalPart = data.participated.reduce((a, b) => a + b, 0);
                            const totalNot = data.notParticipated.reduce((a, b) => a + b, 0);
                            loadDoughnut(totalPart, totalNot);
                        });
                }

                // ===== 2. Quiz Accuracy Chart =====
                function loadQuizAccuracyChart() {
                    const q = buildQuery(getFilters());
                    fetch(`${ROUTES.quizAccuracy}?${q}`)
                        .then(r => r.json())
                        .then(data => {
                            const c = chartColors();
                            const ctx = document.getElementById('quizAccuracyChart').getContext('2d');
                            if (quizAccuracyChart) quizAccuracyChart.destroy();

                            quizAccuracyChart = new Chart(ctx, {
                                type: 'bar',
                                data: {
                                    labels: data.labels,
                                    datasets: [
                                        {
                                            label: 'Correct',
                                            data: data.correct,
                                            backgroundColor: 'rgba(0, 210, 91, 0.8)',
                                            borderRadius: 4,
                                        },
                                        {
                                            label: 'Incorrect',
                                            data: data.incorrect,
                                            backgroundColor: 'rgba(255, 71, 66, 0.8)',
                                            borderRadius: 4,
                                        }
                                    ]
                                },
                                options: {
                                    ...defaultOptions,
                                    scales: {
                                        ...defaultOptions.scales,
                                        x: { ...defaultOptions.scales.x, stacked: true },
                                        y: { ...defaultOptions.scales.y, stacked: true }
                                    }
                                }
                            });
                        });
                }

                // ===== 3. Response Time Chart =====
                function loadResponseTimeChart() {
                    const q = buildQuery(getFilters());
                    fetch(`${ROUTES.responseTime}?${q}`)
                        .then(r => r.json())
                        .then(data => {
                            const ctx = document.getElementById('responseTimeChart').getContext('2d');
                            if (responseTimeChart) responseTimeChart.destroy();

                            responseTimeChart = new Chart(ctx, {
                                type: 'bar',
                                data: {
                                    labels: data.labels,
                                    datasets: [{
                                        label: 'Avg Seconds',
                                        data: data.avgResponseTime,
                                        backgroundColor: 'rgba(255, 185, 0, 0.8)',
                                        borderRadius: 4,
                                    }]
                                },
                                options: {
                                    ...defaultOptions,
                                    indexAxis: 'y',
                                    scales: {
                                        x: { beginAtZero: true, ticks: { color: '#aaa' }, grid: { color: 'rgba(255,255,255,0.05)' } },
                                        y: { ticks: { color: '#aaa', font: { size: 11 } }, grid: { color: 'rgba(255,255,255,0.05)' } }
                                    }
                                }
                            });
                        });
                }

                // ===== 4. Doughnut =====
                function loadDoughnut(participated, notParticipated) {
                    const ctx = document.getElementById('participationDoughnut').getContext('2d');
                    if (participationDoughnut) participationDoughnut.destroy();

                    participationDoughnut = new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: ['Participated', 'Not Participated'],
                            datasets: [{
                                data: [participated, notParticipated],
                                backgroundColor: ['rgba(0, 210, 91, 0.85)', 'rgba(255, 71, 66, 0.85)'],
                                borderColor: ['#00D25B', '#FF4742'],
                                borderWidth: 2,
                                hoverOffset: 8,
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '60%',
                            plugins: {
                                legend: { position: 'bottom', labels: { color: '#fff', padding: 15, font: { size: 13 } } }
                            }
                        }
                    });
                }

                // ===== 5. Top Performers =====
                function loadTopPerformers() {
                    const q = buildQuery(getFilters());
                    fetch(`${ROUTES.topPerformers}?${q}`)
                        .then(r => r.json())
                        .then(data => {
                            const tbody = document.getElementById('topPerformersBody');
                            if (!data.length) {
                                tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-muted">No data available</td></tr>';
                                return;
                            }
                            tbody.innerHTML = data.map((p, i) => {
                                let rankClass = i === 0 ? 'gold' : i === 1 ? 'silver' : i === 2 ? 'bronze' : 'default';
                                return `<tr>
                                    <td><span class="badge-rank ${rankClass}">${i + 1}</span></td>
                                    <td>
                                        <strong class="text-white">${p.name}</strong>
                                        <br><small class="text-muted">${p.email}</small>
                                    </td>
                                    <td>${p.shows_joined}</td>
                                    <td class="text-warning fw-bold">${p.total_score}</td>
                                    <td>${parseFloat(p.avg_score).toFixed(1)}</td>
                                    <td><span class="badge bg-success">${p.wins}</span></td>
                                </tr>`;
                            }).join('');
                        });
                }

                // ===== 6. Show Summary =====
                function loadShowSummary() {
                    const q = buildQuery(getFilters());
                    fetch(`${ROUTES.showSummary}?${q}`)
                        .then(r => r.json())
                        .then(data => {
                            const tbody = document.getElementById('showSummaryBody');
                            if (!data.length) {
                                tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-muted">No data available</td></tr>';
                                return;
                            }

                            // Also build avg score chart data
                            const labels = [];
                            const scores = [];

                            tbody.innerHTML = data.map(s => {
                                labels.push(s.title);
                                scores.push(s.avg_score);

                                let rateColor = s.participation_rate >= 70 ? '#00D25B' :
                                                s.participation_rate >= 40 ? '#FFB900' : '#FF4742';
                                let statusBadge = s.status === 'completed' ? 'bg-success' :
                                                  s.status === 'live' ? 'bg-danger' : 'bg-secondary';
                                return `<tr>
                                    <td>
                                        <strong class="text-white">${s.title}</strong>
                                        <br><span class="badge ${statusBadge}" style="font-size: 0.7rem;">${s.status}</span>
                                    </td>
                                    <td><small>${s.scheduled_at}</small></td>
                                    <td>${s.total_users}</td>
                                    <td>${s.participated}</td>
                                    <td>
                                        <span style="color: ${rateColor}; font-weight: 600;">${s.participation_rate}%</span>
                                        <div class="participation-bar mt-1">
                                            <div class="participation-bar-fill" style="width: ${s.participation_rate}%; background: ${rateColor};"></div>
                                        </div>
                                    </td>
                                    <td><span class="badge bg-warning text-dark">${s.winners}</span></td>
                                </tr>`;
                            }).join('');

                            loadAvgScoreChart(labels, scores);
                        });
                }

                // ===== 7. Avg Score per Show =====
                function loadAvgScoreChart(labels, scores) {
                    const ctx = document.getElementById('avgScoreChart').getContext('2d');
                    if (avgScoreChart) avgScoreChart.destroy();

                    const barColors = scores.map((_, i) => {
                        const hue = (i * 30) % 360;
                        return `hsla(${hue}, 70%, 55%, 0.85)`;
                    });

                    avgScoreChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Avg Score',
                                data: scores,
                                backgroundColor: barColors,
                                borderRadius: 6,
                            }]
                        },
                        options: defaultOptions
                    });
                }

                // ===== Load all on page ready =====
                function loadAll() {
                    loadParticipationChart();
                    loadQuizAccuracyChart();
                    loadResponseTimeChart();
                    loadTopPerformers();
                    loadShowSummary();
                }

                document.getElementById('apply-filters').addEventListener('click', loadAll);

                loadAll();
            });
        </script>
    @endpush
</x-app-dashboard-layout>
