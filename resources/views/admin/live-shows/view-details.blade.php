<x-app-dashboard-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 py-3 mb-1">
            {{ __('Live Show Details') }}
            <a href="{{ route('admin.live-shows.index') }}" class="btn btn-secondary btn-sm mx-2">
                <i class="fas fa-arrow-left me-1"></i> Back to List
            </a>
            <a href="{{ route('admin.live-show-quizzes.index', ['live_show_id' => $liveShow->id]) }}"
                class="btn btn-warning btn-sm text-white">
                <i class="fas fa-question-circle me-1"></i> View Quiz Questions
            </a>
            <a href="{{ route('admin.live-shows.stream-management', $liveShow->id) }}"
                class="btn btn-primary btn-sm ms-1">
                <i class="fas fa-cog me-1"></i> Stream Management
            </a>
            <a href="{{ route('admin.live-shows.gallery-attach', $liveShow->id) }}"
                class="btn btn-secondary btn-sm ms-1">
                <i class="fas fa-images me-1"></i> Gallery Media
            </a>
            <a href="{{ route('admin.live-shows.edit', $liveShow->id) }}" class="btn btn-secondary btn-sm ms-1">
                <i class="fas fa-edit me-1"></i> Edit
            </a>

        </h2>
    </x-slot>

    <div class="py-4">
        <div class="container-fluid">

            {{-- Show Info Card --}}
            <div class="card mb-4 shadow-sm border-0">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><i class="fas fa-broadcast-tower me-2"></i>{{ $liveShow->title }}</h4>
                    <div>
                        @if ($liveShow->status === 'live')
                            <span class="badge bg-danger fs-6">LIVE</span>
                        @elseif($liveShow->status === 'completed')
                            <span class="badge bg-success fs-6">Completed</span>
                        @else
                            <span class="badge bg-warning text-dark fs-6">{{ ucfirst($liveShow->status) }}</span>
                        @endif
                        @if ($liveShow->is_test_show)
                            <span class="badge bg-danger fs-6 ms-1">Test Show</span>
                        @endif
                    </div>
                </div>
                <div class="card-body bg-dark text-light">
                    @if ($liveShow->description)
                        <p class="text-secondary mb-3">{{ $liveShow->description }}</p>
                    @endif
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="border border-secondary rounded p-3 h-100">
                                        <small class="text-secondary d-block mb-1">Host</small>
                                        <strong>{{ $liveShow->host_name ?? 'TBA' }}</strong>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="border border-secondary rounded p-3 h-100">
                                        <small class="text-secondary d-block mb-1">Scheduled At</small>
                                        <strong>{{ $liveShow->scheduled_at ? $liveShow->scheduled_at->format('d M Y, H:i') : 'N/A' }}</strong>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="border border-secondary rounded p-3 h-100">
                                        <small class="text-secondary d-block mb-1">Started At</small>
                                        <strong>{{ $liveShow->start_time ? $liveShow->start_time->format('d M Y, H:i:s') : 'Not started yet' }}</strong>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="border border-secondary rounded p-3 h-100">
                                        <small class="text-secondary d-block mb-1">Ended At</small>
                                        <strong>{{ $liveShow->end_time ? $liveShow->end_time->format('d M Y, H:i:s') : 'Not ended yet' }}</strong>
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <div class="border border-secondary rounded p-3 h-100">
                                        <small class="text-secondary d-block mb-1">Prize Pool</small>
                                        <strong class="text-warning">{{ number_format($liveShow->prize_amount, 2) }}
                                            {{ $liveShow->currency }}</strong>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="border border-secondary rounded p-3 h-100">
                                        <small class="text-secondary d-block mb-1">Max Winners</small>
                                        <strong>{{ $liveShow->max_winners }}</strong>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="border border-secondary rounded p-3 h-100">
                                        <small class="text-secondary d-block mb-1">Winners announced</small>
                                        @if (!empty($liveShow->winners_announced))
                                            <strong class="text-success">Yes</strong>
                                            <span class="d-block small text-muted mt-1">Winner emails are queued per
                                                your schedule.</span>
                                        @else
                                            <strong class="text-warning">No</strong>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="border border-secondary rounded p-3 h-100">
                                        <small class="text-secondary d-block mb-1">Total Players</small>
                                        <strong id="totalPlayersCount">-</strong>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="border border-secondary rounded p-3 h-100">
                                        <small class="text-secondary d-block mb-1">Total Questions</small>
                                        <strong>{{ $totalQuestions }}</strong>
                                        @if ($specialQuestionsCount > 0)
                                            <div class="small text-warning mt-1">
                                                {{ $specialQuestionsCount }} special quiz question(s)
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                @if ($specialQuestionsCount > 0)
                                    <div class="col-md-6 mb-3">
                                        <div class="border border-warning rounded p-3 h-100">
                                            <small class="text-secondary d-block mb-1">Special Max Winners</small>
                                            <strong>{{ $liveShow->special_max_winners }}</strong>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="border border-warning rounded p-3 h-100">
                                            <small class="text-secondary d-block mb-1">Special winners announced</small>
                                            @if (!empty($liveShow->special_winners_announced))
                                                <strong class="text-success">Yes</strong>
                                            @else
                                                <strong class="text-warning">No</strong>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                @if ($liveShow->start_time && $liveShow->end_time)
                                    <div class="col-md-6 mb-3">
                                        <div class="border border-secondary rounded p-3 h-100">
                                            <small class="text-secondary d-block mb-1">Duration</small>
                                            <strong>{{ $liveShow->start_time->diff($liveShow->end_time)->format('%H:%I:%S') }}</strong>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            {{-- Participation Stats --}}
                            <div class="row mb-3">
                                <div class="col-6">
                                    <div class="border border-secondary rounded p-3 h-100 text-center">
                                        <small class="text-secondary d-block mb-1">Played</small>
                                        <strong class="text-success fs-4"><span id="playedCount">-</span> /
                                            <span id="playedTotalCount">-</span></strong>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="border border-secondary rounded p-3 h-100 text-center">
                                        <small class="text-secondary d-block mb-1">Did Not Participate</small>
                                        <strong class="text-danger fs-4"><span id="notParticipatedCount">-</span> /
                                            <span id="notParticipatedTotalCount">-</span></strong>
                                    </div>
                                </div>
                            </div>

                            {{-- Winner Prize Distribution Table --}}
                            @if ($liveShow->winnerPrizes->count())
                                <div class="mt-2">
                                    <small class="text-secondary d-block mb-2">Winner Prize Distribution</small>
                                    <div style="max-height: 200px; overflow-y: auto;">
                                        <table class="table table-sm table-dark table-bordered mb-0">
                                            <thead class="sticky-top" style="top: 0; z-index: 1;">
                                                <tr>
                                                    <th>Rank</th>
                                                    <th>Prize</th>
                                                    <th>Voucher Code</th>
                                                    
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($liveShow->winnerPrizes as $wp)
                                                    <tr>
                                                        <td><span class="badge bg-secondary">{{ $wp->rank }}</span>
                                                        </td>
                                                        <td>{{ $wp->prize }}</td>
                                                        <td>{{ $wp->discount_code ?? '--' }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif

                            @if ($specialQuestionsCount > 0 && $liveShow->specialGifts->count())
                                <div class="mt-2">
                                    <small class="text-warning d-block mb-2">Special Quiz Gift Distribution</small>
                                    <div style="max-height: 200px; overflow-y: auto;">
                                        <table class="table table-sm table-dark table-bordered mb-0">
                                            <thead class="sticky-top" style="top: 0; z-index: 1;">
                                                <tr>
                                                    <th>Rank</th>
                                                    <th>Gift</th>
                                                    <th>Type</th>
                                                    <th>Value</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($liveShow->specialGifts as $gift)
                                                    <tr>
                                                        <td><span class="badge bg-warning text-dark">{{ $gift->rank }}</span></td>
                                                        <td>{{ $gift->name }}</td>
                                                        <td>{{ ucfirst($gift->type) }}</td>
                                                        <td>{{ $gift->value ?? ($gift->voucher_amount ? $gift->voucher_amount.' voucher' : '--') }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif


                        </div>
                    </div>
                </div>
            </div>



            {{-- Split Panel: Players Left, Responses Right --}}
            <div class="row" id="detailsContainer">
                {{-- Left Panel: Players List --}}
                <div class="col-lg-5 col-md-6 mb-3" id="playersPanel">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="text-white mb-0"><i class="fas fa-users me-2"></i>Players & Responses</h5>
                        <div class="w-100">
                            <a href="{{ route('admin.live-shows.export-participants-csv', $liveShow->id) }}"
                                class="btn btn-success btn-sm">
                                <i class="fas fa-file-csv me-1"></i> Export All Participants CSV
                            </a>
                            <a href="{{ route('admin.live-shows.export-winners-csv', $liveShow->id) }}"
                                class="btn btn-primary btn-sm ms-2">
                                <i class="fas fa-trophy me-1"></i> Export Winners CSV
                            </a>
                            @if ($specialQuestionsCount > 0)
                                <a href="{{ route('admin.live-shows.export-special-winners-csv', $liveShow->id) }}"
                                    class="btn btn-warning btn-sm text-dark ms-2">
                                    <i class="fas fa-gift me-1"></i> Export Special Winners CSV
                                </a>
                            @endif
                            <button type="button" class="btn btn-secondary btn-sm ms-2" id="refreshPlayersBtn">
                                <span id="refreshPlayersBtnIcon">
                                    <i class="fas fa-sync me-1"></i>
                                </span>
                                Refresh
                            </button>
                        </div>
                    </div>


                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-secondary text-white p-0">
                            <ul class="nav nav-tabs border-0" id="detailsPlayerRankingTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active text-white fw-semibold" id="detailsMainRankingTabBtn"
                                        data-bs-toggle="tab" data-bs-target="#detailsMainRankingTab" type="button"
                                        role="tab">
                                        <i class="fas fa-trophy me-1"></i> Main Quiz
                                    </button>
                                </li>
                                @if ($specialQuestionsCount > 0)
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link text-white fw-semibold" id="detailsSpecialRankingTabBtn"
                                            data-bs-toggle="tab" data-bs-target="#detailsSpecialRankingTab" type="button"
                                            role="tab">
                                            <i class="fas fa-star me-1 text-warning"></i> Special Quiz
                                        </button>
                                    </li>
                                @endif
                            </ul>
                        </div>
                        <div
                            class="card-header bg-secondary text-white d-flex justify-content-between align-items-center border-top border-dark">
                            <span><i class="fas fa-users me-1"></i> Players (<span
                                    id="playersListCount">-</span>)</span>
                            <input type="text" id="playerSearch"
                                class="form-control form-control-sm bg-dark text-light border-secondary"
                                style="max-width: 200px;" placeholder="Search players...">
                        </div>
                        <div class="card-body p-0 bg-dark" style="max-height: 75vh; overflow-y: auto;">
                            <div class="tab-content" id="detailsPlayerRankingTabContent">
                                <div class="tab-pane fade show active" id="detailsMainRankingTab" role="tabpanel">
                                    <div id="playersLoading" class="p-4 text-center text-secondary">
                                        <div class="spinner-border spinner-border-sm text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <p class="mt-2 mb-0">Loading players...</p>
                                    </div>
                                    <div class="list-group list-group-flush" id="playersList" style="display:none;"></div>
                                    <div id="playersEmpty" class="p-4 text-center text-secondary" style="display:none;">
                                        <i class="fas fa-user-slash fa-2x mb-2"></i>
                                        <p>No participants joined this show.</p>
                                    </div>
                                </div>
                                @if ($specialQuestionsCount > 0)
                                    <div class="tab-pane fade" id="detailsSpecialRankingTab" role="tabpanel">
                                        <div id="specialPlayersLoading" class="p-4 text-center text-secondary">
                                            <div class="spinner-border spinner-border-sm text-warning" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                            <p class="mt-2 mb-0">Loading special quiz players...</p>
                                        </div>
                                        <div class="list-group list-group-flush" id="specialPlayersList" style="display:none;"></div>
                                        <div id="specialPlayersEmpty" class="p-4 text-center text-secondary" style="display:none;">
                                            <i class="fas fa-user-slash fa-2x mb-2"></i>
                                            <p>No special quiz participants found.</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Right Panel: Response Details --}}
                <div class="col-lg-7 col-md-6 mb-3" id="responsesPanel">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center"
                            id="responsesHeader">
                            <span><i class="fas fa-clipboard-list me-1"></i> <span id="responsesTitle">Select a player
                                    to view responses</span></span>
                            <div id="responsesActions" style="display:none;">
                                <a href="#" id="exportPlayerCsvBtn" class="btn btn-success btn-sm">
                                    <i class="fas fa-file-csv me-1"></i> Export Player CSV
                                </a>
                            </div>
                        </div>
                        <div class="card-body bg-dark text-light" style="max-height: 75vh; overflow-y: auto;"
                            id="responsesBody">
                            {{-- Player Summary (shown when a player is selected) --}}
                            <div id="playerSummary" style="display:none;" class="mb-3">
                                <div class="row mb-3">
                                    <div class="col-sm-6">
                                        <div class="border border-secondary rounded p-3">
                                            <h5 class="mb-1" id="summaryName"></h5>
                                            <small class="text-secondary" id="summaryEmail"></small>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="d-flex gap-2 flex-wrap h-100 align-items-center">
                                            <span class="badge bg-primary py-2 px-3" id="summaryScore"></span>
                                            <span class="badge py-2 px-3" id="summaryWinner"></span>
                                            <span class="badge bg-secondary py-2 px-3" id="summaryStatus"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-4">
                                        <div class="border border-secondary rounded p-2 text-center">
                                            <small class="text-secondary d-block">Correct</small>
                                            <strong class="text-success" id="summaryCorrect">-</strong>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="border border-secondary rounded p-2 text-center">
                                            <small class="text-secondary d-block">Wrong</small>
                                            <strong class="text-danger" id="summaryWrong">-</strong>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="border border-secondary rounded p-2 text-center">
                                            <small class="text-secondary d-block">Avg Time</small>
                                            <strong class="text-info" id="summaryAvgTime">-</strong>
                                        </div>
                                    </div>
                                </div>
                                <hr class="border-secondary">
                            </div>

                            {{-- Empty State --}}
                            <div id="emptyState" class="text-center py-5">
                                <i class="fas fa-hand-pointer fa-3x text-secondary mb-3"></i>
                                <p class="text-secondary fs-5">Click on a player from the left panel<br>to view their
                                    response details.</p>
                            </div>

                            {{-- Loading State --}}
                            <div id="loadingState" style="display:none;" class="text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="text-secondary mt-2">Loading responses...</p>
                            </div>

                            {{-- Responses Table --}}
                            <div id="responsesContent" style="display:none;">
                                <div class="table-responsive">
                                    <table class="table table-dark table-borderless table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Question</th>
                                                <th>Selected Answer</th>
                                                <th>Correct Answer</th>
                                                <th>Answered At</th>

                                                <th>Result</th>
                                                <th>Time</th>
                                                <th>Score</th>
                                            </tr>
                                        </thead>
                                        <tbody id="responsesTableBody">
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            {{-- No Responses State --}}
                            <div id="noResponsesState" style="display:none;" class="text-center py-5">
                                <i class="fas fa-inbox fa-3x text-secondary mb-3"></i>
                                <p class="text-secondary fs-5">This player has no quiz responses.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const hasSpecialQuiz = {{ $specialQuestionsCount > 0 ? 'true' : 'false' }};
                const liveShowId = {{ $liveShow->id }};

                const playersList = document.getElementById('playersList');
                const playersLoading = document.getElementById('playersLoading');
                const playersEmpty = document.getElementById('playersEmpty');
                const specialPlayersList = document.getElementById('specialPlayersList');
                const specialPlayersLoading = document.getElementById('specialPlayersLoading');
                const specialPlayersEmpty = document.getElementById('specialPlayersEmpty');
                const playersListCount = document.getElementById('playersListCount');
                const emptyState = document.getElementById('emptyState');
                const loadingState = document.getElementById('loadingState');
                const responsesContent = document.getElementById('responsesContent');
                const noResponsesState = document.getElementById('noResponsesState');
                const responsesTableBody = document.getElementById('responsesTableBody');
                const responsesTitle = document.getElementById('responsesTitle');
                const responsesActions = document.getElementById('responsesActions');
                const exportPlayerCsvBtn = document.getElementById('exportPlayerCsvBtn');
                const playerSummary = document.getElementById('playerSummary');
                const playerSearch = document.getElementById('playerSearch');
                const refreshPlayersBtn = document.getElementById('refreshPlayersBtn');

                let activePlayerId = null;
                let activeRankingMode = 'main';
                let specialPlayersLoaded = false;
                let searchTimeout = null;

                function getActiveListElements() {
                    if (activeRankingMode === 'special' && hasSpecialQuiz) {
                        return {
                            list: specialPlayersList,
                            loading: specialPlayersLoading,
                            empty: specialPlayersEmpty,
                        };
                    }

                    return {
                        list: playersList,
                        loading: playersLoading,
                        empty: playersEmpty,
                    };
                }

                function getPlayersApiUrl(search) {
                    const query = new URLSearchParams({
                        skip: 0,
                        take: 1000,
                        search: search,
                    });
                    const endpoint = activeRankingMode === 'special'
                        ? `{{ url('api/live-show') }}/${liveShowId}/get-special-live-show-users`
                        : `{{ url('api/live-show') }}/${liveShowId}/get-live-show-users`;

                    return `${endpoint}?${query.toString()}`;
                }

                function getResponsesApiUrl(userId) {
                    const base = "{{ url('admin/live-shows') }}/" + liveShowId;
                    return activeRankingMode === 'special'
                        ? `${base}/special-player-responses/${userId}`
                        : `${base}/player-responses/${userId}`;
                }

                function escapeHtml(text) {
                    var div = document.createElement('div');
                    div.appendChild(document.createTextNode(text ?? ''));
                    return div.innerHTML;
                }

                function updatePlayerStats(data) {
                    const total = data.totalUsers ?? 0;
                    const played = data.playedCount ?? 0;
                    const notParticipated = data.notParticipatedCount ?? 0;
                    const displayCount = data.filteredUsers ?? total;

                    document.getElementById('totalPlayersCount').textContent = total;
                    document.getElementById('playedCount').textContent = played;
                    document.getElementById('playedTotalCount').textContent = total;
                    document.getElementById('notParticipatedCount').textContent = notParticipated;
                    document.getElementById('notParticipatedTotalCount').textContent = total;
                    playersListCount.textContent = displayCount;
                }

                function buildPlayerItem(player, index) {
                    const isWinner = !!player.is_winner;
                    const score = player.score ?? 0;
                    const prizeWon = escapeHtml(player.prize_won ?? '');
                    const status = escapeHtml(player.status ?? '');
                    const joinedAt = escapeHtml(player.joined_at ?? 'N/A');
                    const winnerIcon = activeRankingMode === 'special'
                        ? '<i class="fas fa-gift text-warning ms-1" title="Special Winner"></i>'
                        : '<i class="fas fa-crown text-warning ms-1" title="Winner"></i>';
                    const scoreBadgeClass = activeRankingMode === 'special' ? 'bg-warning text-dark' : 'bg-primary';

                    return `<a href="javascript:void(0)"
                        class="list-group-item list-group-item-action bg-dark text-light border-secondary player-item"
                        data-user-id="${player.id}"
                        data-user-name="${escapeHtml(player.name)}"
                        data-user-email="${escapeHtml(player.email)}"
                        data-user-score="${score}"
                        data-user-is-winner="${isWinner ? '1' : '0'}"
                        data-user-prize="${escapeHtml(player.prize_won ?? 'N/A')}"
                        data-user-status="${status}"
                        data-user-joined="${joinedAt}">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <span class="badge bg-secondary me-2" style="min-width:30px;">${index}</span>
                                <div>
                                    <strong>${escapeHtml(player.name)}</strong>
                                    ${isWinner ? winnerIcon : ''}
                                    <br>
                                    <small class="text-secondary">${escapeHtml(player.email)}</small>
                                </div>
                            </div>
                            <div class="text-end">
                                <span class="badge ${scoreBadgeClass}">${score} pts</span>
                                ${isWinner ? `<br><small class="text-warning">${prizeWon}</small>` : ''}
                            </div>
                        </div>
                    </a>`;
                }

                function renderPlayersList(data) {
                    const elements = getActiveListElements();
                    elements.loading.style.display = 'none';

                    if (!data.users || data.users.length === 0) {
                        elements.list.style.display = 'none';
                        elements.empty.style.display = 'block';
                        return;
                    }

                    elements.empty.style.display = 'none';
                    elements.list.style.display = 'block';
                    elements.list.innerHTML = data.users.map(function(player, index) {
                        return buildPlayerItem(player, index + 1);
                    }).join('');
                }

                function clearResponsesPanel() {
                    activePlayerId = null;
                    responsesTitle.textContent = 'Select a player to view responses';
                    responsesActions.style.display = 'none';
                    emptyState.style.display = 'block';
                    loadingState.style.display = 'none';
                    responsesContent.style.display = 'none';
                    noResponsesState.style.display = 'none';
                    playerSummary.style.display = 'none';
                }

                function fetchPlayers(search = '') {
                    const elements = getActiveListElements();
                    elements.loading.style.display = 'block';
                    elements.list.style.display = 'none';
                    elements.empty.style.display = 'none';

                    return fetch(getPlayersApiUrl(search))
                        .then(function(res) {
                            return res.json();
                        })
                        .then(function(data) {
                            updatePlayerStats(data);
                            renderPlayersList(data);
                            if (activeRankingMode === 'special') {
                                specialPlayersLoaded = true;
                            }
                            return data;
                        })
                        .catch(function(err) {
                            elements.loading.style.display = 'none';
                            elements.empty.style.display = 'block';
                            elements.empty.querySelector('p').textContent = 'Failed to load players.';
                            console.error('Failed to load players:', err);
                        });
                }

                function bindPlayerListClick(listEl) {
                    if (!listEl) return;

                    listEl.addEventListener('click', function(e) {
                        const item = e.target.closest('.player-item');
                        if (!item) return;

                        const userId = item.dataset.userId;
                        const userName = item.dataset.userName;
                        const userEmail = item.dataset.userEmail;
                        const userScore = item.dataset.userScore;
                        const isWinner = item.dataset.userIsWinner === '1';
                        const prizWon = item.dataset.userPrize;
                        const userStatus = item.dataset.userStatus;

                        [playersList, specialPlayersList].forEach(function(list) {
                            if (!list) return;
                            list.querySelectorAll('.player-item').forEach(function(el) {
                                el.classList.remove('active');
                            });
                        });
                        item.classList.add('active');

                        activePlayerId = userId;

                        const quizLabel = activeRankingMode === 'special' ? 'Special Quiz' : 'Main Quiz';
                        responsesTitle.textContent = userName + "'s " + quizLabel + ' Responses';
                        responsesActions.style.display = 'block';
                        exportPlayerCsvBtn.href =
                            "{{ url('admin/live-shows') }}/" + liveShowId + "/export-player-csv/" + userId;

                        document.getElementById('summaryName').textContent = userName;
                        document.getElementById('summaryEmail').textContent = userEmail;
                        document.getElementById('summaryScore').textContent = userScore + ' pts';

                        const winnerBadge = document.getElementById('summaryWinner');
                        if (isWinner) {
                            winnerBadge.textContent = (activeRankingMode === 'special' ? 'Special Winner - ' : 'Winner - ') + prizWon;
                            winnerBadge.className = 'badge bg-warning text-dark py-2 px-3';
                        } else {
                            winnerBadge.textContent = 'Participant';
                            winnerBadge.className = 'badge bg-info py-2 px-3';
                        }
                        document.getElementById('summaryStatus').textContent = userStatus.charAt(0)
                            .toUpperCase() + userStatus.slice(1);

                        emptyState.style.display = 'none';
                        noResponsesState.style.display = 'none';
                        responsesContent.style.display = 'none';
                        playerSummary.style.display = 'none';
                        loadingState.style.display = 'block';

                        fetch(getResponsesApiUrl(userId))
                            .then(function(res) {
                                return res.json();
                            })
                            .then(function(data) {
                                loadingState.style.display = 'none';
                                playerSummary.style.display = 'block';

                                if (!data.responses || data.responses.length === 0) {
                                    noResponsesState.style.display = 'block';
                                    document.getElementById('summaryCorrect').textContent = '0';
                                    document.getElementById('summaryWrong').textContent = '0';
                                    document.getElementById('summaryAvgTime').textContent = '-';
                                    return;
                                }

                                var correctCount = 0;
                                var wrongCount = 0;
                                var totalTime = 0;

                                responsesTableBody.innerHTML = '';
                                data.responses.forEach(function(resp, idx) {
                                    if (resp.is_correct) correctCount++;
                                    else wrongCount++;
                                    totalTime += parseFloat(resp.seconds_to_submit) || 0;

                                    var correctOpt = resp.options.find(function(o) {
                                        return o.is_correct;
                                    });
                                    var correctText = correctOpt ? correctOpt.option_text :
                                        'N/A';

                                    var row = '<tr>' +
                                        '<td>' + (idx + 1) + '</td>' +
                                        '<td>' + escapeHtml(resp.question) + '</td>' +
                                        '<td>' +
                                        '<span class="' + (resp.is_correct ?
                                            'text-success' : 'text-danger') + '">' +
                                        escapeHtml(resp.selected_option) +
                                        '</span>' +
                                        '</td>' +
                                        '<td class="text-success">' + escapeHtml(
                                            correctText) + '</td>' +
                                        '<td>' + escapeHtml(resp.answered_at) + '</td>' +
                                        '<td>' +
                                        (resp.is_correct ?
                                            '<span class="badge bg-success"><i class="fas fa-check"></i> Correct</span>' :
                                            '<span class="badge bg-danger"><i class="fas fa-times"></i> Wrong</span>'
                                        ) +
                                        '</td>' +
                                        '<td>' + (resp.seconds_to_submit !== null ?
                                            parseFloat(resp.seconds_to_submit).toFixed(2) +
                                            's' : '-') + '</td>' +
                                        '<td>' + (resp.response_score !== null ? parseFloat(
                                            resp.response_score).toFixed(2) : '-') +
                                        '</td>' +
                                        '</tr>';

                                    responsesTableBody.insertAdjacentHTML('beforeend', row);
                                });

                                document.getElementById('summaryCorrect').textContent =
                                    correctCount + ' / ' + data.responses.length;
                                document.getElementById('summaryWrong').textContent = wrongCount +
                                    ' / ' + data.responses.length;
                                var avgTime = data.responses.length > 0 ? (totalTime / data
                                    .responses.length).toFixed(2) + 's' : '-';
                                document.getElementById('summaryAvgTime').textContent = avgTime;

                                responsesContent.style.display = 'block';
                            })
                            .catch(function(err) {
                                loadingState.style.display = 'none';
                                noResponsesState.style.display = 'block';
                                console.error('Failed to load responses:', err);
                            });
                    });
                }

                playerSearch.addEventListener('input', function() {
                    clearTimeout(searchTimeout);
                    const query = this.value.trim();
                    searchTimeout = setTimeout(function() {
                        fetchPlayers(query);
                    }, 300);
                });

                refreshPlayersBtn.addEventListener('click', function() {
                    const icon = this.querySelector('#refreshPlayersBtnIcon');
                    this.disabled = true;
                    icon.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

                    if (activeRankingMode === 'special') {
                        specialPlayersLoaded = false;
                    }

                    fetchPlayers(playerSearch.value.trim()).finally(function() {
                        refreshPlayersBtn.disabled = false;
                        icon.innerHTML = '<i class="fas fa-sync me-1"></i>';
                    });
                });

                bindPlayerListClick(playersList);
                if (hasSpecialQuiz) {
                    bindPlayerListClick(specialPlayersList);

                    const specialTabBtn = document.getElementById('detailsSpecialRankingTabBtn');
                    if (specialTabBtn) {
                        specialTabBtn.addEventListener('shown.bs.tab', function() {
                            activeRankingMode = 'special';
                            clearResponsesPanel();
                            if (!specialPlayersLoaded) {
                                fetchPlayers(playerSearch.value.trim());
                            } else {
                                updatePlayerStats({ filteredUsers: specialPlayersList.querySelectorAll('.player-item').length });
                            }
                        });
                    }

                    const mainTabBtn = document.getElementById('detailsMainRankingTabBtn');
                    if (mainTabBtn) {
                        mainTabBtn.addEventListener('shown.bs.tab', function() {
                            activeRankingMode = 'main';
                            clearResponsesPanel();
                            fetchPlayers(playerSearch.value.trim());
                        });
                    }
                }

                fetchPlayers();
            });
        </script>
    @endpush

    @push('styles')
        <style>
            .player-item:hover {
                background-color: #2c3034 !important;
            }

            .player-item.active {
                background-color: #0d6efd !important;
                border-color: #0d6efd !important;
            }

            #detailsPlayerRankingTabs .nav-link {
                color: rgba(255, 255, 255, 0.75);
                border: none;
                border-radius: 0;
            }

            #detailsPlayerRankingTabs .nav-link.active {
                color: #fff;
                background-color: rgba(255, 255, 255, 0.1);
            }

            #responsesBody::-webkit-scrollbar,
            #playersPanel .card-body::-webkit-scrollbar {
                width: 6px;
            }

            #responsesBody::-webkit-scrollbar-track,
            #playersPanel .card-body::-webkit-scrollbar-track {
                background: #1a1d21;
            }

            #responsesBody::-webkit-scrollbar-thumb,
            #playersPanel .card-body::-webkit-scrollbar-thumb {
                background: #495057;
                border-radius: 3px;
            }
        </style>
    @endpush
</x-app-dashboard-layout>
