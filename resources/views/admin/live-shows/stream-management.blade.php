 <x-app-dashboard-layout>


     <div class="container-fluid  min-vh-100">
         <div class=" d-flex justify-content-between align-items-center py-3 bg-dark rounded mb-1 p-3">
             <div>
                 <h4 class="mb-0 fw-bold  ">{{ $liveShow->title }}</h4>
             </div>
             <div class="btn-group shadow-sm">
                 <a target="_blank" class="btn btn-outline-primary"
                     href="{{ route('admin.live-shows.stream-management.broadcaster', [$liveShow->id]) }}">
                     <i class="fas fa-video me-1"></i> Broadcaster
                 </a>
                 <a target="_blank" href="{{ route('admin.live-shows.edit', $liveShow->id) }}"
                     class="btn btn-outline-secondary">
                     <i class="fas fa-edit me-1"></i> Edit
                 </a>
                 <a target="_blank" href="{{ route('admin.live-shows.view-details', $liveShow->id) }}"
                     class="btn btn-outline-info">
                     <i class="fas fa-info-circle me-1"></i> Details
                 </a>
                 <button class="btn btn-outline-danger" id="resetGameButton">
                     <i class="fas fa-undo me-1"></i> Reset
                 </button>
             </div>
         </div>
         <div class="row g-4">
             <div class="col-lg-2">
                 <label class="small text-muted d-block mb-2">Join via QR Code</label>
                 <div id="qrcode" class=" p-2 rounded"></div>
                 <div class="mt-1 d-flex   mb-3">
                     <a href="{{ url('live-show-play/' . $liveShow->id) }}" target="_blank"
                         class="text-decoration-none small text-truncate d-block  ">
                         {{ url('live-show-play/' . $liveShow->id) }}
                     </a>
                     <button type="button" class="btn btn-sm btn-link" id="copyLiveShowLinkBtn"
                         data-bs-toggle="tooltip" data-bs-placement="top" title="Copy link to clipboard">
                         <i class="fas fa-copy"></i>
                     </button>
                 </div>
                 <div class="card border-0 shadow-sm h-100">
                     <div class="card-header  border-bottom py-3">
                         <h6 class="mb-0 fw-bold text-uppercase small text-muted">
                             <i class="fas fa-users me-2 text-primary"></i>
                             Active Players
                             <span id="total-users-count">
                                 ({{ $liveShow->users()->count() }})
                             </span>
                         </h6>
                     </div>
                     <div class="card-body p-0">
                         <div class="d-flex justify-content-between align-items-center gap-2 mb-2 p-2 flex-wrap">
                             <div class="input-group input-group-sm" style="max-width: 100%;">
                                 <span class="input-group-text">
                                     <i class="fas fa-search"></i>
                                 </span>
                                 <input type="text" class="form-control" id="playerSearchInput"
                                     placeholder="Search players by name, username, or email">
                             </div>
                             <div class="d-flex gap-2 ms-auto">
                                 <a target="_blank" href="{{ route('admin.live-shows.players', $liveShow->id) }}"
                                     class="btn btn-outline-light btn-sm text-nowrap">
                                     <i class="fas fa-external-link-alt me-1"></i> View All
                                 </a>
                                 <button type="button" class="btn btn-primary btn-sm text-nowrap"
                                     id="fetchPlayersButton">
                                     <i class="fas fa-sync"></i> Refresh Players
                                 </button>
                                 <a href="{{ route('admin.live-shows.export-all-users-as-csv', $liveShow->id) }}"
                                     title="Export Users" class="btn btn-primary btn-sm" id="exportUsersBtn"
                                     data-bs-toggle="tooltip" data-bs-placement="top">
                                     <i class="fas fa-file-export"></i>
                                 </a>
                             </div>
                         </div>
                         <div class="d-flex justify-content-between align-items-center px-2 pb-2 small text-muted">
                             <span id="playersSearchSummary">Showing players</span>

                         </div>
                         <table class="table table-sm table-dark table-hover align-middle mb-0"
                             style=" overflow-y: scroll; max-height: 80vh; padding-bottom: 30px;">
                             <thead>
                                 <tr>
                                     <th>Player</th>
                                     <th>Score</th>
                                     <th>Actions</th>
                                 </tr>
                             </thead>
                             <tbody id="activePlayersList">
                                 <tr class="bg-dark align-middle">
                                     <td>
                                         <span class="position-relative me-3">
                                             <div class="bg-secondary rounded-circle"
                                                 style="width: 32px; height: 32px;">
                                             </div>
                                             <span
                                                 class="position-absolute bottom-0 end-0 p-1 bg-success border border-light rounded-circle"></span>
                                         </span>
                                         <div class="small fw-medium">Loading...</div>
                                     </td>
                                 </tr>
                             </tbody>

                         </table>
                         <div class="p-2 border-top">
                             <button type="button" class="btn btn-outline-primary btn-sm w-100 d-none"
                                 id="loadMorePlayersButton">
                                 <i class="fas fa-plus-circle me-1"></i> Load More Players
                             </button>
                         </div>
                     </div>
                 </div>
             </div>

             <main class="col-lg-7">
                 <div class="card border-0 shadow-sm mb-4">
                     <div class="card-body ">
                         <div class="row align-items-center">
                             <div class="col-lg-5">
                                 <div class="col-md-12">
                                     <div class="card border-0 shadow-sm">
                                         <div class="card-body">
                                             <h6 class="text-muted small text-uppercase fw-bold mb-3">Show Status
                                             </h6>
                                             <form action="" method="post" id="live-show-status-form"
                                                 class="d-flex gap-2">
                                                 <select class="form-select fw-bold" id="liveShowStatusSelect"
                                                     onchange="updateLiveShowStatus(this.value)">
                                                     >
                                                     <option value="scheduled"
                                                         {{ $liveShow->status == 'scheduled' ? 'selected' : '' }}>⏳
                                                         Scheduled</option>
                                                     <option value="live"
                                                         {{ $liveShow->status == 'live' ? 'selected' : '' }}>🟢
                                                         Live</option>
                                                     <option value="completed"
                                                         {{ $liveShow->status == 'completed' ? 'selected' : '' }}>
                                                         🔴
                                                         Completed
                                                     </option>
                                                 </select>
                                                 {{-- <button type="submit"
                                                       class="btn btn-dark text-nowrap px-3">Update</button> --}}
                                             </form>
                                         </div>
                                     </div>
                                 </div>
                                 <div class="col-md-12 mb-3">
                                     <div class="card border-0 shadow-sm">
                                         <div class="card-body">
                                             <h6 class="text-muted small text-uppercase fw-bold mb-3">
                                                 Winners Ceremony
                                             </h6>
                                             <button type="button" id="announceWinnersBtn"
                                                 class="btn btn-warning w-100 py-2 fw-bold text-white shadow-sm my-2"
                                                 onclick="updateWinners()"
                                                 @if ($liveShow->winners_announced) disabled aria-disabled="true" @endif>
                                                 <span id="announceWinnersBtnContent"
                                                     class="announce-winners-btn-label @if ($liveShow->winners_announced) d-none @endif">
                                                     <i class="fas fa-trophy me-2"></i> Announce Winners
                                                 </span>
                                                 <span id="announceWinnersBtnLoader"
                                                     class="announce-winners-btn-loader d-none">
                                                     <i class="fas fa-spinner fa-spin me-2" aria-hidden="true"></i>
                                                     Announcing…
                                                 </span>
                                                 <span id="announceWinnersBtnDone"
                                                     class="announce-winners-btn-done @if (!$liveShow->winners_announced) d-none @endif">
                                                     <i class="fas fa-check me-2"></i> Winners announced
                                                 </span>
                                             </button>
                                             <p id="announceWinnersAckMessage"
                                                 class="small text-success mb-0 mt-2 px-1 @if (!$liveShow->winners_announced) d-none @endif">
                                                 Winners have been announced. Winner notification emails have been
                                                 queued for the winners.
                                             </p>
                                             <button type="button" id="unannounceWinnersBtn"
                                                 class="btn btn-outline-secondary w-100 py-2 fw-bold shadow-sm my-2 @if (!$liveShow->winners_announced) d-none @endif"
                                                 onclick="unannounceWinners()">
                                                 <span id="unannounceWinnersBtnLabel"
                                                     class="unannounce-winners-btn-label">
                                                     <i class="fas fa-undo me-2"></i> Un-announce winners
                                                 </span>
                                                 <span id="unannounceWinnersBtnLoader"
                                                     class="unannounce-winners-btn-loader d-none">
                                                     <i class="fas fa-spinner fa-spin me-2" aria-hidden="true"></i>
                                                     Updating…
                                                 </span>
                                             </button>
                                             <div class="d-flex gap-2 mt-3">
                                                 <button type="button"
                                                     class="btn btn-warning   py-2 fw-bold text-white shadow-sm my-2"
                                                     onclick="showWinnerTab(this)">
                                                     <i class="fas fa-eye me-2"></i> Show winner tab
                                                 </button>
                                                 <button type="button"
                                                     class="btn btn-outline-warning   py-2 fw-bold text-white shadow-sm my-2"
                                                     onclick="hideWinnerTab(this)">
                                                     <i class="fas fa-eye-slash me-2"></i> Hide winner tab
                                                 </button>
                                             </div>


                                         </div>
                                     </div>
                                 </div>
                             </div>
                             <div class="col-md-7 text-center">
                                 <div style="width: 480px; height: 720px; padding: 5px; overflow: hidden; border: 1px solid #ccc;border-radius: 10px;">
                                     <div id="">
                                         <button class="btn btn-outline-primary mb-2" type="button"
                                             onclick="document.querySelector('#broadcasterIframe').src = document.querySelector('#broadcasterIframe').src;"
                                             style=" ">
                                             <i class="fas fa-sync-alt me-1"></i> Refresh Broadcast
                                         </button>
                                     <a
                                         class="btn btn-outline-info mb-2"
                                         href="{{ route('admin.live-shows.stream-management.broadcaster', [$liveShow->id]) }}"
                                         target="_blank"
                                         style="margin-left: 10px;"
                                     >
                                         <i class="fas fa-external-link-alt me-1"></i> Open in New Tab
                                     </a>
                                
                                     </div>

                                     <iframe id="broadcasterIframe"
                                         src="{{ route('admin.live-shows.stream-management.broadcaster', [$liveShow->id]) }}"
                                         style="width: 960px; height: 1440px; transform: scale(0.5); transform-origin: 0 0; border: none;">
                                     </iframe>
                                 </div>

                             </div>
                         </div>
                     </div>
                 </div>

                 <div class="card border-0 shadow-sm mb-4">
                     <div class="card-body position-relative">
                         <div class="row">
                             <div class="col-lg-7 ">
                                 <div class="p-3   rounded bg-dark">
                                     <h5 class="mb-0 fw-bold text-center mb-3">Quiz Questions</h5>
                                     <div class="question-slider ">
                                         @foreach ($liveShow->quizzes as $index => $quiz)
                                             <div class="px-2">
                                                 <div class="card border mb-3">
                                                     <div class="card-body" style="height: auto; overflow-y:scroll">
                                                         <div class="text-center mb-4 fw-bold">
                                                             <div class="mb-2">Question {{ $index + 1 }} /
                                                                 {{ $liveShow->quizzes->count() }}</div>
                                                             <div class="fw-bold h3">{{ $quiz->question }}</div>
                                                         </div>

                                                         @if ($quiz->options)
                                                             <div class="row g-3 mb-4">
                                                                 @foreach ($quiz->options as $option)
                                                                     <div class="col-md-12">
                                                                         <div
                                                                             class="p-3 border rounded @if ($option->is_correct) border-success @endif">
                                                                             <div
                                                                                 class="d-flex justify-content-between mb-2">
                                                                                 <span
                                                                                     class="fw-bold @if ($option->is_correct) text-success @endif">
                                                                                     {{ $option->option_text }}
                                                                                     @if ($option->is_correct)
                                                                                         <i
                                                                                             class="fas fa-check-circle ms-1"></i>
                                                                                     @endif
                                                                                 </span>
                                                                                 <span class="small fw-bold"
                                                                                     id="option-result-label-{{ $option->id }}">0%</span>
                                                                             </div>
                                                                             <div class="progress"
                                                                                 style="height: 8px;">
                                                                                 <div id="option-result-bar-{{ $option->id }}"
                                                                                     class="progress-bar @if ($option->is_correct) bg-success @else bg-primary @endif"
                                                                                     role="progressbar"
                                                                                     style="width: 0%">
                                                                                 </div>
                                                                             </div>
                                                                         </div>
                                                                     </div>
                                                                 @endforeach
                                                             </div>

                                                             <form method="POST"
                                                                 id="quiz-timer-form-{{ $quiz->id }}"
                                                                 onsubmit="submitQuizTimerForm(event, {{ $quiz->id }})"
                                                                 class="row g-2 align-items-center justify-content-center">
                                                                 @csrf
                                                                 <div class="col-auto">
                                                                     <div class="input-group">
                                                                         <span class="input-group-text bg-white"><i
                                                                                 class="fas fa-stopwatch text-muted"></i></span>
                                                                         <input type="number" min="1"
                                                                             name="seconds"
                                                                             id="timer-{{ $quiz->id }}"
                                                                             value="10"
                                                                             class="form-control text-center fw-bold"
                                                                             style="width: 80px;" required>
                                                                     </div>
                                                                 </div>
                                                                 @if ($loop->last)
                                                                     <input type="hidden" name="is_last"
                                                                         value="1">
                                                                 @endif
                                                                 <div class="col-auto">
                                                                     <div class="btn-group shadow-sm">
                                                                         <button type="submit"
                                                                             class="btn btn-success px-3">
                                                                             <i class="fas fa-play me-2"></i> Start
                                                                         </button>
                                                                         <button type="button"
                                                                             onclick="viewResponses({{ $liveShow->id }}, {{ $quiz->id }}, this)"
                                                                             class="btn btn-info px-3 text-white">
                                                                             <i class="fas fa-chart-bar me-2"></i>
                                                                             Show Responses
                                                                         </button>
                                                                         <button class="btn btn-danger px-3"
                                                                             type="button"
                                                                             onclick="removeQuiz({{ $quiz->id }}, this)">
                                                                             <i class="fas fa-times me-2"></i> Hide
                                                                         </button>

                                                                     </div>
                                                                 </div>
                                                             </form>
                                                         @endif
                                                     </div>
                                                 </div>
                                             </div>
                                         @endforeach
                                     </div>

                                     <div id="quizTimer"
                                         style=" position: absolute; bottom: 120px; right: 100px;   padding: 10px; border: 1px solid transparent; border-radius: 50%; z-index: 1000;width: 100px;height: 100px;display: none;align-items: center;justify-content: center; font-size: 3rem;font-weight: bold; text-align: center; background: url('{{ asset('/images/clock.png') }}') no-repeat center center; background-size: contain;">
                                         <span id="quizTimerText" style="color: #000;margin-top: 10px;">0</span>
                                     </div>
                                 </div>
                             </div>
                             <div class="col-lg-5">
                                 <h5 class="mb-0 fw-bold text-center mb-3">Gallery Media</h5>
                                 <div class="p-3 border border-light rounded bg-dark">
                                     <div class="w-100">
                                         <div class="mb-2">
                                             <h6 class="text-muted small text-uppercase fw-bold mb-">
                                                 Attached to this stream</h6>

                                             <button type="button" class="btn btn-sm btn-outline-primary mt-2"
                                                 title="Attach media from gallery" data-bs-toggle="modal"
                                                 data-bs-target="#select-media-modal">
                                                 <i class="fas fa-plus"></i> Add from gallery
                                             </button>
                                             <button type="button"
                                                 class="btn btn-sm btn-outline-secondary gallery-hide-on-stream-btn mt-2"
                                                 title="Hide image/video overlay on live stream ">
                                                 <i class="fas fa-eye-slash"></i> Hide on stream
                                             </button>
                                         <button type="button"
                                             class="btn btn-sm btn-outline-success  mt-2"
                                             title="Refresh gallery items"
                                             onclick="fetchGalleryMediaItems()">
                                             <i class="fas fa-sync-alt"></i> Refresh gallery
                                         </button>
                                    
                                             
                                         </div>
                                         <div id="gallery-attached-list" class="table-responsive mb-3"
                                             style="max-height: 520px; overflow-y: auto;">
                                             <table class="table table-sm table-dark table-hover align-middle mb-0">

                                                 <tbody id="attached-media-list">

                                                 </tbody>
                                             </table>
                                         </div>
                                     </div>
                                 </div>
                             </div>
                         </div>
                     </div>
                 </div>



             </main>

             <div class="col-lg-3">
                 <div class="card border-0 shadow-sm h-100">
                     <div class="card-header p-0">
                         <ul class="nav nav-tabs nav-fill border-0" id="rightPanelTabs">
                             <li class="nav-item position-relative d-flex align-items-center">
                                 <a href="#chat-tab"
                                     class="nav-link active py-3 border-0 border-bottom fw-bold d-flex text-center"
                                     data-bs-toggle="tab">Live Chat

                                 </a>

                             </li>
                             <li class="nav-item">
                                 <a href="#live-show-details" class="nav-link py-3 border-0 border-bottom fw-bold"
                                     data-bs-toggle="tab">Live Show Details</a>
                             </li>
                             <li class="nav-item">
                                 <a href="#live-show-preview" class="nav-link py-3 border-0 border-bottom fw-bold"
                                     data-bs-toggle="tab">Preview
                                     <button class="btn btn-sm btn-primary ms-2" id="previewRefreshButton"
                                         onclick="refreshPreview()">
                                         <i class="fas fa-refresh"></i>
                                     </button>
                                     <button class="btn btn-sm btn-secondary ms-2" id="mutePreviewButton"
                                         onclick="togglePreviewMute()" title="Mute/Unmute Preview">
                                         <i class="fas fa-volume-mute" id="mutePreviewIcon"></i>
                                     </button>


                                 </a>
                             </li>
                         </ul>
                     </div>
                     <div class="card-body p-0">
                         <div class="tab-content">
                             <div class="tab-pane fade show active" id="chat-tab">
                                 <div class="p-3">
                                     <button class="btn btn-primary mb-1" id="resetChatBtn" title="Reset Chat"
                                         data-bs-toggle="tooltip" data-bs-placement="top">
                                         <i class="fas fa-eraser"></i>
                                     </button>


                                     <a href="{{ route('admin.live-shows.export-all-chats-as-csv', $liveShow->id) }}"
                                         class="btn btn-primary mb-1 ms-1" id="exportChatsBtn" title="Export Chats"
                                         data-bs-toggle="tooltip" data-bs-placement="top">
                                         <i class="fas fa-file-export"></i>
                                     </a>

                                     <button class="btn btn-warning mb-1 ms-1" id="toggleChatStatusBtn"
                                         title="Toggle Chat Access" data-bs-toggle="tooltip" data-bs-placement="top">
                                         <span class="badge bg-success ms-2 d-none" id="chatStatusBadge">Chat
                                             Enabled</span>
                                         <i class="fas fa-comments"></i>

                                         <span id="chatToggleBtnText">Disable Chat</span>
                                     </button>
                                 </div>
                                 <div id="live-chat-messages" class="p-3">
                                 </div>
                                 <div class="p-3 border-top">
                                     <form onsubmit="event.preventDefault(); sendMessage(event)" class="input-group">
                                         <input type="text" class="form-control" id="messageInput"
                                             placeholder="Write to players...">
                                         <button class="btn btn-primary" type="submit">
                                             <i class="fas fa-paper-plane"></i>
                                         </button>
                                     </form>
                                 </div>
                             </div>

                             <div class="tab-pane fade" id="live-show-details">
                                 <div class="p-2">
                                     <table class="table table-success table-bordered">
                                         <tbody>
                                             <tr>
                                                 <th>Title</th>
                                                 <td>{{ $liveShow->title }}</td>
                                             </tr>
                                             <tr>
                                                 <th>Status</th>
                                                 <td>
                                                     @if ($liveShow->status == 'live')
                                                         <span class="badge bg-success">Live</span>
                                                     @elseif($liveShow->status == 'completed')
                                                         <span class="badge bg-danger">Completed</span>
                                                     @else
                                                         <span
                                                             class="badge bg-secondary">{{ ucfirst($liveShow->status) }}</span>
                                                     @endif
                                                 </td>
                                             </tr>
                                             <tr>
                                                 <th>Stream ID</th>
                                                 <td>{{ $liveShow->stream_id }}</td>
                                             </tr>

                                             <tr>
                                                 <td>Live URL</td>
                                                 <td>
                                                     <a href="{{ url('live-show-play/' . $liveShow->id) }}"
                                                         class="text-decoration-none small text-truncate d-block px-3">
                                                         {{ url('live-show-play/' . $liveShow->id) }}
                                                     </a>
                                                 </td>
                                             </tr>

                                             <tr>
                                                 <th>Prize</th>
                                                 <td>{{ $liveShow->currency }}
                                                     {{ number_format($liveShow->prize_amount, 2) }}</td>
                                             </tr>
                                             <tr>
                                                 <th>Scheduled At</th>
                                                 <td>{{ $liveShow->scheduled_at->format('Y-m-d H:i') }}</td>

                                             <tr>
                                                 <th>Total Questions</th>
                                                 <td>{{ $liveShow->quizzes->count() }}</td>
                                             </tr>
                                             <tr>
                                                 <th>Created At</th>
                                                 <td>{{ $liveShow->created_at->format('Y-m-d H:i') }}</td>
                                             </tr>
                                             <tr>
                                                 <th>Updated At</th>
                                                 <td>{{ $liveShow->updated_at->format('Y-m-d H:i') }}</td>
                                             </tr>
                                             <tr>
                                                 <td>Max Winner</td>
                                                 <td>{{ $liveShow->max_winners }}</td>
                                             </tr>
                                             <tr>
                                                 <td>Max Players</td>
                                                 <td>{{ $liveShow->max_players ?? 'Unlimited' }}</td>
                                             </tr>
                                             <tr>
                                                 <td>Chat Status</td>
                                                 <td>
                                                     @if ($liveShow->chat_enabled)
                                                         <span class="badge bg-success">Enabled</span>
                                                     @else
                                                         <span class="badge bg-danger">Disabled</span>
                                                     @endif
                                                 </td>
                                             </tr>
                                             <tr>
                                                 <th>Winners Prizes</th>
                                                 <td>
                                                     @foreach ($liveShow->winnerPrizes as $winner)
                                                         <div
                                                             class="d-flex justify-content-start align-items-center mb-3">
                                                             <span
                                                                 class="badge bg-success">#{{ $winner->rank }}</span>
                                                             <span class="text-muted">-</span>
                                                             <span class="text-dark">{{ $winner->prize }}</span>
                                                         </div>
                                                     @endforeach
                                                 </td>
                                             </tr>
                                             <tr>
                                             </tr>
                                         </tbody>
                                     </table>
                                 </div>
                             </div>
                             <div class="tab-pane fade" id="live-show-preview">
                                 <div class="p-2 d-flex justify-content-center align-items-center   ">
                                     <iframe src="{{ url('live-show-play/' . $liveShow->id) }}?preview=true"
                                         id="live-show-preview-iframe" class="live-show-preview-iframe mt-2"
                                         style="height: 956px; width: 500px; pointer-events: none; border-radius: 30px; border: 1px solid #ccc;overflow: hidden;"
                                         allowfullscreen="true" allow="autoplay; encrypted-media; picture-in-picture"
                                         frameborder="0"></iframe>
                                 </div>
                             </div>


                         </div>
                     </div>
                 </div>
             </div>
         </div>
     </div>



     <div class="modal fade" id="media-preview-modal" tabindex="-1" aria-labelledby="media-preview-modal-label"
         aria-hidden="true">
         <div class="modal-dialog modal-dialog-centered modal-xl">
             <div class="modal-content">
                 <div class="modal-header">
                     <h5 class="modal-title" id="media-preview-modal-label">Media Preview</h5>
                     <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                 </div>
                 <div class="modal-body p-0 d-flex justify-content-center align-items-center"
                     style="min-height:400px;">
                     <img src="" id="media-preview-modal-img" class="img-fluid rounded shadow"
                         style="max-height:75vh; max-width:100%;">
                 </div>
                 <div class="modal-footer">
                     <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                 </div>
             </div>
         </div>
     </div>


     <!-- Modal: Select Media From Gallery -->
     <div class="modal fade" id="select-media-modal" tabindex="-1" aria-labelledby="select-media-modal-label"
         aria-hidden="true">
         <div class="modal-dialog modal-dialog-centered modal-xl">
             <div class="modal-content">
                 <div class="modal-header">
                     <h5 class="modal-title" id="select-media-modal-label">Select Media from Gallery</h5>
                     <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                 </div>
                 <div class="modal-body" style="min-height:350px;">
                     <!-- Placeholder: List of available gallery media will be rendered here later -->
                     <div id="select-media-modal-list" class="row g-3">
                         <div class="col-12 text-muted text-center py-4">
                             Loading available media...
                         </div>
                     </div>
                 </div>
                 <div class="modal-footer">
                     <!-- You can add action buttons here later, e.g., confirm/cancel -->
                     <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                 </div>
             </div>
         </div>
     </div>


     <style>
         /* Custom Styling for the Admin Dashboard */
         .bg-soft-info {
             background-color: #e0f2fe;
         }

         .nav-tabs .nav-link {
             color: #64748b;
             border-radius: 0;
         }

         .nav-tabs .nav-link.active {
             color: #0d6efd;
             border-bottom: 2px solid #0d6efd !important;
         }

         .list-group-item:hover {
             background-color: #f8fafc;
         }

         .progress-bar {
             transition: width 0.6s ease;
         }

         /* Custom Scrollbar */
         ::-webkit-scrollbar {
             width: 6px;
         }

         ::-webkit-scrollbar-track {
             background: #f1f1f1;
         }

         ::-webkit-scrollbar-thumb {
             background: #cbd5e1;
             border-radius: 10px;
         }

         ::-webkit-scrollbar-thumb:hover {
             background: #94a3b8;
         }
     </style>

     @push('styles')
         <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.15.10/dist/sweetalert2.min.css">
         <style>
             #live-chat-messages {
                 height: 60vh;
                 overflow-y: auto;
                 display: flex;
                 flex-direction: column;
                 /* justify-content: flex-end; */

             }



             #live-chat-messages .message {
                 align-self: flex-start;
                 padding: 8px;
                 border-bottom: 1px solid #ccc;
                 margin-bottom: 10px;
             }

             .option-result-bar {
                 height: 15px;
                 background: linear-gradient(90deg, #1e90ff, #00bfff);
                 width: 0;
                 transition: width 0.4s ease-in-out;
                 border-radius: 5px;
             }

             .slick-list {
                 min-height: 500px !important;
                 width: 100% !important;
             }

             .slick-initialized .slick-slide {
                 min-height: 500px !important;
                 min-width: 500px !important;
             }
         </style>
     @endpush

     @push('scripts')
         <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.15.10/dist/sweetalert2.all.min.js"></script>
         <script>
             Pusher.logToConsole = true;
             let isChatEnabled = {{ $liveShow->chat_enabled ? 'true' : 'false' }};
             const playerPageSize = 100;
             const playerListState = {
                 loadedCount: playerPageSize,
                 search: '',
                 totalUsers: 0,
                 filteredUsers: 0,
                 hasMore: false,
             };
             let playerSearchDebounceTimer = null;
             let liveShowStatus = '{{ $liveShow->status }}';
             let liveShowWinnersAnnounced = {{ $liveShow->winners_announced ? 'true' : 'false' }};

             var pusher = new Pusher('{{ env('PUSHER_APP_KEY', '2a66d003a7ded9fe567a') }}', {
                 cluster: '{{ env('PUSHER_APP_CLUSTER', 'eu') }}',
             });

             function streamSwalSuccess(message, title) {
                 return Swal.fire({
                     icon: 'success',
                     title: title || 'Success',
                     text: message || undefined,
                 });
             }

             function streamSwalError(message, title) {
                 return Swal.fire({
                     icon: 'error',
                     title: title || 'Error',
                     text: message || undefined,
                 });
             }

             function streamSwalWarning(message, title) {
                 return Swal.fire({
                     icon: 'warning',
                     title: title || 'Notice',
                     text: message || undefined,
                 });
             }

             function streamSwalConfirm(options) {
                 return Swal.fire(Object.assign({
                     icon: 'warning',
                     showCancelButton: true,
                     confirmButtonText: 'Yes, continue',
                     cancelButtonText: 'Cancel',
                     reverseButtons: true,
                 }, options));
             }

             // Generic helper: toggle a busy/loading state on any action button.
             // Saves the original markup the first time, swaps it for a spinner,
             // and restores it (and re-enables the button) when busy=false.
             function setBtnBusy(btn, busy, busyText) {
                 if (!btn) return;
                 if (busy) {
                     if (btn.dataset.busyActive === '1') return;
                     btn.dataset.busyActive = '1';
                     btn.dataset.originalHtml = btn.innerHTML;
                     btn.dataset.originalDisabled = btn.disabled ? '1' : '0';
                     btn.disabled = true;
                     btn.setAttribute('aria-busy', 'true');
                     var spinner = '<i class="fas fa-spinner fa-spin me-1" aria-hidden="true"></i>';
                     btn.innerHTML = spinner + (busyText || 'Working\u2026');
                 } else {
                     if (btn.dataset.busyActive !== '1') return;
                     if (typeof btn.dataset.originalHtml === 'string') {
                         btn.innerHTML = btn.dataset.originalHtml;
                     }
                     btn.disabled = btn.dataset.originalDisabled === '1';
                     btn.removeAttribute('aria-busy');
                     delete btn.dataset.busyActive;
                     delete btn.dataset.originalHtml;
                     delete btn.dataset.originalDisabled;
                 }
             }

             document.addEventListener('DOMContentLoaded', function() {
                 fetchAndAppendPlayers();

                 fetchChatMessages().then(messages => {
                     appendChatMessages(messages);
                 });

                 document.getElementById('playerSearchInput')?.addEventListener('input', function(event) {
                     clearTimeout(playerSearchDebounceTimer);
                     playerSearchDebounceTimer = setTimeout(() => {
                         playerListState.search = event.target.value.trim();
                         playerListState.loadedCount = playerPageSize;
                         fetchAndAppendPlayers();
                     }, 250);
                 });

                 document.getElementById('loadMorePlayersButton')?.addEventListener('click', function() {
                     loadMorePlayers();
                 });

                 document.getElementById('resetChatBtn').addEventListener('click', function() {
                     streamSwalConfirm({
                         title: 'Reset live chat?',
                         text: 'All chat messages will be removed for everyone. This cannot be undone.',
                         confirmButtonText: 'Yes, reset chat',
                         confirmButtonColor: '#d33',
                     }).then(function(result) {
                         if (result.isConfirmed) {
                             resetChat();
                         }
                     });
                 });
                 document.getElementById('toggleChatStatusBtn')?.addEventListener('click', function() {
                     const nextStatus = !isChatEnabled;
                     const actionText = nextStatus ? 'enable' : 'disable';
                     streamSwalConfirm({
                         title: nextStatus ? 'Enable participant chat?' : 'Disable participant chat?',
                         text: nextStatus ?
                             'Viewers will be able to send messages in the live show chat.' :
                             'Viewers will not be able to send new chat messages.',
                         confirmButtonText: nextStatus ? 'Yes, enable chat' : 'Yes, disable chat',
                     }).then(function(result) {
                         if (result.isConfirmed) {
                             toggleLiveChatStatus(nextStatus);
                         }
                     });
                 });

                 document.querySelector('.gallery-hide-on-stream-btn')?.addEventListener('click', function(e) {
                     e.preventDefault();
                     galleryHideOnStream(e.currentTarget);
                     turnTrClassToTableSuccess(e.currentTarget);
                 });


                 fetchGalleryMediaItems();
                 fetchGalleryShowStatus();

                 fetchAllMedia();
                 updateAdminChatUi(isChatEnabled);
             });


             function fetchChatMessages() {
                 // Simulate an API call to fetch chat messages
                 return fetch(`{{ url('api/live-show') }}/{{ $liveShow->id }}/get-live-show-messages`)
                     .then(response => response.json())
                     .then(data => {
                         //  console.log('Chat messages:', data);
                         // Assuming data is an array of messages
                         return data;
                     })
                     .catch(error => {
                         console.error('Error fetching chat messages:', error);
                         return [];
                     });
             }

             function appendChatMessages(messages) {

                 const chatContainer = document.querySelector('#live-chat-messages');
                 chatContainer.innerHTML = ''; // Clear existing messages
                 if (messages.length === 0) {
                     chatContainer.innerHTML = '<p class="text-muted">No messages yet.</p>';
                     return;
                 }

                 messages.forEach(message => {
                     appendSingleMessage(message);
                 });
             }

             function sendMessage() {
                 console.log('Sending message...');
                 // Simulate sending a message via an API call
                 message = document.querySelector('#messageInput').value;
                 if (!message || message.trim() === '') {

                     return;
                 }
                 fetch(`{{ url('admin/live-shows/stream-management') }}/{{ $liveShow->id }}/send-message`, {
                         method: 'POST',
                         headers: {
                             'X-CSRF-TOKEN': '{{ csrf_token() }}',
                             'Accept': 'application/json',
                             'Content-Type': 'application/json'
                         },
                         body: JSON.stringify({
                             message: message
                         })
                     })
                     .then(response => response.json())
                     .then(data => {
                         if (data.data) {

                             document.querySelector('#messageInput').value = '';
                         }
                     })
                     .catch(error => {
                         console.error('Error sending message:', error);
                     });
             }

             function appendSingleMessage(message) {
                 const userMessageBgClass = 'bg-primary text-white';
                 const adminMessageBgClass = 'bg-light text-dark';
                 const chatContainer = document.querySelector('#live-chat-messages');
                 if (message.user !== null) {
                     let bgClass = userMessageBgClass;
                     if (message.user.id == "{{ Auth::guard('admin')->user()->id }}") {
                         bgClass = adminMessageBgClass;
                     }
                     const messageDiv =
                         ` <div class="message alert ${bgClass} d-flex justify-content-between border border-1 rounded-3 p-2">
                                <div><strong>${message.user.name}:</strong> ${message.message}</div>
                                   
                            </div>`;

                     chatContainer.insertAdjacentHTML('beforeend', messageDiv);
                 }
                 //scroll to bottom of chat container
                 chatContainer.scrollTop = chatContainer.scrollHeight;
             }

             function toggleBlockStatusForPlayer(userId, action) {
                 const isBlock = action === 'block';
                 streamSwalConfirm({
                     title: isBlock ? 'Block this player?' : 'Unblock this player?',
                     text: isBlock ?
                         'They will be blocked from live chat until you unblock them.' :
                         'They will be able to participate in live chat again.',
                     confirmButtonText: isBlock ? 'Yes, block' : 'Yes, unblock',
                     confirmButtonColor: isBlock ? '#d33' : '#3085d6',
                 }).then(function(result) {
                     if (!result.isConfirmed) {
                         return;
                     }
                     fetch(`{{ url('admin/live-shows/stream-management') }}/{{ $liveShow->id }}/toggle-block-status-for-player/${userId}`, {
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
                             console.log('Player block status updated:', data);
                             if (data.success) {
                                 streamSwalSuccess(data.message, 'Player updated');
                                 refreshVisiblePlayers();
                             } else {
                                 streamSwalError(data.message || 'Could not update this player.');
                             }
                         })
                         .catch(error => {
                             console.error('Error updating player block status:', error);
                             streamSwalError(error.message || 'Could not update player block status.',
                                 'Chat block update failed');
                         });
                 });
             }

             function resetScore(userId) {
                 streamSwalConfirm({
                     title: 'Reset player score?',
                     text: 'Their score and progress for this live show will be cleared.',
                     confirmButtonText: 'Yes, reset score',
                     confirmButtonColor: '#d33',
                 }).then(function(result) {
                     if (!result.isConfirmed) {
                         return;
                     }
                     fetch(`{{ url('admin/live-shows/stream-management') }}/{{ $liveShow->id }}/reset-score/${userId}`, {
                             method: 'POST',
                             headers: {
                                 'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                 'Accept': 'application/json',
                             },
                         })
                         .then(response => response.json())
                         .then(data => {
                             if (data.success) {
                                 streamSwalSuccess(data.message, 'Score reset');
                                 refreshVisiblePlayers();
                             } else {
                                 streamSwalError(data.message || 'Could not reset this player\'s score.',
                                     'Reset failed');
                             }
                         })
                         .catch(error => {
                             console.error('Error resetting player score:', error);
                             streamSwalError('Please try again in a moment.', 'Could not reset score');
                         });
                 });
             }


             //onlick #fetchPlayersButton execute fetchActivePlayers and appendPlayerList
             document.getElementById('fetchPlayersButton').addEventListener('click', function() {

                 refreshVisiblePlayers();
             });




             function resetChat() {
                 fetch(`{{ route('admin.live-shows.stream-management.reset-chat', ['id' => $liveShow->id]) }}`, {
                         method: 'POST',
                         headers: {
                             'X-CSRF-TOKEN': '{{ csrf_token() }}',
                             'Accept': 'application/json',
                             'Content-Type': 'application/json'
                         }
                     })
                     .then(response => response.json())
                     .then(data => {
                         if (data.success) {
                             updateChatAfterReset();
                             streamSwalSuccess(data.message || 'All chat messages have been cleared.',
                                 'Chat reset');
                         } else {
                             streamSwalError(data.message || 'Could not reset the chat.', 'Reset failed');
                         }
                     })
                     .catch(error => {
                         console.error('Error resetting chat:', error);
                         streamSwalError('Could not reset the chat. Please try again.', 'Reset failed');
                     });
             }

             function updateChatAfterReset() {
                 const chatContainer = document.querySelector('#live-chat-messages');
                 if (chatContainer) {
                     chatContainer.innerHTML = '<p class="text-muted">No messages yet.</p>';
                 }
             }

             function updateAdminChatUi(chatEnabled) {
                 isChatEnabled = !!chatEnabled;
                 const badge = document.getElementById('chatStatusBadge');
                 const btn = document.getElementById('toggleChatStatusBtn');
                 const btnText = document.getElementById('chatToggleBtnText');
                 if (badge) {
                     badge.classList.remove('bg-success', 'bg-danger');
                     badge.classList.add(isChatEnabled ? 'bg-success' : 'bg-danger');
                     badge.textContent = isChatEnabled ? 'Chat Enabled' : 'Chat Disabled';
                 }
                 if (btn) {
                     btn.classList.remove('btn-warning', 'btn-success');
                     btn.classList.add(isChatEnabled ? 'btn-warning' : 'btn-success');
                     btn.setAttribute('title', isChatEnabled ? 'Disable chat for participants' :
                         'Enable chat for participants');
                 }
                 if (btnText) {
                     btnText.textContent = isChatEnabled ? 'Disable Chat' : 'Enable Chat';
                 }
             }

             function toggleLiveChatStatus(chatEnabled) {
                 return fetch(`{{ route('admin.live-shows.stream-management.chat-status', ['id' => $liveShow->id]) }}`, {
                         method: 'POST',
                         headers: {
                             'X-CSRF-TOKEN': '{{ csrf_token() }}',
                             'Accept': 'application/json',
                             'Content-Type': 'application/json'
                         },
                         body: JSON.stringify({
                             chat_enabled: chatEnabled ? 1 : 0
                         })
                     })
                     .then(response => response.json())
                     .then(data => {
                         if (data.success) {
                             updateAdminChatUi(!!data.chat_enabled);
                             streamSwalSuccess(
                                 data.message || (data.chat_enabled ?
                                     'Participants can now send chat messages.' :
                                     'Participant chat has been turned off.'),
                                 'Chat updated');
                         } else {
                             streamSwalError(data.message || 'Could not update chat settings.', 'Update failed');
                         }
                     })
                     .catch(error => {
                         console.error('Error updating chat status:', error);
                         streamSwalError('Could not update chat settings. Please try again.', 'Update failed');
                     });
             }



             function escapeHtml(value) {
                 return String(value ?? '')
                     .replace(/&/g, '&amp;')
                     .replace(/</g, '&lt;')
                     .replace(/>/g, '&gt;')
                     .replace(/"/g, '&quot;')
                     .replace(/'/g, '&#39;');
             }

             function setPlayersLoading() {
                 const activePlayerUlElement = document.getElementById('activePlayersList');
                 activePlayerUlElement.innerHTML =
                     '<tr class="bg-dark align-middle"><td colspan="3"><i class="fas fa-spinner fa-spin me-2"></i> Loading...</td></tr>';
             }

             function updatePlayerListMeta() {
                 const totalUsersCount = document.getElementById('total-users-count');
                 const summary = document.getElementById('playersSearchSummary');
                 const loadMoreButton = document.getElementById('loadMorePlayersButton');
                 const searchActive = playerListState.search !== '';

                 totalUsersCount.innerText = searchActive ?
                     `(${playerListState.filteredUsers}/${playerListState.totalUsers})` :
                     `(${playerListState.totalUsers})`;

                 summary.textContent = searchActive ?
                     `Showing ${playerListState.filteredUsers} matching player(s)` :
                     `Showing ${playerListState.totalUsers} participating player(s)`;

                 loadMoreButton.classList.toggle('d-none', !playerListState.hasMore);
             }

             function buildPlayerListItem(player, index) {
                 const playerName = escapeHtml(player.name);
                 const playerEmail = escapeHtml(player.email);
                 const prizeWon = escapeHtml(player.prize_won ?? '');
                 const playerShowUrl = `{{ url('admin/players') }}/${player.id}`;

                 return `<tr class="bg-dark align-middle">
                    <td class='text-white'>
                        ${index}.
                        <strong class='${player.status != 'eliminated' ? 'text-white' : 'text-secondary'}'>${playerName}</strong>
                        <span class="ms-2 ${player.is_online == 1 ? 'text-success' : 'text-secondary'}">
                            <i class="bi bi-circle-fill" style="font-size: 0.5rem;"></i>
                        </span>
                       
                        ${player.is_winner ? '<i class="bi bi-trophy-fill text-warning"></i>' : ''}
                        <div class='text-white small text-secondary'>${playerEmail}</div>
                        <div class='text-white'>
                            ${player.is_winner && prizeWon ? `Prize:  <span class='badge bg-primary'> ${prizeWon} </span>` : ''}
                        </div>
                    </td>
                    <td class='text-white '>${player.score !== null ? ` ${player.score}` : ''}</td>

                    <td>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" id="playerDropdownMenuButton${player.id}" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-three-dots"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end"
                                id="playerDropdownMenu${player.id}"
                                aria-labelledby="playerDropdownMenuButton${player.id}">
                                <li>
                                    <a class="dropdown-item" target="_blank" href="${playerShowUrl}">
                                        <i class="fas fa-eye"></i>
                                        View Details
                                    </a>
                                </li>
                                <li id="dd_option_toggleBlockStatusForPlayer${player.id}">
                                    <a class="dropdown-item" href="javascript:void(0)"
                                        onclick="toggleBlockStatusForPlayer('${player.id}', '${player.is_blocked ? 'unblock' : 'block'}')">
                                        <i class="fas fa-ban"></i>
                                        ${player.is_blocked ? 'Unblock Player' : 'Block Player'}
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item"
                                        href="javascript:void(0)"
                                        onclick="resetScore('${player.id}')">
                                        <i class="fas fa-sync"></i>
                                        Reset Score
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>`;

             }

             function appendPlayerList(data, options = {}) {
                 const {
                     append = false
                 } = options;
                 const activePlayersList = document.getElementById('activePlayersList');

                 if (!append) {
                     activePlayersList.innerHTML = '';
                 }

                 if (data.users.length === 0 && !append) {
                     activePlayersList.innerHTML =
                         '<tr class="bg-dark align-middle"><td colspan="3">No players found.</td></tr>';
                     return;
                 }

                 const startIndex = append ? (playerListState.loadedCount - data.users.length) : 0;

                 data.users.forEach((player, index) => {
                     activePlayersList.insertAdjacentHTML('beforeend', buildPlayerListItem(player, startIndex + index +
                         1));
                 });
             }

             function fetchActivePlayers({
                 skip = 0,
                 take = playerPageSize
             } = {}) {
                 const query = new URLSearchParams({
                     skip,
                     take,
                 });

                 if (playerListState.search !== '') {
                     query.set('search', playerListState.search);
                 }

                 return fetch(`{{ url('api/live-show') }}/{{ $liveShow->id }}/get-live-show-users?${query.toString()}`)
                     .then(response => response.json())
                     .then(data => {
                         return {
                             users: data.users.map(player => ({
                                 name: player.name,
                                 id: player.id,
                                 email: player.email,
                                 is_online: player.is_online,
                                 is_winner: player.is_winner,
                                 prize_won: player.prize_won,
                                 status: player.status,
                                 score: player.score,
                                 is_blocked: player.is_blocked
                             })),
                             totalUsers: data.totalUsers,
                             filteredUsers: data.filteredUsers ?? data.totalUsers,
                             hasMore: !!data.hasMore,
                         };
                     })
                     .catch(error => {
                         console.error('Error fetching active players:', error);
                         return {
                             users: [],
                             totalUsers: 0,
                             filteredUsers: 0,
                             hasMore: false,
                         };
                     });
             }

             function refreshVisiblePlayers() {
                 const take = Math.max(playerListState.loadedCount, playerPageSize);
                 setPlayersLoading();

                 return fetchActivePlayers({
                     skip: 0,
                     take
                 }).then(data => {
                     playerListState.loadedCount = Math.max(data.users.length, playerPageSize);
                     playerListState.totalUsers = data.totalUsers;
                     playerListState.filteredUsers = data.filteredUsers;
                     playerListState.hasMore = data.hasMore;
                     appendPlayerList(data);
                     updatePlayerListMeta();
                 });
             }

             function fetchAndAppendPlayers() {
                 setPlayersLoading();
                 playerListState.loadedCount = playerPageSize;

                 return fetchActivePlayers({
                     skip: 0,
                     take: playerListState.loadedCount
                 }).then(data => {
                     playerListState.totalUsers = data.totalUsers;
                     playerListState.filteredUsers = data.filteredUsers;
                     playerListState.hasMore = data.hasMore;
                     appendPlayerList(data);
                     updatePlayerListMeta();
                 });
             }

             function loadMorePlayers() {
                 const loadMoreButton = document.getElementById('loadMorePlayersButton');
                 loadMoreButton.disabled = true;
                 loadMoreButton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Loading...';

                 return fetchActivePlayers({
                     skip: playerListState.loadedCount,
                     take: playerPageSize
                 }).then(data => {
                     playerListState.loadedCount += data.users.length;
                     playerListState.totalUsers = data.totalUsers;
                     playerListState.filteredUsers = data.filteredUsers;
                     playerListState.hasMore = data.hasMore;
                     appendPlayerList(data, {
                         append: true
                     });
                     updatePlayerListMeta();
                 }).finally(() => {
                     loadMoreButton.disabled = false;
                     loadMoreButton.innerHTML = '<i class="fas fa-plus-circle me-1"></i> Load More Players';
                 });
             }

             const timerDiv = document.querySelector('#quizTimer');


             // Function to display a countdown timer in the div#quizTimer and hide it after countdown finishes
             function showQuizTimer(seconds, quizId) {
                 if (!timerDiv) return;

                 let timeLeft = parseInt(seconds, 10);
                 timerDiv.style.display = 'flex';
                 timerDiv.querySelector('#quizTimerText').innerText = timeLeft;

                 // Clear any previous timer to avoid stacking
                 if (timerDiv._quizTimerInterval) {
                     clearInterval(timerDiv._quizTimerInterval);
                 }



                 timerDiv._quizTimerInterval = setInterval(function() {
                     timeLeft--;
                     if (timeLeft > 0) {
                         timerDiv.querySelector('#quizTimerText').innerText = timeLeft;
                     } else {
                         timerDiv.querySelector('#quizTimerText').innerText = '0';
                         clearInterval(timerDiv._quizTimerInterval);
                         setTimeout(() => {
                             timerDiv.style.display = 'none';
                         }, 500); // Give a short delay before hiding

                         // Fetch players list 5 seconds after timer finishes
                         setTimeout(() => {
                             console.log('viewing responses after timer finishes..');
                             refreshVisiblePlayers();
                             viewResponses('{{ $liveShow->id }}', quizId, null);
                         }, 5000);
                     }
                 }, 1000);

             }

             function hideQuizTimer() {
                 timerDiv.querySelector('#quizTimerText').innerText = '0';
                 clearInterval(timerDiv._quizTimerInterval);
                 setTimeout(() => {
                     timerDiv.style.display = 'none';
                 }, 500); // Give a short delay before hiding
             }


             function submitQuizTimerForm(event, quizId) {
                 event.preventDefault();
                 const form = document.getElementById(`quiz-timer-form-${quizId}`);
                 const formData = new FormData(form);
                 const seconds = formData.get('seconds');
                 const isLast = formData.get('is_last') ? true : false;
                 const startBtn = form.querySelector('button[type="submit"]');
                 setBtnBusy(startBtn, true, 'Starting\u2026');

                 fetch(`{{ url('admin/live-shows/stream-management') }}/{{ $liveShow->id }}/quizzes/${quizId}/send-quiz-question`, {
                         method: 'POST',
                         headers: {
                             'X-CSRF-TOKEN': '{{ csrf_token() }}',
                             'Accept': 'application/json',
                         },
                         body: formData
                     })
                     .then(response => response.json())
                     .then(data => {
                         console.log('Quiz question sent:', data);
                     })
                     .catch(error => {
                         console.error('Error sending quiz question:', error);
                         streamSwalError('Could not start the quiz. Please try again.', 'Start failed');
                     })
                     .finally(() => {
                         setBtnBusy(startBtn, false);
                         showQuizTimer(seconds, quizId);
                     });
             }
         </script>


         <script>
             var channel2 = pusher.subscribe('live-show.{{ $liveShow->id }}');

             // System subscription event
             channel2.bind('pusher:subscription_succeeded', function() {
                 console.log('Subscribed message event successfully!');
             });

             channel2.bind('LiveShowMessageEvent', function(data) {
                 //  console.log('new message:', data.data);
                 appendSingleMessage(data.data);
             });

             var channelResetChat = pusher.subscribe('live-show.{{ $liveShow->id }}');
             channelResetChat.bind('pusher:subscription_succeeded', function() {
                 console.log('Reset chat channel subscribed successfully!');
             });
             channelResetChat.bind('ResetChatEvent', function() {
                 //  console.log('Chat reset event received');
                 const chatContainer = document.querySelector('#live-chat-messages');
                 if (chatContainer) {
                     chatContainer.innerHTML = '<p class="text-muted">No messages yet.</p>';
                 }
             });
             channelResetChat.bind('LiveShowChatStatusUpdatedEvent', function(data) {
                 updateAdminChatUi(!!data.chatEnabled);
             });



             function removeQuiz(quizId, btn) {
                 setBtnBusy(btn, true, 'Hiding\u2026');
                 fetch(`{{ url('admin/live-shows/stream-management') }}/{{ $liveShow->id }}/quizzes/${quizId}/remove-quiz-question`, {
                         method: 'POST',
                         headers: {
                             'X-CSRF-TOKEN': '{{ csrf_token() }}',
                             'Accept': 'application/json',
                         },
                     })
                     .then(response => response.json())
                     .then(data => {
                         hideQuizTimer();
                     })
                     .catch(error => {
                         console.error('Error removing quiz question:', error);
                         streamSwalError('Could not hide the quiz question. Please try again.', 'Hide failed');
                     })
                     .finally(() => {
                         setBtnBusy(btn, false);
                     });
             }

             function setAnnounceWinnersLoading(isLoading) {
                 const btn = document.getElementById('announceWinnersBtn');
                 const label = document.getElementById('announceWinnersBtnContent');
                 const loader = document.getElementById('announceWinnersBtnLoader');
                 const done = document.getElementById('announceWinnersBtnDone');
                 if (!btn || !label || !loader || !done) {
                     return;
                 }
                 if (liveShowWinnersAnnounced) {
                     return;
                 }
                 if (isLoading) {
                     btn.disabled = true;
                     label.classList.add('d-none');
                     done.classList.add('d-none');
                     loader.classList.remove('d-none');
                 }
             }

             function clearAnnounceWinnersLoading() {
                 const btn = document.getElementById('announceWinnersBtn');
                 const label = document.getElementById('announceWinnersBtnContent');
                 const loader = document.getElementById('announceWinnersBtnLoader');
                 if (!btn || !label || !loader) {
                     return;
                 }
                 loader.classList.add('d-none');
                 if (liveShowWinnersAnnounced) {
                     return;
                 }
                 btn.disabled = false;
                 label.classList.remove('d-none');
             }

             function applyAnnounceWinnersCompleted() {
                 const btn = document.getElementById('announceWinnersBtn');
                 const label = document.getElementById('announceWinnersBtnContent');
                 const loader = document.getElementById('announceWinnersBtnLoader');
                 const done = document.getElementById('announceWinnersBtnDone');
                 const ack = document.getElementById('announceWinnersAckMessage');
                 if (btn) {
                     btn.disabled = true;
                 }
                 if (label) {
                     label.classList.add('d-none');
                 }
                 if (loader) {
                     loader.classList.add('d-none');
                 }
                 if (done) {
                     done.classList.remove('d-none');
                 }
                 if (ack) {
                     ack.classList.remove('d-none');
                 }
                 liveShowWinnersAnnounced = true;
                 const unBtn = document.getElementById('unannounceWinnersBtn');
                 if (unBtn) {
                     unBtn.classList.remove('d-none');
                     unBtn.disabled = false;
                 }
             }

             function setUnannounceWinnersLoading(isLoading) {
                 const btn = document.getElementById('unannounceWinnersBtn');
                 const label = document.getElementById('unannounceWinnersBtnLabel');
                 const loader = document.getElementById('unannounceWinnersBtnLoader');
                 if (!btn || !label || !loader) {
                     return;
                 }
                 if (isLoading) {
                     btn.disabled = true;
                     label.classList.add('d-none');
                     loader.classList.remove('d-none');
                 } else {
                     loader.classList.add('d-none');
                     label.classList.remove('d-none');
                     btn.disabled = false;
                 }
             }

             function applyUnannounceWinnersCompleted() {
                 const btn = document.getElementById('announceWinnersBtn');
                 const label = document.getElementById('announceWinnersBtnContent');
                 const loader = document.getElementById('announceWinnersBtnLoader');
                 const done = document.getElementById('announceWinnersBtnDone');
                 const ack = document.getElementById('announceWinnersAckMessage');
                 const unBtn = document.getElementById('unannounceWinnersBtn');
                 liveShowWinnersAnnounced = false;
                 if (btn) {
                     btn.disabled = false;
                 }
                 if (label) {
                     label.classList.remove('d-none');
                 }
                 if (loader) {
                     loader.classList.add('d-none');
                 }
                 if (done) {
                     done.classList.add('d-none');
                 }
                 if (ack) {
                     ack.classList.add('d-none');
                 }
                 if (unBtn) {
                     unBtn.classList.add('d-none');
                     unBtn.disabled = false;
                 }
             }

             function unannounceWinners() {
                 if (!liveShowWinnersAnnounced) {
                     return;
                 }
                 streamSwalConfirm({
                     title: 'Un-announce winners?',
                     text: 'This clears the “winners announced” flag so you can run announce winners again. It does not remove winner assignments from players.',
                     confirmButtonText: 'Yes, un-announce',
                 }).then(function(result) {
                     if (!result.isConfirmed) {
                         return;
                     }
                     setUnannounceWinnersLoading(true);
                     fetch(`{{ route('admin.live-shows.unannounce-winners', ['liveShowId' => $liveShow->id]) }}`, {
                             method: 'POST',
                             headers: {
                                 'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                 'Accept': 'application/json',
                             },
                         })
                         .then(function(response) {
                             return response.json().then(function(data) {
                                 return {
                                     ok: response.ok,
                                     status: response.status,
                                     data: data
                                 };
                             });
                         })
                         .then(function(result) {
                             setUnannounceWinnersLoading(false);
                             if (!result.ok) {
                                 streamSwalError(
                                     (result.data && result.data.message) ? result.data.message :
                                     'Could not update winners announcement status.',
                                     'Update failed');
                                 return;
                             }
                             applyUnannounceWinnersCompleted();
                             streamSwalSuccess(
                                 (result.data && result.data.message) ? result.data.message :
                                 'Winners announcement cleared.',
                                 'Updated');
                         })
                         .catch(function(error) {
                             console.error('Error unannouncing winners:', error);
                             setUnannounceWinnersLoading(false);
                             streamSwalError('Could not update winners announcement status.', 'Update failed');
                         });
                 });
             }

             function updateWinners() {
                 if (liveShowWinnersAnnounced) {
                     streamSwalWarning('Winners have already been announced for this live show.', 'Already announced');
                     return;
                 }
                 streamSwalConfirm({
                     title: 'Announce winners?',
                     text: 'This will determine winners by score, notify participants, and queue winner notification emails.',
                     confirmButtonText: 'Yes, announce winners',
                 }).then(function(result) {
                     if (!result.isConfirmed) {
                         return;
                     }
                     setAnnounceWinnersLoading(true);
                     fetch(`{{ route('admin.live-shows.update-winners', ['liveShowId' => $liveShow->id]) }}`, {
                             method: 'POST',
                             headers: {
                                 'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                 'Accept': 'application/json',
                             },
                         })
                         .then(function(response) {
                             return response.json().then(function(data) {
                                 return {
                                     ok: response.ok,
                                     status: response.status,
                                     data: data
                                 };
                             });
                         })
                         .then(function(result) {
                             if (result.status === 422) {
                                 applyAnnounceWinnersCompleted();
                                 streamSwalWarning(
                                     result.data.message ||
                                     'Winners have already been announced for this live show.',
                                     'Already announced');
                                 return;
                             }
                             if (!result.ok) {
                                 clearAnnounceWinnersLoading();
                                 streamSwalError(
                                     (result.data && result.data.message) ? result.data.message :
                                     'Could not announce winners. Please try again.',
                                     'Update failed');
                                 return;
                             }
                             applyAnnounceWinnersCompleted();
                             streamSwalSuccess(
                                 (result.data && result.data.message) ? result.data.message :
                                 'Winners have been announced for this live show.',
                                 'Winners announced');
                             var fetchBtn = document.getElementById('fetchPlayersButton');
                             if (fetchBtn) {
                                 fetchBtn.click();
                             }
                         })
                         .catch(function(error) {
                             console.error('Error updating winners:', error);
                             clearAnnounceWinnersLoading();
                             streamSwalError('Could not announce winners. Please try again.', 'Update failed');
                         });
                 });
             }

             function hideWinnerTab(btn) {
                 streamSwalConfirm({
                     title: 'Hide winner tab?',
                     text: 'Participants will no longer see the winners tab in the live show.',
                     confirmButtonText: 'Yes, hide tab',
                 }).then(function(result) {
                     if (!result.isConfirmed) {
                         return;
                     }
                     setBtnBusy(btn, true, 'Hiding\u2026');
                     fetch(`{{ route('admin.live-shows.stream-management.hide-winners-tab', ['id' => $liveShow->id]) }}`, {
                             method: 'POST',
                             headers: {
                                 'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                 'Accept': 'application/json',
                             },
                         })
                         .then(response => response.json())
                         .then(data => {
                             if (data.success) {
                                 streamSwalSuccess(data.message || 'The winners tab is now hidden for participants.',
                                     'Winners tab hidden');
                             } else {
                                 streamSwalError(data.message || 'Could not hide the winners tab.',
                                     'Request failed');
                             }
                         })
                         .catch(error => {
                             console.error('Error hiding winners tab:', error);
                             streamSwalError('Could not hide the winners tab. Please try again.', 'Request failed');
                         })
                         .finally(() => {
                             setBtnBusy(btn, false);
                         });
                 });
             }

             function showWinnerTab(btn) {
                 streamSwalConfirm({
                     title: 'Show winner tab?',
                     text: 'Participants will be switched to the winners tab in the live show.',
                     confirmButtonText: 'Yes, show tab',
                 }).then(function(result) {
                     if (!result.isConfirmed) {
                         return;
                     }
                     setBtnBusy(btn, true, 'Showing\u2026');
                     fetch(`{{ route('admin.live-shows.stream-management.show-winners-tab', ['id' => $liveShow->id]) }}`, {
                             method: 'POST',
                             headers: {
                                 'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                 'Accept': 'application/json',
                             },
                         })
                         .then(response => response.json())
                         .then(data => {
                             if (data.success) {
                                 streamSwalSuccess(data.message || 'The winners tab is now shown for participants.',
                                     'Winners tab shown');
                             } else {
                                 streamSwalError(data.message || 'Could not show the winners tab.',
                                     'Request failed');
                             }
                         })
                         .catch(error => {
                             console.error('Error showing winners tab:', error);
                             streamSwalError('Could not show the winners tab. Please try again.', 'Request failed');
                         })
                         .finally(() => {
                             setBtnBusy(btn, false);
                         });
                 });
             }
         </script>
         <script>
             document.addEventListener('DOMContentLoaded', function() {
                 console.log('Document loaded');

                 $('.question-slider').slick({
                     infinite: false,
                     slidesToShow: 1,
                     slidesToScroll: 1,
                     arrows: true,
                     dots: true,
                     adaptiveHeight: true
                 });
             });


             document.getElementById('live-show-status-form').addEventListener('submit', function(event) {
                 event.preventDefault();
                 const status = document.getElementById('liveShowStatusSelect').value;

                 updateLiveShowStatus(status);
             });



             function updateLiveShowStatus(status) {
                 streamSwalConfirm({
                     title: 'Update live show status?',
                     text: 'The status will be set to "' + status + '" for this live show.',
                     confirmButtonText: 'Yes, update status',
                 }).then(function(result) {
                     if (!result.isConfirmed) {
                         return;
                     }
                     $.ajax({
                         url: "{{ route('admin.live-shows.update-live-show', ['id' => $liveShow->id]) }}",
                         method: "POST",
                         data: JSON.stringify({
                             status: status
                         }),
                         headers: {
                             "X-CSRF-TOKEN": "{{ csrf_token() }}",
                             "Accept": "application/json"
                         },
                         contentType: "application/json",
                         success: function(data) {
                             streamSwalSuccess(data.message || 'Live show status was updated.',
                                 'Status updated');
                             liveShowStatus = data.status;
                         },
                         error: function(xhr, status, error) {
                             console.error("Error ending live show:", error);
                             console.log(xhr.responseText);
                             var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON
                                 .message :
                                 'Could not update the live show status. Please try again.';
                             streamSwalError(msg, 'Update failed');
                         }
                     });
                 });
             }


             function viewResponses(liveShowId, quizId, btn = null) {

                 if (btn) {
                     setBtnBusy(btn, true, 'Loading\u2026');
                 }
                 fetch(`{{ url('admin/live-shows') }}/${liveShowId}/get-users-quiz-responses/${quizId}`, {
                         method: 'GET',
                         headers: {
                             'X-CSRF-TOKEN': '{{ csrf_token() }}',
                             'Accept': 'application/json',
                         },
                     }).then(response => response.json())
                     .then(data => {
                         console.log('responses fetched:', data);
                         let stats = data.statistics;
                         stats.forEach(stat => {
                             let bar = document.getElementById(`option-result-bar-${stat.quiz_option_id}`);
                             let label = document.getElementById(`option-result-label-${stat.quiz_option_id}`);
                             if (bar) {
                                 bar.style.width = `${stat.percentage}%`;
                             }
                             if (label) {
                                 label.textContent = `${stat.percentage}% (${stat.total_response_for_option})`;
                             }
                         });
                     })
                     .catch(error => {
                         console.error('Error fetching quiz responses:', error);
                         streamSwalError('Could not load quiz responses. Please try again.', 'Load failed');
                     })
                     .finally(() => {
                         setBtnBusy(btn, false);
                     });
             }



             document.getElementById('resetGameButton').addEventListener('click', function() {
                 streamSwalConfirm({
                     title: 'Reset the game?',
                     text: 'This will remove all current player progress for this live show.',
                     confirmButtonText: 'Yes, reset game',
                     confirmButtonColor: '#d33',
                 }).then(function(result) { //add spinner to the button
                     const btn = document.getElementById('resetGameButton');
                     if (btn) {
                         btn.disabled = true;
                         btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Resetting...';
                     }
                     if (!result.isConfirmed) {
                         return;
                     }
                     fetch(`{{ route('admin.live-shows.reset-game', ['id' => $liveShow->id]) }}`, {
                             method: 'POST',
                             headers: {
                                 'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                 'Accept': 'application/json',
                             },
                         })
                         .then(response => response.json())
                         .then(data => {
                             streamSwalSuccess(data.message || 'The game has been reset.',
                                 'Game reset');
                             //refreshVisiblePlayers();
                             window.location.reload();
                         })
                         .catch(error => {
                             console.error('Error resetting game:', error);
                             streamSwalError('Could not reset the game. Please try again.', 'Reset failed');
                         });
                 });
             });
         </script>


         <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>

         <script>
             generateQRCode('{{ url('live-show-play/' . $liveShow->id) }}');
             // Function to generate the QR code
             function generateQRCode(link) {
                 // 1. Get the container element
                 const qrcodeContainer = document.getElementById('qrcode');

                 // 2. Clear any existing QR code before generating a new one
                 // The library may append a new canvas/image if you don't clear it.
                 qrcodeContainer.innerHTML = '';

                 // 3. Get the data from the input field
                 const dataToEncode = link;

                 // Check if the input is not empty
                 if (dataToEncode.trim() === '') {
                     streamSwalWarning(
                         'Add a link or text in the field above, or use the default join link, then generate again.',
                         'Nothing to encode');
                     return;
                 }

                 // 4. Generate the QR Code using the QRCode constructor
                 // Syntax: new QRCode(element, options);
                 const qrcode = new QRCode(qrcodeContainer, {
                     text: dataToEncode,
                     width: 120,
                     height: 120,
                     colorDark: "#000000",
                     colorLight: "#ffffff",
                     correctLevel: QRCode.CorrectLevel.H // High error correction level
                 });

                 //  console.log('QR Code generated for:', dataToEncode);
             }

             function announcementEventTest() {
                 fetch('{{ route('admin.announcement.send') }}', {
                     method: 'POST',
                     headers: {
                         'X-CSRF-TOKEN': '{{ csrf_token() }}',
                         'Accept': 'application/json',
                     },
                 }).then(response => response.text()).then(data => {
                     //  console.log('Announcement sent:', data);
                 }).catch(error => {
                     console.error('Error sending announcement:', error);
                 });
             }



             function refreshPreview() {
                 const iframe = document.querySelector('.live-show-preview-iframe');
                 iframe.src = '{{ url('live-show-play/' . $liveShow->id) }}?preview=true';
                 iframe.style.display = 'block';
                 iframe.style.opacity = '0';
                 setTimeout(() => {
                     iframe.style.opacity = '1';
                 }, 100);
             }


             /*all media gallery functions*/
             const liveShowId = {{ $liveShow->id }};
             const galleryAttachUrl = '{{ route('admin.media-gallery.attach-to-live-show') }}';
             const galleryDetachUrl = '{{ route('admin.media-gallery.detach-from-live-show') }}';
             const galleryReorderUrl = '{{ route('admin.media-gallery.reorder') }}';
             const galleryMediaItemsUrl = '{{ route('admin.media-gallery.items', ['id' => $liveShow->id]) }}';
             const galleryShowOnStreamUrl =
                 '{{ route('admin.live-shows.stream-management.show-gallery-image', ['id' => $liveShow->id]) }}';
             const galleryHideOnStreamUrl =
                 '{{ route('admin.live-shows.stream-management.hide-gallery-image', ['id' => $liveShow->id]) }}';
             const galleryCsrf = '{{ csrf_token() }}';

             const galleryShowStatusUrl =
                 '{{ route('admin.live-shows.gallery-stream-state', ['live_show' => $liveShow->id]) }}';

             const allMediaUrl = '{{ route('admin.media-gallery.all') }}';



             function galleryShowOnStream(mediaId, btn) {
                 if (!btn) return;
                 setBtnBusy(btn, true, 'Showing\u2026');
                 fetch(galleryShowOnStreamUrl, {
                         method: 'POST',
                         headers: {
                             'X-CSRF-TOKEN': galleryCsrf,
                             'Accept': 'application/json',
                             'Content-Type': 'application/json'
                         },
                         body: JSON.stringify({
                             gallery_media_id: parseInt(mediaId, 10)
                         })
                     })
                     .then(r => r.json())
                     .then(data => {
                         if (data.success) {
                             updateGalleryShowStatus('showing');
                         }
                     })
                     .catch(err => {
                         console.error('Show on stream error:', err);
                         streamSwalError('Could not show this item on stream. Please try again.', 'Show failed');
                     })
                     .finally(() => {
                         setBtnBusy(btn, false);
                     });
             }
             //funtion to turn tr class of current button closet tr to table-success and other's without any class
             function turnTrClassToTableSuccess(btn) {
                 const trs = document.querySelectorAll('tr');
                 trs.forEach(tr => {
                     if (tr !== btn.closest('tr')) {
                         tr.classList.remove('table-success');
                     }
                 });

                 const tr = btn.closest('tr');
                 if (tr) {
                     tr.classList.add('table-success');
                 }

             }

             function galleryHideOnStream(btn) {
                 if (!btn) return;
                 setBtnBusy(btn, true, 'Hiding\u2026');
                 fetch(galleryHideOnStreamUrl, {
                         method: 'POST',
                         headers: {
                             'X-CSRF-TOKEN': galleryCsrf,
                             'Accept': 'application/json',
                             'Content-Type': 'application/json'
                         }
                     })
                     .then(r => r.json())
                     .then(data => {
                         if (data.success) {
                             updateGalleryShowStatus('hidden');
                         }
                     })
                     .catch(err => {
                         console.error('Hide on stream error:', err);
                         streamSwalError('Could not hide this item on stream. Please try again.', 'Hide failed');
                     })
                     .finally(() => {
                         setBtnBusy(btn, false);
                     });
             }

             function galleryAttach(mediaId, btn) {
                 btn.disabled = true;
                 fetch(galleryAttachUrl, {
                         method: 'POST',
                         headers: {
                             'X-CSRF-TOKEN': galleryCsrf,
                             'Accept': 'application/json',
                             'Content-Type': 'application/json'
                         },
                         body: JSON.stringify({
                             live_show_id: liveShowId,
                             gallery_media_id: parseInt(mediaId, 10)
                         })
                     })
                     .then(r => r.json())
                     .then(data => {
                         if (data.success) {
                             // Remove the original card (if present), or handle row changes as needed
                             const row = attachGalleryMediaItemRow(data.media, data.idx !== undefined ? data.idx : 0);
                             const tbody = document.getElementById('attached-media-list');
                             if (tbody) {
                                 tbody.insertAdjacentHTML('beforeend', row);
                             }
                             const emptyEl = document.getElementById('gallery-attached-empty');
                             if (emptyEl) emptyEl.remove();
                             updateRowIndices();
                             initSortable();
                             persistOrder();
                         } else {
                             streamSwalError(data.message || 'Could not attach this item to the stream.',
                                 'Attach failed');
                         }
                     })
                     .catch(err => console.error('Gallery attach error:', err))
                     .finally(() => {
                         btn.disabled = false;
                     });
             }

             function ensureGalleryAttachedEmptyRow() {
                 const tbody = document.getElementById('attached-media-list');
                 if (!tbody) {
                     return;
                 }
                 const hasAttached = tbody.querySelector('tr.gallery-media-card[data-media-id]');
                 let emptyRow = document.getElementById('gallery-attached-empty');
                 if (!hasAttached) {
                     if (!emptyRow) {
                         tbody.insertAdjacentHTML('beforeend',
                             '<tr id="gallery-attached-empty" class="text-muted">' +
                             '<td colspan="6" class="text-center py-3 small">No media attached. Use &quot;Add from gallery&quot; to attach items.</td>' +
                             '</tr>'
                         );
                     }
                 } else if (emptyRow) {
                     emptyRow.remove();
                 }
             }

             function galleryDetach(mediaId, btn) {
                 const row = btn.closest('tr.gallery-media-card');
                 if (!row) {
                     return;
                 }
                 streamSwalConfirm({
                     title: 'Remove from stream?',
                     text: 'This media will be removed from this live show’s attached list. You can add it again later.',
                     confirmButtonText: 'Yes, remove',
                     confirmButtonColor: '#d33',
                 }).then(function(result) {
                     if (!result.isConfirmed) {
                         return;
                     }
                     btn.disabled = true;
                     fetch(galleryDetachUrl, {
                             method: 'POST',
                             headers: {
                                 'X-CSRF-TOKEN': galleryCsrf,
                                 'Accept': 'application/json',
                                 'Content-Type': 'application/json'
                             },
                             body: JSON.stringify({
                                 live_show_id: liveShowId,
                                 gallery_media_id: parseInt(mediaId, 10)
                             })
                         })
                         .then(function(r) {
                             return r.json().then(function(data) {
                                 return {
                                     ok: r.ok,
                                     data: data
                                 };
                             });
                         })
                         .then(function(res) {
                             if (!res.ok || !res.data.success) {
                                 streamSwalError(
                                     (res.data && res.data.message) ? res.data.message :
                                     'Could not remove this item from the stream.',
                                     'Remove failed');
                                 return;
                             }
                             row.remove();
                             updateRowIndices();
                             persistOrder();
                             ensureGalleryAttachedEmptyRow();
                             streamSwalSuccess(res.data.message || 'Removed from this stream.', 'Removed');
                         })
                         .catch(function(err) {
                             console.error('Gallery detach error:', err);
                             streamSwalError('Could not remove this item. Please try again.', 'Remove failed');
                         })
                         .finally(function() {
                             btn.disabled = false;
                         });
                 });
             }

             function fetchGalleryMediaItems() {
                 return fetch(galleryMediaItemsUrl, {
                         method: 'GET',
                         headers: {
                             'X-CSRF-TOKEN': galleryCsrf,
                         }
                     })
                     .then(r => r.json())
                     .then(data => {
                         console.log('Gallery media items:', data);
                         if (data.success) {
                             //append gallery media items to gallery-available-list
                             const galleryAvailableList = document.getElementById('attached-media-list');
                             if (galleryAvailableList) {
                                 galleryAvailableList.innerHTML = '';
                                 data.media.forEach((media, idx) => {

                                     galleryAvailableList.insertAdjacentHTML('beforeend', attachGalleryMediaItemRow(
                                         media, idx));
                                 });
                                 if (!data.media.length) {
                                     ensureGalleryAttachedEmptyRow();
                                 }
                             }
                             initSortable();
                         } else {
                             streamSwalError(data.message || 'Could not load gallery items for this stream.',
                                 'Gallery load failed');
                             return [];
                         }
                     })
                     .catch(err => console.error('Gallery media items error:', err))
             }

             function attachGalleryMediaItemRow(data, idx) {
                 return `
                <tr class="gallery-media-card" data-media-id="${data.id}" data-attached="1">
                    <td colspan="100" style="padding:0; border:none;">
                        <div class="  d-flex    gap-2 py-3 px-2">
                             
                                  <div class="drag-handle mb-2" style="cursor: grab;">
                                <i class="fas fa-grip-vertical text-muted"></i>
                                </div>
                            
                          <div class="    ">
                                <img src="${data.is_image ? data.path : (data.thumbnail ?? data.path)}"
                                    alt=""
                                    style="width: 64px; height: 64px; object-fit: cover; border-radius: 6px; border: 1px solid #555;">

                                    <span class="badge ${data.type === 'video' ? 'bg-primary' : 'bg-warning text-dark'}">
                                    ${data.type ?? ''}
                                </span>
                                <div class="mb-1   fw-semibold" style="width:100%;">
                                ${data.title || '—'}
                            </div>
                            
                            <div class="d-flex gap-2 flex-wrap   mt-1">
                                <button type="button"
                                    class="btn btn-sm btn-success gallery-show-on-stream-btn"
                                    onclick="galleryShowOnStream('${data.id}', this)"
                                    data-media-id="${data.id}"
                                    title="Show on live stream">
                                    <i class="fas fa-tv"></i>
                                    Show
                                </button>
                                <button type="button"
                                    class="btn btn-sm btn-warning gallery-hide-on-stream-btn"
                                    onclick="galleryHideOnStream(this)"
                                    data-media-id="${data.id}"
                                    title="Hide on live stream">
                                    <i class="fas fa-eye-slash"></i>
                                    Hide
                                </button>
                                <button type="button"
                                    class="btn btn-sm btn-outline-danger gallery-detach-btn"
                                    data-media-id="${data.id}"
                                    title="Remove from stream"
                                    onclick="galleryDetach('${data.id}', this)">
                                    <i class="fas fa-times"></i>
                                </button>
                                <button type="button"
                                    class="btn btn-sm btn-secondary" title="Preview"
                                    onclick="openMediaPreviewModal('${data.is_image ? data.path : (data.thumbnail ?? data.path)}')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            </div>
                            
                             
                        </div>
                    </td>
                </tr>
           
            `;
             }

             function allMediaItemCard(data, idx) {
                 return `
                <div class="col-6 col-md-3 col-lg-3">
                    <div class="card">
                        <img src="${data.is_image ? data.path : (data.thumbnail ?? data.path)}" class="card-img-top w-100" alt="" style="height: 200px; object-fit: cover; ">    
                        <div class="card-body">
                            <p class="card-title">${data.title || '—'}</p>
                            <p class="card-text">${data.type ?? ''}</p>
                        </div>
                        <div class="card-footer">
                            <button type="button" class="btn btn-sm btn-primary" onclick="attachMediaItem(this, '${data.id}')">Attach</button>
                        </div>
                    </div>
                </div>
                `;
             }

             function fetchGalleryShowStatus() {
                 return fetch(galleryShowStatusUrl, {
                         method: 'GET',
                         headers: {
                             'X-CSRF-TOKEN': galleryCsrf,
                         }
                     })
                     .then(r => r.json())
                     .then(data => {
                         //  console.log('Gallery show status:', data);

                         updateGalleryShowStatus(data.showing ? 'showing' : 'hidden');

                     })
                     .catch(err => console.error('Gallery show status error:', err))
             }

             function updateGalleryShowStatus(status) {
                 const galleryShowStatus = document.getElementById('gallery-show-status');
                 if (galleryShowStatus) {
                     if (status === 'showing') {
                         galleryShowStatus.innerHTML = `<span class="badge bg-success">Showing</span>`;
                     } else if (status === 'hidden') {
                         galleryShowStatus.innerHTML = `<span class="badge bg-danger">Hidden</span>`;
                     } else {
                         galleryShowStatus.innerHTML = `<span class="badge bg-warning">Unknown</span>`;
                     }
                     galleryShowStatus.innerHTML = `<span class="badge bg-success">${status}</span>`;
                 }
             }



             function openMediaPreviewModal(url) {
                 const modal = document.getElementById('media-preview-modal');
                 if (modal) {
                     const img = modal.querySelector('img');
                     if (img) img.src = url;

                     // Use Bootstrap's Modal API to show modal
                     if (typeof bootstrap !== 'undefined') {
                         let bsModal = bootstrap.Modal.getOrCreateInstance(modal);
                         bsModal.show();
                     }
                 }
             }

             function fetchAllMedia() {
                 return fetch(allMediaUrl, {
                     method: 'GET',
                     headers: {
                         'X-CSRF-TOKEN': galleryCsrf,
                     }
                 }).then(r => r.json()).then(data => {
                     //  console.log('All media:', data);
                     if (data.success) {
                         const allMediaList = document.getElementById('select-media-modal-list');
                         if (allMediaList) {
                             allMediaList.innerHTML = '';
                             data.media.forEach((media, idx) => {
                                 allMediaList.insertAdjacentHTML('beforeend', allMediaItemCard(media,
                                     idx));
                             });
                         }
                     }
                 });
             }

             function initSortable() {
                 const tbody = document.getElementById('attached-media-list');
                 if (!tbody || tbody._sortable) return;

                 tbody._sortable = new Sortable(tbody, {
                     handle: '.drag-handle',
                     animation: 150,
                     ghostClass: 'sortable-ghost',
                     chosenClass: 'sortable-chosen',
                     onEnd: function() {
                         updateRowIndices();
                         persistOrder();
                     }
                 });
             }

             function updateRowIndices() {
                 const rows = document.querySelectorAll('#attached-media-list tr');
                 rows.forEach((row, i) => {
                     const cell = row.querySelector('.row-index');
                     if (cell) cell.textContent = i + 1;
                 });
             }

             function persistOrder() {
                 const rows = document.querySelectorAll('#attached-media-list tr[data-media-id]');
                 const order = Array.from(rows).map(r => parseInt(r.dataset.mediaId, 10));
                 if (order.length === 0) {
                     return;
                 }

                 fetch(galleryReorderUrl, {
                         method: 'POST',
                         headers: {
                             'X-CSRF-TOKEN': galleryCsrf,
                             'Accept': 'application/json',
                             'Content-Type': 'application/json'
                         },
                         body: JSON.stringify({
                             live_show_id: liveShowId,
                             order: order
                         })
                     })
                     .then(r => r.json())
                     .then(data => {
                         if (!data.success) console.error('Reorder failed', data);
                     })
                     .catch(err => console.error('Reorder error:', err));
             }

             function attachMediaItem(btn, mediaId) {
                 //  console.log('Attach media item:', btn, mediaId);
                 btn.disabled = true;
                 fetch(galleryAttachUrl, {
                         method: 'POST',
                         headers: {
                             'X-CSRF-TOKEN': galleryCsrf,
                             'Accept': 'application/json',
                             'Content-Type': 'application/json'
                         },
                         body: JSON.stringify({
                             live_show_id: liveShowId,
                             gallery_media_id: parseInt(mediaId, 10)
                         })
                     }).then(r => r.json()).then(data => {
                         if (data.success) {

                             const selectMediaModal = document.getElementById('select-media-modal');
                             if (selectMediaModal) {
                                 const modalInstance = bootstrap.Modal.getInstance(selectMediaModal) || new bootstrap.Modal(
                                     selectMediaModal);
                                 modalInstance.hide();
                             }
                             // //refresh the attached media list
                             fetchGalleryMediaItems();
                         } else {
                             console.error('Attach media item error:', data);
                             streamSwalError(data.message || 'Could not attach this media item.', 'Attach failed');
                         }
                     })
                     .catch(err => console.error('Attach media item error:', err))
                     .finally(() => {
                         btn.disabled = false;
                     });
             }



             function togglePreviewMute() {
                 // Assuming the iframe for preview has an id="live-show-preview-iframe"
                 var iframe = document.getElementById('live-show-preview-iframe');
                 if (iframe && iframe.contentWindow) {
                     // Try to access the video/audio element in the iframe via postMessage or direct access
                     // This requires the iframe to be in the same origin
                     try {
                         // Direct access (same origin)
                         var video = iframe.contentWindow.document.querySelector('video, audio');
                         if (video) {
                             video.muted = !video.muted;
                             document.getElementById('mutePreviewIcon').className = video.muted ? 'fas fa-volume-mute' :
                                 'fas fa-volume-up';
                         } else {
                             // No video/audio found
                             streamSwalError('No audio/video found in the preview.', 'Mute Preview');
                         }
                     } catch (e) {
                         // If cross-origin or error
                         streamSwalError('Unable to mute the preview due to browser restrictions or cross-origin.',
                             'Mute Preview');
                     }
                 } else {
                     streamSwalError('Preview iframe not found.', 'Mute Preview');
                 }
             }
         </script>
         <script>
             document.addEventListener('DOMContentLoaded', function() {
                 const btn = document.getElementById('copyLiveShowLinkBtn');
                 if (btn) {
                     btn.addEventListener('click', function() {
                         const link = "{{ url('live-show-play/' . $liveShow->id) }}";
                         navigator.clipboard.writeText(link).then(function() {
                             btn.setAttribute('data-bs-original-title', 'Copied!');
                             var tooltip = bootstrap.Tooltip.getOrCreateInstance(btn);
                             tooltip.show();
                             setTimeout(() => {
                                 btn.setAttribute('data-bs-original-title',
                                     'Copy link to clipboard');
                                 tooltip.hide();
                             }, 1200);
                         });
                     });
                 }
             });
         </script>
         <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js"></script>
         <style>
             .sortable-ghost {
                 opacity: 0.4;
                 background: rgba(90, 16, 172, 0.2) !important;
             }

             .sortable-chosen {
                 background: rgba(90, 16, 172, 0.1) !important;
             }

             .drag-handle:active {
                 cursor: grabbing !important;
             }
         </style>
     @endpush

 </x-app-dashboard-layout>
