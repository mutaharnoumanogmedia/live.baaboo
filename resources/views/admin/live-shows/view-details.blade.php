<x-app-dashboard-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 py-3 mb-1">
            {{ __('Live Show Details') }}
            <a href="{{ route('admin.live-shows.index') }}" class="btn btn-outline-secondary btn-sm mx-2">
                <i class="fas fa-arrow-left me-1"></i> Back to List
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
                        @if($liveShow->status === 'live')
                            <span class="badge bg-danger fs-6">LIVE</span>
                        @elseif($liveShow->status === 'completed')
                            <span class="badge bg-success fs-6">Completed</span>
                        @else
                            <span class="badge bg-warning text-dark fs-6">{{ ucfirst($liveShow->status) }}</span>
                        @endif
                        @if($liveShow->is_test_show)
                            <span class="badge bg-info fs-6 ms-1">Test Show</span>
                        @endif
                    </div>
                </div>
                <div class="card-body bg-dark text-light">
                    @if($liveShow->description)
                        <p class="text-secondary mb-3">{{ $liveShow->description }}</p>
                    @endif
                    <div class="row">
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="border border-secondary rounded p-3 h-100">
                                <small class="text-secondary d-block mb-1">Host</small>
                                <strong>{{ $liveShow->host_name ?? 'TBA' }}</strong>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="border border-secondary rounded p-3 h-100">
                                <small class="text-secondary d-block mb-1">Scheduled At</small>
                                <strong>{{ $liveShow->scheduled_at ? $liveShow->scheduled_at->format('d M Y, H:i') : 'N/A' }}</strong>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="border border-secondary rounded p-3 h-100">
                                <small class="text-secondary d-block mb-1">Started At</small>
                                <strong>{{ $liveShow->start_time ? $liveShow->start_time->format('d M Y, H:i:s') : 'Not started yet' }}</strong>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="border border-secondary rounded p-3 h-100">
                                <small class="text-secondary d-block mb-1">Ended At</small>
                                <strong>{{ $liveShow->end_time ? $liveShow->end_time->format('d M Y, H:i:s') : 'Not ended yet' }}</strong>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="border border-secondary rounded p-3 h-100">
                                <small class="text-secondary d-block mb-1">Prize Pool</small>
                                <strong class="text-warning">{{ number_format($liveShow->prize_amount, 2) }} {{ $liveShow->currency }}</strong>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="border border-secondary rounded p-3 h-100">
                                <small class="text-secondary d-block mb-1">Max Winners</small>
                                <strong>{{ $liveShow->max_winners }}</strong>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="border border-secondary rounded p-3 h-100">
                                <small class="text-secondary d-block mb-1">Total Players</small>
                                <strong>{{ $players->count() }}</strong>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="border border-secondary rounded p-3 h-100">
                                <small class="text-secondary d-block mb-1">Total Questions</small>
                                <strong>{{ $totalQuestions }}</strong>
                            </div>
                        </div>
                    </div>
                    @if($liveShow->start_time && $liveShow->end_time)
                        <div class="row">
                            <div class="col-md-3 col-sm-6 mb-3">
                                <div class="border border-secondary rounded p-3 h-100">
                                    <small class="text-secondary d-block mb-1">Duration</small>
                                    <strong>{{ $liveShow->start_time->diff($liveShow->end_time)->format('%H:%I:%S') }}</strong>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($liveShow->winnerPrizes->count())
                        <div class="mt-2">
                            <small class="text-secondary d-block mb-2">Winner Prize Distribution</small>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($liveShow->winnerPrizes as $wp)
                                    <span class="badge bg-secondary py-2 px-3">Rank {{ $wp->rank }}: {{ $wp->prize }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Export Buttons --}}
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="text-dark mb-0"><i class="fas fa-users me-2"></i>Players & Responses</h5>
                <a href="{{ route('admin.live-shows.export-participants-csv', $liveShow->id) }}"
                   class="btn btn-outline-success btn-sm">
                    <i class="fas fa-file-csv me-1"></i> Export All Participants CSV
                </a>
            </div>

            {{-- Split Panel: Players Left, Responses Right --}}
            <div class="row" id="detailsContainer">
                {{-- Left Panel: Players List --}}
                <div class="col-lg-5 col-md-6 mb-3" id="playersPanel">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-trophy me-1"></i> Players ({{ $players->count() }})</span>
                            <input type="text" id="playerSearch" class="form-control form-control-sm bg-dark text-light border-secondary"
                                   style="max-width: 200px;" placeholder="Search players...">
                        </div>
                        <div class="card-body p-0 bg-dark" style="max-height: 75vh; overflow-y: auto;">
                            @if($players->count())
                                <div class="list-group list-group-flush" id="playersList">
                                    @foreach($players as $index => $player)
                                        <a href="javascript:void(0)"
                                           class="list-group-item list-group-item-action bg-dark text-light border-secondary player-item"
                                           data-user-id="{{ $player->id }}"
                                           data-user-name="{{ $player->name }}"
                                           data-user-email="{{ $player->email }}"
                                           data-user-score="{{ $player->pivot->score ?? 0 }}"
                                           data-user-is-winner="{{ $player->pivot->is_winner ? '1' : '0' }}"
                                           data-user-prize="{{ $player->pivot->prize_won ?? 'N/A' }}"
                                           data-user-status="{{ $player->pivot->status ?? '' }}"
                                           data-user-joined="{{ $player->pivot->created_at ? \Carbon\Carbon::parse($player->pivot->created_at)->format('d M Y, H:i') : 'N/A' }}">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div class="d-flex align-items-center">
                                                    <span class="badge bg-secondary me-2" style="min-width:30px;">{{ $index + 1 }}</span>
                                                    <div>
                                                        <strong>{{ $player->name }}</strong>
                                                        @if($player->pivot->is_winner)
                                                            <i class="fas fa-crown text-warning ms-1" title="Winner"></i>
                                                        @endif
                                                        <br>
                                                        <small class="text-secondary">{{ $player->email }}</small>
                                                    </div>
                                                </div>
                                                <div class="text-end">
                                                    <span class="badge bg-primary">{{ $player->pivot->score ?? 0 }} pts</span>
                                                    @if($player->pivot->is_winner)
                                                        <br><small class="text-warning">{{ $player->pivot->prize_won ?? '' }}</small>
                                                    @endif
                                                </div>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            @else
                                <div class="p-4 text-center text-secondary">
                                    <i class="fas fa-user-slash fa-2x mb-2"></i>
                                    <p>No participants joined this show.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Right Panel: Response Details --}}
                <div class="col-lg-7 col-md-6 mb-3" id="responsesPanel">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center" id="responsesHeader">
                            <span><i class="fas fa-clipboard-list me-1"></i> <span id="responsesTitle">Select a player to view responses</span></span>
                            <div id="responsesActions" style="display:none;">
                                <a href="#" id="exportPlayerCsvBtn" class="btn btn-outline-success btn-sm">
                                    <i class="fas fa-file-csv me-1"></i> Export Player CSV
                                </a>
                            </div>
                        </div>
                        <div class="card-body bg-dark text-light" style="max-height: 75vh; overflow-y: auto;" id="responsesBody">
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
                                <p class="text-secondary fs-5">Click on a player from the left panel<br>to view their response details.</p>
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
    document.addEventListener('DOMContentLoaded', function () {
        const playerItems = document.querySelectorAll('.player-item');
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

        let activePlayerId = null;

        playerSearch.addEventListener('input', function () {
            const query = this.value.toLowerCase();
            playerItems.forEach(function (item) {
                const name = item.dataset.userName.toLowerCase();
                const email = item.dataset.userEmail.toLowerCase();
                item.style.display = (name.includes(query) || email.includes(query)) ? '' : 'none';
            });
        });

        playerItems.forEach(function (item) {
            item.addEventListener('click', function () {
                const userId = this.dataset.userId;
                const userName = this.dataset.userName;
                const userEmail = this.dataset.userEmail;
                const userScore = this.dataset.userScore;
                const isWinner = this.dataset.userIsWinner === '1';
                const prizWon = this.dataset.userPrize;
                const userStatus = this.dataset.userStatus;

                playerItems.forEach(function (el) { el.classList.remove('active'); });
                this.classList.add('active');

                activePlayerId = userId;

                responsesTitle.textContent = userName + "'s Responses";
                responsesActions.style.display = 'block';
                exportPlayerCsvBtn.href = "{{ url('admin/live-shows') }}/{{ $liveShow->id }}/export-player-csv/" + userId;

                document.getElementById('summaryName').textContent = userName;
                document.getElementById('summaryEmail').textContent = userEmail;
                document.getElementById('summaryScore').textContent = userScore + ' pts';

                const winnerBadge = document.getElementById('summaryWinner');
                if (isWinner) {
                    winnerBadge.textContent = 'Winner - ' + prizWon;
                    winnerBadge.className = 'badge bg-warning text-dark py-2 px-3';
                } else {
                    winnerBadge.textContent = 'Participant';
                    winnerBadge.className = 'badge bg-info py-2 px-3';
                }
                document.getElementById('summaryStatus').textContent = userStatus.charAt(0).toUpperCase() + userStatus.slice(1);

                emptyState.style.display = 'none';
                noResponsesState.style.display = 'none';
                responsesContent.style.display = 'none';
                playerSummary.style.display = 'none';
                loadingState.style.display = 'block';

                fetch("{{ url('admin/live-shows') }}/{{ $liveShow->id }}/player-responses/" + userId)
                    .then(function (res) { return res.json(); })
                    .then(function (data) {
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
                        data.responses.forEach(function (resp, idx) {
                            if (resp.is_correct) correctCount++;
                            else wrongCount++;
                            totalTime += parseFloat(resp.seconds_to_submit) || 0;

                            var correctOpt = resp.options.find(function (o) { return o.is_correct; });
                            var correctText = correctOpt ? correctOpt.option_text : 'N/A';

                            var row = '<tr>' +
                                '<td>' + (idx + 1) + '</td>' +
                                '<td>' + escapeHtml(resp.question) + '</td>' +
                                '<td>' +
                                    '<span class="' + (resp.is_correct ? 'text-success' : 'text-danger') + '">' +
                                        escapeHtml(resp.selected_option) +
                                    '</span>' +
                                '</td>' +
                                '<td class="text-success">' + escapeHtml(correctText) + '</td>' +
                                '<td>' +
                                    (resp.is_correct
                                        ? '<span class="badge bg-success"><i class="fas fa-check"></i> Correct</span>'
                                        : '<span class="badge bg-danger"><i class="fas fa-times"></i> Wrong</span>') +
                                '</td>' +
                                '<td>' + (resp.seconds_to_submit !== null ? parseFloat(resp.seconds_to_submit).toFixed(1) + 's' : '-') + '</td>' +
                                '<td>' + (resp.response_score !== null ? parseFloat(resp.response_score).toFixed(1) : '-') + '</td>' +
                                '</tr>';

                            responsesTableBody.insertAdjacentHTML('beforeend', row);
                        });

                        document.getElementById('summaryCorrect').textContent = correctCount + ' / ' + data.responses.length;
                        document.getElementById('summaryWrong').textContent = wrongCount + ' / ' + data.responses.length;
                        var avgTime = data.responses.length > 0 ? (totalTime / data.responses.length).toFixed(1) + 's' : '-';
                        document.getElementById('summaryAvgTime').textContent = avgTime;

                        responsesContent.style.display = 'block';
                    })
                    .catch(function (err) {
                        loadingState.style.display = 'none';
                        noResponsesState.style.display = 'block';
                        console.error('Failed to load responses:', err);
                    });
            });
        });

        function escapeHtml(text) {
            var div = document.createElement('div');
            div.appendChild(document.createTextNode(text));
            return div.innerHTML;
        }
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
