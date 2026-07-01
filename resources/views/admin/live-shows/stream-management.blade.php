 <x-app-dashboard-layout>
     @php

         $broadcasterUrl = route('admin.live-shows.stream-management.broadcaster', [$liveShow->id]);
     @endphp

     <div class="container-fluid min-vh-100">
         <div class="p-3 py-3 mb-1 rounded d-flex justify-content-between align-items-center bg-dark">
             <div class="gap-2 d-flex align-items-center">

                 <h4 class="mb-0 fw-bold ">{{ $liveShow->title }} {!! $liveShow->is_test_show
                     ? '<span class="badge bg-danger">Test Show</span>'
                     : '<span class="badge bg-success">Live Show</span>' !!}</h4>
             </div>
             <div class="shadow-sm btn-group">
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
             <div class="col-12">
                 <button type="button" class="btn btn-outline-light btn-sm" id="toggleLeftSidebarBtn"
                     title="Show/Hide sidebar" data-bs-toggle="tooltip" data-bs-placement="bottom">
                     <i class="fa fa-angle-double-right"></i>
                 </button>
             </div>
             <div class="col-lg-2" id="left-sidebar">
                 <label class="mb-2 small text-muted d-block">Join via QR Code</label>
                 <div id="qrcode" class="p-2 rounded "></div>
                 <div class="mt-1 mb-3 d-flex">
                     <a href="{{ url('live-show-play/' . $liveShow->id) }}" target="_blank"
                         class="text-decoration-none small text-truncate d-block ">
                         {{ url('live-show-play/' . $liveShow->id) }}
                     </a>
                     <button type="button" class="btn btn-sm btn-link" id="copyLiveShowLinkBtn"
                         data-bs-toggle="tooltip" data-bs-placement="top" title="Copy link to clipboard">
                         <i class="fas fa-copy"></i>
                     </button>
                 </div>
                 <div class="border-0 shadow-sm card h-100">
                     <div class="py-3 card-header border-bottom">
                         <h6 class="mb-0 fw-bold text-uppercase small text-muted">
                             <i class="fas fa-users me-2 text-primary"></i>
                             Active Players
                             <span id="total-users-count">
                                 ({{ $liveShow->users()->count() }})
                             </span>
                         </h6>
                     </div>
                     <div class="p-0 card-body" style="overflow: scroll;  max-height: 80vh;">
                         <div class="flex-wrap gap-2 p-2 mb-2 d-flex justify-content-between align-items-center">
                             <div class="input-group input-group-sm" style="max-width: 100%;">
                                 <span class="input-group-text">
                                     <i class="fas fa-search"></i>
                                 </span>
                                 <input type="text" class="form-control" id="playerSearchInput"
                                     placeholder="Search players by name, username, or email">
                             </div>
                             <div class="gap-2 d-flex ms-auto">
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
                         <div class="px-2 pb-2 d-flex justify-content-between align-items-center small text-muted">
                             <span id="playersSearchSummary">Showing players</span>

                         </div>
                         <table class="table mb-0 align-middle table-sm table-dark table-hover"
                             style=" overflow-y: scroll; max-height: 80vh; padding-bottom: 30px;">
                             <thead>
                                 <tr>
                                     <th>Player</th>
                                     <th>Score</th>
                                     <th>Actions</th>
                                 </tr>
                             </thead>
                             <tbody id="activePlayersList">
                                 <tr class="align-middle bg-dark">
                                     <td>
                                         <span class="position-relative me-3">
                                             <div class="bg-secondary rounded-circle"
                                                 style="width: 32px; height: 32px;">
                                             </div>
                                             <span
                                                 class="bottom-0 p-1 border position-absolute end-0 bg-success border-light rounded-circle"></span>
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

             <main class="col-lg-7" id="main-content-stream">
                 <div class="mb-4 border-0 shadow-sm card" id="live-show-status-card">
                     <div class="py-2 card-header border-bottom d-flex justify-content-between align-items-center">
                         <h6 class="mb-0 fw-bold text-uppercase small text-muted">
                             <i class="fas fa-broadcast-tower me-2 text-primary"></i> Show Controls
                         </h6>
                         <button type="button" class="btn btn-outline-light " data-bs-toggle="collapse"
                             data-toggle-status="opened" data-bs-target="#liveShowStatusCardBody"
                             aria-expanded="true" aria-controls="liveShowStatusCardBody" id="liveShowStatusToggle"
                             title="Toggle show controls">
                             <i class="fa fa-angle-up"></i>
                         </button>
                     </div>
                     <div class="collapse show" id="liveShowStatusCardBody">
                         <div class="card-body ">
                             <div class="row align-items-center">
                                 <div class="mb-4 col-lg-12">
                                     <div class="row">
                                         <div class="col-lg-3">

                                             <h6 class="mb-3 text-muted small text-uppercase fw-bold">Live Show Status
                                             </h6>
                                             <form action="" method="post" id="live-show-status-form"
                                                 class="">
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
                                                              class="px-3 btn btn-dark text-nowrap">Update</button> --}}
                                             </form>

                                         </div>
                                         <div class="col-lg ">

                                             <div>
                                                 <h6 class="mb-3 text-muted small text-uppercase fw-bold">
                                                     Winners Ceremony
                                                 </h6>
                                             </div>

                                             <div>
                                                 <div class="d-grid">
                                                     <button type="button" id="announceWinnersBtn"
                                                         class="py-2 text-white shadow-sm btn btn-warning w-100 fw-bold "
                                                         onclick="updateWinners()"
                                                         @if ($liveShow->winners_announced) disabled aria-disabled="true" @endif>
                                                         <span id="announceWinnersBtnContent"
                                                             class="announce-winners-btn-label @if ($liveShow->winners_announced) d-none @endif">
                                                             <i class="fas fa-trophy me-2"></i> Announce Winners
                                                         </span>
                                                         <span id="announceWinnersBtnLoader"
                                                             class="announce-winners-btn-loader d-none">
                                                             <i class="fas fa-spinner fa-spin me-2"
                                                                 aria-hidden="true"></i>
                                                             Announcing…
                                                         </span>
                                                         <span id="announceWinnersBtnDone"
                                                             class="announce-winners-btn-done @if (!$liveShow->winners_announced) d-none @endif">
                                                             <i class="fas fa-check me-2"></i> Winners announced
                                                         </span>
                                                     </button>
                                                     <div class="gap-2 mt-2 d-flex @if (!$liveShow->winners_announced) d-none @endif"
                                                         id="winnersAnnouncedActions">
                                                         <button
                                                             class="py-2 mt-2 text-white shadow-sm btn btn-secondary w-100 fw-bold"
                                                             id="regenerateWinnersBtn" onclick="regenerateWinners()">
                                                             <i class="fas fa-sync-alt me-2"></i>
                                                             ReGenerate Winners
                                                         </button>
                                                         <button
                                                             class="py-2 mt-2 text-white shadow-sm btn btn-info w-100 fw-bold"
                                                             id="resendVoucherWinnersBtn"
                                                             onclick="resendVoucherWinners()">
                                                             <i class="fas fa-envelope me-2"></i>
                                                             Resend Email To Voucher Winners
                                                         </button>
                                                     </div>
                                                 </div>
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
                                                         <i class="fas fa-spinner fa-spin me-2"
                                                             aria-hidden="true"></i>
                                                         Updating…
                                                     </span>
                                                 </button>
                                             </div>

                                         </div>
                                         <div class="col-lg ">
                                             <div>
                                                 <h6 class="mb-3 text-muted small text-uppercase fw-bold">
                                                     Winner Tab Management
                                                 </h6>
                                             </div>
                                            <div>
                                                <button type="button" id="showWinnerTabBtn"
                                                    class="mb-2 text-white shadow-sm btn btn-primary fw-bold"
                                                    onclick="showWinnerTab(this)"
                                                    @if (!$liveShow->winners_announced) disabled aria-disabled="true" @endif>
                                                    <i class="fas fa-eye me-2"></i> Show
                                                </button>
                                                <button type="button" id="hideWinnerTabBtn"
                                                    class="mb-2 text-white shadow-sm btn btn-danger fw-bold"
                                                    onclick="hideWinnerTab(this)"
                                                    @if (!$liveShow->winners_announced) disabled aria-disabled="true" @endif>
                                                    <i class="fas fa-eye-slash me-2"></i> Hide
                                                </button>
                                            </div>
                                         </div>
                                     </div>
                                 </div>

                                {{-- Push notification trigger: alert every player of this show on their devices --}}
                                <div class="col-12 pt-3 mt-2 border-top">
                                    <h6 class="mb-3 text-muted small text-uppercase fw-bold">
                                        <i class="fas fa-bell me-2 text-warning"></i> Push Notification To Players
                                    </h6>
                                    <div class="row g-2 align-items-end">
                                        <div class="col-lg-4">
                                            <label class="mb-1 form-label small text-muted" for="pushNotifyTitle">Title</label>
                                            <input type="text" id="pushNotifyTitle" class="form-control form-control-sm"
                                                value="Badabing Live-Show" maxlength="255">
                                        </div>
                                        <div class="col-lg-5">
                                            <label class="mb-1 form-label small text-muted" for="pushNotifyMessage">Message
                                                (German)</label>
                                            <input type="text" id="pushNotifyMessage" class="form-control form-control-sm"
                                                value="Die Live-Show läuft jetzt – steig ein und sichere dir deine Gewinnchance!"
                                                maxlength="500">
                                        </div>
                                        <div class="col-lg-3 d-grid">
                                            <button type="button" id="notifyPlayersBtn"
                                                class="py-2 text-white shadow-sm btn btn-warning fw-bold"
                                                onclick="notifyPlayers()">
                                                <span class="notify-players-label">
                                                    <i class="fas fa-paper-plane me-2"></i> Send Push
                                                </span>
                                                <span class="notify-players-loader d-none">
                                                    <i class="fas fa-spinner fa-spin me-2" aria-hidden="true"></i>
                                                    Sending…
                                                </span>
                                            </button>
                                        </div>
                                    </div>
                                    <p class="mt-2 mb-0 small text-muted">
                                        Only players who enabled browser notifications will receive this alert.
                                    </p>
                                </div>

                             </div>
                         </div>
                     </div>
                 </div>

                 <button type="button" id="quizQuestionsFullscreenBackdrop"
                     class="quiz-questions-fullscreen-backdrop" aria-hidden="true" tabindex="-1"
                     title="Close expanded view"></button>

                 <div class="mb-4 border-0 shadow-sm card " id="quiz-questions-container-card">
                     <div class="p-0 card-header">
                         <button type="button" id="quizQuestionsFullscreenToggleBtn"
                             class="py-2 mx-1 shadow-sm btn btn-outline-secondary fw-bold float-end" title="Maximize"
                             onclick="toggleQuizQuestionsFullscreen(event)" aria-expanded="false">
                             <i class="fas fa-expand" aria-hidden="true"></i>
                             <span class="visually-hidden">Toggle expanded quiz panel</span>
                         </button>


                     </div>
                     <div class="card-body position-relative">
                         <div class="row">
                             <div class="col-lg-8 ">
                                 <div class="p-3 rounded bg-dark">
                                     <div>
                                         <h5 class="mb-0 mb-3 text-center fw-bold">Quiz Questions</h5>
                                     </div>
                                     <div class="position-relative question-slider-wrap">
                                         <div id="quizQuestionsCarousel" class="carousel slide">
                                             @if ($liveShow->quizzes->count() > 1)
                                                 <div class="carousel-indicators">
                                                     @foreach ($liveShow->quizzes as $index => $quiz)
                                                         <button type="button"
                                                             data-bs-target="#quizQuestionsCarousel"
                                                             data-bs-slide-to="{{ $index }}"
                                                             @if ($index === 0) class="active" aria-current="true" @endif
                                                             aria-label="Question {{ $index + 1 }}"></button>
                                                     @endforeach
                                                 </div>
                                             @endif
                                             <div class="carousel-inner">
                                            @foreach ($liveShow->quizzes as $index => $quiz)
                                                {{-- QuizMediaMerge : tag each quiz slide so the JS merge module can identify + reorder it --}}
                                                <div class="carousel-item px-2 @if ($index === 0) active @endif"
                                                    data-qmm-type="quiz" data-qmm-id="{{ $quiz->id }}"
                                                    data-qmm-question="{{ \Illuminate\Support\Str::limit($quiz->question, 60) }}"
                                                    data-qmm-index="{{ $index + 1 }}">
                                                    <div class="mb-5 border card">
                                                        <div class="position-relative card-body"
                                                            style="height: auto; overflow-y:hidden">
                                                            <button type="button"
                                                                class="btn btn-sm btn-outline-danger position-absolute top-0 end-0 m-2 reset-shown-status-btn @if (!$quiz->has_shown) d-none @endif"
                                                                onclick="resetShownStatus({{ $quiz->id }}, this)"
                                                                id="resetShownStatusBtn-{{ $quiz->id }}"
                                                                title="Reset shown status">
                                                                <i class="fas fa-undo me-1"></i> Reset shown status
                                                            </button>
                                                            <div class="mb-4 text-center fw-bold">
                                                                <div class="mb-2">Question {{ $index + 1 }} /
                                                                    {{ $liveShow->quizzes->count() }}</div>
                                                                <div class="question-text">{{ $quiz->question }}
                                                                </div>
                                                            </div>

                                                             @if ($quiz->options)
                                                                 <div class="mb-4 row g-3">
                                                                     @foreach ($quiz->options as $option)
                                                                         <div class="col-md-12">
                                                                             <div
                                                                                 class="p-3 border rounded @if ($option->is_correct) border-success @endif">
                                                                                 <div
                                                                                     class="mb-2 d-flex justify-content-between">
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
                                                                     data-has-shown="{{ $quiz->has_shown ? 1 : 0 }}"
                                                                     class="row g-2 align-items-center justify-content-center">
                                                                     @csrf
                                                                     <div class="col-auto">
                                                                         <div class="input-group">
                                                                             <span class="bg-white input-group-text"><i
                                                                                     class="fas fa-stopwatch text-muted"></i></span>
                                                                             <input type="number" min="1"
                                                                                 name="seconds"
                                                                                 id="timer-{{ $quiz->id }}"
                                                                                 value="10"
                                                                                 class="text-center form-control fw-bold"
                                                                                 style="width: 80px;" required>
                                                                         </div>
                                                                     </div>
                                                                     @if ($loop->last)
                                                                         <input type="hidden" name="is_last"
                                                                             value="1">
                                                                     @endif
                                                                     <div class="col-auto">
                                                                         <div class="shadow-sm btn-group">
                                                                             <button
                                                                                 type="{{ $quiz->has_shown ? 'button' : 'submit' }}"
                                                                                 class="px-3 btn btn-success"
                                                                                 data-quiz-start
                                                                                 @if ($quiz->has_shown) disabled
                                                                                 aria-disabled="true" @endif>
                                                                                 @if ($quiz->has_shown)
                                                                                     Question shown
                                                                                 @else
                                                                                     <i class="fas fa-play me-2"></i>
                                                                                     Show
                                                                                 @endif
                                                                             </button>
                                                                             <button type="button"
                                                                                 onclick="viewResponses({{ $liveShow->id }}, {{ $quiz->id }}, this)"
                                                                                 class="px-3 text-white btn btn-info">
                                                                                 <i class="fas fa-chart-bar me-2"></i>
                                                                                 Show Responses
                                                                             </button>
                                                                             <button class="px-3 btn btn-danger"
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
                                             @if ($liveShow->quizzes->count() > 1)
                                                 <button class="carousel-control-prev" type="button"
                                                     data-bs-target="#quizQuestionsCarousel" data-bs-slide="prev">
                                                     <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                                     <span class="visually-hidden">Previous</span>
                                                 </button>
                                                 <button class="carousel-control-next" type="button"
                                                     data-bs-target="#quizQuestionsCarousel" data-bs-slide="next">
                                                     <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                                     <span class="visually-hidden">Next</span>
                                                 </button>
                                             @endif
                                         </div>
                                         <div id="questionSliderTimerOverlay" class="question-slider-timer-overlay"
                                             style="display: none;" role="status" aria-live="polite"
                                             aria-hidden="true">
                                             <span id="questionSliderTimerText"
                                                 class="question-slider-timer-text">0</span>
                                         </div>
                                     </div>
                                 </div>
                             </div>
                            <div class="col-lg-4">
                                {{-- QuizMediaMerge : unified sort manager controlling the merged Quiz + Media carousel (front-end only) --}}
                                <h5 class="mb-0 mb-3 text-center fw-bold">Carousel Order</h5>
                                <div class="p-3 mb-3 border rounded border-light bg-dark">
                                    <div class="mb-2 d-flex justify-content-between align-items-center">
                                        <span class="text-muted small text-uppercase fw-bold">Drag to reorder slides</span>
                                        <button type="button" class="btn btn-sm btn-outline-warning"
                                            id="qmmResetOrderBtn" title="Reset to default arrangement (media every 3 questions)">
                                            <i class="fas fa-undo me-1"></i> Default
                                        </button>
                                    </div>
                                    <div id="quizMediaMergeList" class="qmm-list"
                                        style="max-height: 340px; overflow-y:auto;"></div>
                                    <div id="quizMediaMergeEmpty" class="py-3 text-center small text-muted d-none">
                                        No quiz questions or media yet.
                                    </div>
                                </div>

                                <h5 class="mb-0 mb-3 text-center fw-bold">Gallery Media</h5>
                                 <div class="p-3 border rounded border-light bg-dark">
                                     <div class="w-100">
                                         <div class="mb-2">
                                             <h6 class="text-muted small text-uppercase fw-bold mb-">
                                                 Attached to this stream</h6>

                                             <button type="button" class="mt-2 btn btn-sm btn-outline-primary"
                                                 title="Attach media from gallery" data-bs-toggle="modal"
                                                 data-bs-target="#select-media-modal">
                                                 <i class="fas fa-plus"></i>
                                             </button>
                                             <button type="button"
                                                 class="mt-2 btn btn-sm btn-outline-secondary gallery-hide-on-stream-btn"
                                                 id="hideGalleryOnStreamBtn"
                                                 title="Hide image/video overlay on live stream ">
                                                 <i class="fas fa-eye-slash"></i>
                                             </button>
                                             <button type="button" class="mt-2 btn btn-sm btn-outline-success"
                                                 title="Refresh gallery items" onclick="fetchGalleryMediaItems()">
                                                 <i class="fas fa-sync-alt"></i>
                                             </button>


                                         </div>
                                         <div id="gallery-attached-list" class="mb-3 table-responsive"
                                             style="max-height: 520px; overflow-y: auto;">
                                             <table class="table mb-0 
                                             
                                             table-sm table-dark table-hover">

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
                 <div class="border-0 shadow-sm card h-100">
                     <div class="p-0 card-header">
                         <ul class="border-0 nav nav-tabs nav-fill" id="rightPanelTabs">
                             <li class="nav-item position-relative d-flex align-items-center">
                                 <a href="#chat-tab"
                                     class="py-3 text-center border-0 nav-link active border-bottom fw-bold d-flex"
                                     data-bs-toggle="tab">Live Chat

                                 </a>

                             </li>
                             <li class="nav-item">
                                 <a href="#live-show-details" class="py-3 border-0 nav-link border-bottom fw-bold"
                                     data-bs-toggle="tab">Live Show Details</a>
                             </li>
                             <li class="nav-item">
                                 <a href="#live-show-preview" class="py-3 border-0 nav-link border-bottom fw-bold"
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
                     <div class="p-0 card-body" style="overflow: scroll;  min-height: 80vh; height: auto">
                         <div class="tab-content">
                             <div class="tab-pane fade show active" id="chat-tab">
                                 <div class="p-3">
                                     <button class="mb-1 btn btn-primary" id="resetChatBtn" title="Reset Chat"
                                         data-bs-toggle="tooltip" data-bs-placement="top">
                                         <i class="fas fa-eraser"></i>
                                     </button>


                                     <a href="{{ route('admin.live-shows.export-all-chats-as-csv', $liveShow->id) }}"
                                         class="mb-1 btn btn-primary ms-1" id="exportChatsBtn" title="Export Chats"
                                         data-bs-toggle="tooltip" data-bs-placement="top">
                                         <i class="fas fa-file-export"></i>
                                     </a>

                                     <button class="mb-1 btn btn-warning ms-1" id="toggleChatStatusBtn"
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
                                                         class="px-3 text-decoration-none small text-truncate d-block">
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
                                                             class="mb-3 d-flex justify-content-start align-items-center">
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
                                 <div class="p-2 d-flex justify-content-center align-items-center ">
                                     <iframe src="{{ url('live-show-play/' . $liveShow->id) }}?preview=true"
                                         id="live-show-preview-iframe" class="mt-2 live-show-preview-iframe"
                                         style="height: 874px; width: 402px; pointer-events: none; border-radius: 30px; border: 1px solid #ccc;overflow: hidden;"
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
                 <div class="p-0 modal-body d-flex justify-content-center align-items-center"
                     style="min-height:400px;">
                     <img src="" id="media-preview-modal-img" class="rounded shadow img-fluid"
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
                         <div class="py-4 text-center col-12 text-muted">
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

             #quizQuestionsCarousel .carousel-item {
                 min-height: 500px;
             }

             #quizQuestionsCarousel .carousel-control-prev,
             #quizQuestionsCarousel .carousel-control-next {
                 width: auto;
                 opacity: 1;
             }

             #quizQuestionsCarousel .carousel-control-prev-icon,
             #quizQuestionsCarousel .carousel-control-next-icon {
                 width: 2.75rem;
                 height: 2.75rem;
                 border-radius: 50%;
                 background-color: rgba(13, 110, 253, 0.9);
                 background-size: 55% 55%;
             }

             #quizQuestionsCarousel .carousel-control-prev:hover .carousel-control-prev-icon,
             #quizQuestionsCarousel .carousel-control-next:hover .carousel-control-next-icon {
                 background-color: rgba(13, 110, 253, 1);
             }
         </style>
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

             .question-slider-wrap {
                 position: relative;
             }

             .question-slider-timer-overlay {
                 position: absolute;
                 inset: 0;
                 z-index: 10;
                 display: none;
                 background: rgba(255, 255, 255, 0.14);
                 border-radius: 0.375rem;
                 pointer-events: auto;
                 margin-left: -5%;
                 width: 110%;
                 margin-top: -5%;
                 height: 110%;

             }

             .question-slider-timer-text {
                 position: absolute;
                 top: 50%;
                 left: 50%;
                 transform: translate(-50%, -50%);
                 font-size: 5.75rem;
                 font-weight: 700;
                 line-height: 1;
                 color: #f8f9fa;
                 text-shadow: 0 1px 4px rgba(0, 0, 0, 0.55);
                 letter-spacing: 0.02em;
                 background: rgba(10, 10, 10, 0.34);
                 border-radius: 50%;
                 padding: 0.5em 1em;
                 height: 200px;
                 width: 200px;
                 display: flex;
                 align-items: center;
                 justify-content: center;

             }

             /* Optional: Basic styles for fullscreen effect */
             body.quiz-questions-fullscreen-open {
                 overflow: hidden;
             }

             .quiz-questions-fullscreen-backdrop {
                 display: none;
                 position: fixed;
                 inset: 0;
                 z-index: 1990;
                 margin: 0;
                 padding: 0;
                 border: 0;
                 background: rgba(15, 23, 42, 0.62);
                 cursor: pointer;
                 appearance: none;
             }

             .quiz-questions-fullscreen-backdrop.is-visible {
                 display: block;
             }

             #quiz-questions-container-card.fullscreen {
                 position: fixed;
                 top: 100px;
                 z-index: 2000;
                 width: 80%;
                 max-width: 1200px;
                 left: 50%;
                 transform: translateX(-50%);
                 background: #fff;
                 box-shadow: 0 0 40px rgba(0, 0, 0, 0.3);
                 overflow: auto;
             }

             #quiz-questions-container-card.fullscreen .card-header,
             #quiz-questions-container-card.fullscreen .card-body {
                 border-radius: 0 !important;
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

             function toggleQuizQuestionsFullscreen(evt) {
                 evt && evt.stopPropagation();
                 const card = document.getElementById('quiz-questions-container-card');
                 const backdrop = document.getElementById('quizQuestionsFullscreenBackdrop');
                 const toggleBtn = document.getElementById('quizQuestionsFullscreenToggleBtn');
                 if (!card || !backdrop) {
                     return;
                 }

                 const willExpand = !card.classList.contains('fullscreen');
                 card.classList.toggle('fullscreen', willExpand);
                 backdrop.classList.toggle('is-visible', willExpand);
                 document.body.classList.toggle('quiz-questions-fullscreen-open', willExpand);

                 backdrop.setAttribute('aria-hidden', willExpand ? 'false' : 'true');
                 if (toggleBtn) {
                     toggleBtn.setAttribute('aria-expanded', willExpand ? 'true' : 'false');
                     toggleBtn.title = willExpand ? 'Exit expanded view' : 'Maximize';
                     const icon = toggleBtn.querySelector('i');
                     if (icon) {
                         icon.className = willExpand ? 'fas fa-compress' : 'fas fa-expand';
                     }
                 }

                 const quizCarouselEl = document.getElementById('quizQuestionsCarousel');
                 if (quizCarouselEl && typeof bootstrap !== 'undefined') {
                     setTimeout(function() {
                         window.dispatchEvent(new Event('resize'));
                     }, 0);
                 }
             }

             (function bindQuizFullscreenBackdrop() {
                 const backdrop = document.getElementById('quizQuestionsFullscreenBackdrop');
                 if (backdrop) {
                     backdrop.addEventListener('click', function() {
                         const card = document.getElementById('quiz-questions-container-card');
                         if (card && card.classList.contains('fullscreen')) {
                             toggleQuizQuestionsFullscreen({
                                 stopPropagation: function() {}
                             });
                         }
                     });
                 }
                 document.addEventListener('keydown', function(e) {
                     if (e.key !== 'Escape') {
                         return;
                     }
                     const card = document.getElementById('quiz-questions-container-card');
                     if (card && card.classList.contains('fullscreen')) {
                         toggleQuizQuestionsFullscreen({
                             stopPropagation: function() {}
                         });
                     }
                 });
             })();

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
                     '<tr class="align-middle bg-dark"><td colspan="3"><i class="fas fa-spinner fa-spin me-2"></i> Loading...</td></tr>';
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

                 return `<tr class="align-middle bg-dark">
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
                         '<tr class="align-middle bg-dark"><td colspan="3">No players found.</td></tr>';
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
                 take = playerPageSize,
                 search = ''
             } = {}) {
                 const query = new URLSearchParams({
                     skip,
                     take,
                     search,
                 });


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
                     take: playerListState.loadedCount,
                     search: playerListState.search

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
                     take: playerPageSize,
                     search: playerListState.search,
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

             const questionSliderTimerOverlay = document.getElementById('questionSliderTimerOverlay');
             const questionSliderTimerText = document.getElementById('questionSliderTimerText');

             // Light overlay on the question slider + top-right countdown; hidden when done
             function showQuizTimer(seconds, quizId) {
                 if (!questionSliderTimerOverlay || !questionSliderTimerText) return;

                 let timeLeft = parseInt(seconds, 10);
                 if (Number.isNaN(timeLeft) || timeLeft < 0) {
                     timeLeft = 0;
                 }

                 questionSliderTimerOverlay.style.display = 'block';
                 questionSliderTimerOverlay.setAttribute('aria-hidden', 'false');
                 questionSliderTimerText.textContent = String(timeLeft);

                 if (questionSliderTimerOverlay._quizTimerInterval) {
                     clearInterval(questionSliderTimerOverlay._quizTimerInterval);
                 }

                 questionSliderTimerOverlay._quizTimerInterval = setInterval(function() {
                     timeLeft--;
                     if (timeLeft > 0) {
                         questionSliderTimerText.textContent = String(timeLeft);
                     } else {
                         questionSliderTimerText.textContent = '0';
                         clearInterval(questionSliderTimerOverlay._quizTimerInterval);
                         setTimeout(() => {
                             questionSliderTimerOverlay.style.display = 'none';
                             questionSliderTimerOverlay.setAttribute('aria-hidden', 'true');
                         }, 500);

                         setTimeout(() => {
                             console.log('viewing responses after timer finishes..');
                             refreshVisiblePlayers();
                             viewResponses('{{ $liveShow->id }}', quizId, null);
                         }, 5000);
                     }
                 }, 1000);

             }

             function hideQuizTimer() {
                 if (!questionSliderTimerOverlay || !questionSliderTimerText) return;

                 questionSliderTimerText.textContent = '0';
                 if (questionSliderTimerOverlay._quizTimerInterval) {
                     clearInterval(questionSliderTimerOverlay._quizTimerInterval);
                 }
                 setTimeout(() => {
                     questionSliderTimerOverlay.style.display = 'none';
                     questionSliderTimerOverlay.setAttribute('aria-hidden', 'true');
                 }, 500);
             }

            function markQuizQuestionAsShown(form) {
                const btn = form.querySelector('[data-quiz-start]');
                if (!btn) {
                    return;
                }
                btn.type = 'button';
                btn.disabled = true;
                btn.setAttribute('aria-disabled', 'true');
                btn.innerHTML = 'Question shown';

                const quizId = form.id.replace('quiz-timer-form-', '');
                const resetBtn = document.getElementById(`resetShownStatusBtn-${quizId}`);
                if (resetBtn) {
                    resetBtn.classList.remove('d-none');
                }
            }

            function revertQuizQuestionToShowable(quizId) {
                const form = document.getElementById(`quiz-timer-form-${quizId}`);
                if (form) {
                    form.dataset.hasShown = '0';
                    const btn = form.querySelector('[data-quiz-start]');
                    if (btn) {
                        btn.type = 'submit';
                        btn.disabled = false;
                        btn.removeAttribute('aria-disabled');
                        btn.innerHTML = '<i class="fas fa-play me-2"></i> Show';
                    }
                }
                const resetBtn = document.getElementById(`resetShownStatusBtn-${quizId}`);
                if (resetBtn) {
                    resetBtn.classList.add('d-none');
                }
            }

            function resetShownStatus(quizId, btn) {
                streamSwalConfirm({
                    title: 'Reset shown status?',
                    text: 'This will mark the question as not shown so you can show it again.',
                    confirmButtonText: 'Yes, reset',
                }).then(function(result) {
                    if (!result.isConfirmed) {
                        return;
                    }
                    if (btn) {
                        setBtnBusy(btn, true, 'Resetting\u2026');
                    }
                    fetch(`{{ url('admin/live-shows/stream-management') }}/{{ $liveShow->id }}/quizzes/${quizId}/reset-shown-status`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                            },
                        })
                        .then(async response => {
                            const data = await response.json();
                            if (!response.ok) {
                                const error = new Error(data.message || 'Could not reset shown status');
                                error.status = response.status;
                                throw error;
                            }
                            return data;
                        })
                        .then(data => {
                            hideQuizTimer();
                            revertQuizQuestionToShowable(quizId);
                            streamSwalSuccess(data.message || 'Question shown status has been reset.',
                                'Status reset');
                        })
                        .catch(error => {
                            console.error('Error resetting shown status:', error);
                            streamSwalError(error?.message ||
                                'Could not reset the shown status. Please try again.', 'Reset failed');
                        })
                        .finally(() => {
                            if (btn) {
                                setBtnBusy(btn, false);
                            }
                        });
                });
            }

             function submitQuizTimerForm(event, quizId) {
                 event.preventDefault();
                 const form = document.getElementById(`quiz-timer-form-${quizId}`);
                 const formData = new FormData(form);
                 const seconds = formData.get('seconds');
                 const isLast = formData.get('is_last') ? true : false;
                 const startBtn = form.querySelector('[data-quiz-start]');
                 const hasShown = form.dataset.hasShown === '1';

                 if (hasShown) {
                     streamSwalWarning('This question has already been shown.', 'Already shown');
                     return;
                 }

                 setBtnBusy(startBtn, true, 'Starting\u2026');

                 fetch(`{{ url('admin/live-shows/stream-management') }}/{{ $liveShow->id }}/quizzes/${quizId}/send-quiz-question`, {
                         method: 'POST',
                         headers: {
                             'X-CSRF-TOKEN': '{{ csrf_token() }}',
                             'Accept': 'application/json',
                         },
                         body: formData
                     })
                     .then(async response => {
                         const data = await response.json();
                         if (!response.ok) {
                             const error = new Error(data.message || 'Could not start quiz');
                             error.status = response.status;
                             error.data = data;
                             throw error;
                         }

                         return data;
                     })
                     .then(data => {
                         console.log('Quiz question sent:', data);
                         form.dataset.hasShown = data?.has_shown ? '1' : form.dataset.hasShown;
                         showQuizTimer(seconds, quizId);
                     })
                     .catch(error => {
                         console.error('Error sending quiz question:', error);

                         if (error?.status === 422 || error?.data?.already_shown) {
                             form.dataset.hasShown = '1';
                             streamSwalWarning(error?.data?.message || 'This question has already been shown.',
                                 'Already shown');
                             return;
                         }

                         streamSwalError(error?.message || 'Could not start the quiz. Please try again.',
                             'Start failed');
                     })
                     .finally(() => {
                         setBtnBusy(startBtn, false);
                         if (form.dataset.hasShown === '1') {
                             markQuizQuestionAsShown(form);
                         }
                     });
             }
         </script>

         {{-- Regenerate  Winners & Resend Voucher Emails --}}
         <script>
             function regenerateWinners() {

                 streamSwalConfirm({
                     title: 'Regenerate winners?',
                     text: 'This will clear the currently announced winners and select new winners based on the current player scores. This cannot be undone.',
                     confirmButtonText: 'Yes, regenerate winners',
                 }).then(function(result) {
                     Swal.enableLoading();
                     if (!result.isConfirmed) {
                         return;
                     }
                     //  setAnnounceWinnersLoading(true);
                     fetch(`{{ route('admin.live-shows.reupdate-winners', ['liveShowId' => $liveShow->id]) }}`, {
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
                                 //  applyAnnounceWinnersCompleted();
                                 streamSwalWarning(
                                     result.data.message ||
                                     'Winners have already been announced for this live show.',
                                     'Already announced');
                                 return;
                             }
                             if (!result.ok) {
                                 //  clearAnnounceWinnersLoading();
                                 streamSwalError(
                                     (result.data && result.data.message) ? result.data.message :
                                     'Could not announce winners. Please try again.',
                                     'Update failed');
                                 return;
                             }
                             //  applyAnnounceWinnersCompleted();
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
                             Swal.disableLoading();

                             console.error('Error updating winners:', error);
                             //  clearAnnounceWinnersLoading();
                             streamSwalError('Could not announce winners. Please try again.', 'Update failed');
                         });
                 });
             }


             function resendVoucherWinners() {
                 streamSwalConfirm({
                     title: 'Resend voucher winners?',
                     text: 'This will resend the voucher emails to the currently announced voucher winners. This is useful if you have regenerated winners or if some winners did not receive their voucher email.',
                     confirmButtonText: 'Yes, resend voucher winners',
                 }).then(function(result) {
                     if (!result.isConfirmed) {
                         return;
                     }
                     swal.enableLoading();
                     setAnnounceWinnersLoading(true);
                     fetch(`{{ route('admin.live-shows.resend-voucher-winners', ['liveShowId' => $liveShow->id]) }}`, {
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
                             swal.disableLoading();

                             console.error('Error updating winners:', error);
                             clearAnnounceWinnersLoading();
                             streamSwalError('Could not announce winners. Please try again.', 'Update failed');
                         });
                 });
             }
         </script>
         <script>
             var channel2 = pusher.subscribe('live-show.{{ $liveShow->id }}');

             // System subscription event
             channel2.bind('pusher:subscription_succeeded', function() {
                 console.log('Subscribed message event successfully!');
             });

             var channelChatMessages = pusher.subscribe('live-show-chat-messages.{{ $liveShow->id }}');
             channelChatMessages.bind('pusher:subscription_succeeded', function() {
                 console.log('Chat messages channel subscribed successfully!');
             });

             channelChatMessages.bind('LiveShowChatMessagesEvent', function(data) {
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

             // Keep every open stream-management screen in sync with admin state
             // changes (status / winners / quiz) triggered from any other screen.
             function syncLiveShowStatusUi(status) {
                 if (!status) {
                     return;
                 }
                 liveShowStatus = status;
                 const select = document.getElementById('liveShowStatusSelect');
                 if (select && select.value !== status) {
                     select.value = status;
                 }
             }

             function syncWinnersAnnouncedUi(announced) {
                 if (announced) {
                     if (!liveShowWinnersAnnounced) {
                         applyAnnounceWinnersCompleted();
                     }
                 } else if (liveShowWinnersAnnounced) {
                     applyUnannounceWinnersCompleted();
                 }
             }

             function syncQuizUi(payload) {
                 if (!payload || !payload.quizId) {
                     return;
                 }
                 const form = document.getElementById(`quiz-timer-form-${payload.quizId}`);
                 if (payload.action === 'shown') {
                     if (form) {
                         form.dataset.hasShown = '1';
                         markQuizQuestionAsShown(form);
                     }
                     if (payload.seconds) {
                         showQuizTimer(payload.seconds, payload.quizId);
                     }
                } else if (payload.action === 'hidden') {
                    hideQuizTimer();
                } else if (payload.action === 'reset') {
                    hideQuizTimer();
                    revertQuizQuestionToShowable(payload.quizId);
                }
            }

             var channelAdminState = pusher.subscribe('live-show-admin.{{ $liveShow->id }}');
             channelAdminState.bind('pusher:subscription_succeeded', function() {
                 console.log('Admin state channel subscribed successfully!');
             });
             channelAdminState.bind('LiveShowAdminStateEvent', function(data) {
                 const payload = (data && data.payload) ? data.payload : {};
                 switch (data && data.type) {
                     case 'status':
                         syncLiveShowStatusUi(payload.status);
                         break;
                     case 'winners':
                         syncWinnersAnnouncedUi(!!payload.winners_announced);
                         break;
                     case 'quiz':
                         syncQuizUi(payload);
                         break;
                 }
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
                const announcedActions = document.getElementById('winnersAnnouncedActions');
                if (announcedActions) {
                    announcedActions.classList.remove('d-none');
                }
                const showWinnerTabBtn = document.getElementById('showWinnerTabBtn');
                const hideWinnerTabBtn = document.getElementById('hideWinnerTabBtn');
                if (showWinnerTabBtn) {
                    showWinnerTabBtn.disabled = false;
                    showWinnerTabBtn.removeAttribute('aria-disabled');
                }
                if (hideWinnerTabBtn) {
                    hideWinnerTabBtn.disabled = false;
                    hideWinnerTabBtn.removeAttribute('aria-disabled');
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
                const announcedActions = document.getElementById('winnersAnnouncedActions');
                if (announcedActions) {
                    announcedActions.classList.add('d-none');
                }
                const showWinnerTabBtn = document.getElementById('showWinnerTabBtn');
                const hideWinnerTabBtn = document.getElementById('hideWinnerTabBtn');
                if (showWinnerTabBtn) {
                    showWinnerTabBtn.disabled = true;
                    showWinnerTabBtn.setAttribute('aria-disabled', 'true');
                }
                if (hideWinnerTabBtn) {
                    hideWinnerTabBtn.disabled = true;
                    hideWinnerTabBtn.setAttribute('aria-disabled', 'true');
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

            // Send a web-push notification to every player of this live show.
            function notifyPlayers() {
                var btn = document.getElementById('notifyPlayersBtn');
                var titleInput = document.getElementById('pushNotifyTitle');
                var messageInput = document.getElementById('pushNotifyMessage');

                var title = titleInput ? titleInput.value.trim() : '';
                var message = messageInput ? messageInput.value.trim() : '';

                if (!message) {
                    streamSwalWarning('Please enter a notification message.', 'Message required');
                    return;
                }

                streamSwalConfirm({
                    title: 'Send push notification?',
                    text: 'All players of this show who enabled notifications will receive this alert on their devices.',
                    confirmButtonText: 'Yes, send notification',
                }).then(function(result) {
                    if (!result.isConfirmed) {
                        return;
                    }

                    // Swap the button into its loading state while we queue the push.
                    setNotifyPlayersLoading(btn, true);

                    fetch(`{{ route('admin.live-shows.notify-players', ['liveShowId' => $liveShow->id]) }}`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({
                                title: title,
                                message: message,
                            }),
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
                            setNotifyPlayersLoading(btn, false);
                            if (!result.ok) {
                                streamSwalWarning(
                                    (result.data && result.data.message) ? result.data.message :
                                    'Could not send the push notification.',
                                    'Notification not sent');
                                return;
                            }
                            streamSwalSuccess(
                                (result.data && result.data.message) ? result.data.message :
                                'Push notification has been queued.',
                                'Notification queued');
                        })
                        .catch(function(error) {
                            console.error('Error sending push notification:', error);
                            setNotifyPlayersLoading(btn, false);
                            streamSwalError('Could not send the push notification. Please try again.',
                                'Notification failed');
                        });
                });
            }

            // Toggle the spinner/label on the "Send Push" button.
            function setNotifyPlayersLoading(btn, loading) {
                if (!btn) return;
                var label = btn.querySelector('.notify-players-label');
                var loader = btn.querySelector('.notify-players-loader');
                btn.disabled = loading;
                if (label) label.classList.toggle('d-none', loading);
                if (loader) loader.classList.toggle('d-none', !loading);
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
                 const quizCarouselEl = document.getElementById('quizQuestionsCarousel');
                 if (quizCarouselEl && typeof bootstrap !== 'undefined') {
                     bootstrap.Carousel.getOrCreateInstance(quizCarouselEl, {
                         interval: false,
                         wrap: false,
                         touch: true
                     });
                 }
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
                             console.log('liveShowStatus:', liveShowStatus);
                             //check which radio button is checked, play that media by clicking its show button
                             if (liveShowStatus == 'live') {
                                 console.log('liveShowStatus is live');
                                 const playWithLiveRadio = document.querySelector(
                                     'input[name="play_with_live"]:checked');
                                 console.log('playWithLiveRadio:', playWithLiveRadio);
                                 if (playWithLiveRadio) {
                                     const mediaId = playWithLiveRadio.value;
                                     const showBtn = document.getElementById(`show-media-btn-${mediaId}`);
                                     if (showBtn) {
                                         showBtn.click();
                                     }
                                 }
                             }
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
                 let triggerEvent = 0;
                 if (btn) {
                     setBtnBusy(btn, true, 'Loading\u2026');
                 }
                 if (btn != null) {
                     triggerEvent = 1;
                 } else {
                     triggerEvent = 0;
                 }
                 fetch(`{{ url('admin/live-shows') }}/${liveShowId}/get-users-quiz-responses/${quizId}?triggerEvent=${triggerEvent}`, {
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

             const liveShowMediaHiddenUrl = '{{ route('admin.live-shows.media-hidden', $liveShow->id) }}';



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

                             //  if (data.total_seconds) {
                             //      setTimeout(() => {
                             //          galleryHideOnStream(btn);
                             //      }, data.total_seconds * 1000 + 5000);
                             //  }
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



                 console.log('LiveShowMediaHidden event dispatched successfully!');
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
                            // QuizMediaMerge : resync the merged carousel so the newly attached media appears as a slide
                            fetchGalleryMediaItems();
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
                             '<td colspan="6" class="py-3 text-center small">No media attached. Use &quot;Add from gallery&quot; to attach items.</td>' +
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
                            // QuizMediaMerge : resync the merged carousel so the detached media slide disappears
                            fetchGalleryMediaItems();
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
                            // QuizMediaMerge : feed the freshly loaded media into the merged carousel + sort manager
                            if (typeof QuizMediaMerge !== 'undefined') {
                                QuizMediaMerge.onMediaLoaded(data.media || []);
                            }
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
                        <div class="gap-2 px-2 py-3 d-flex">

                                  <div class="mb-2 drag-handle" style="cursor: grab;">
                                <i class="fas fa-grip-vertical text-muted"></i>
                                </div>

                          <div class="row justify-content-between">
                            <div class="position-relative col-6">
                                <img src="${data.is_image ? data.path : (data.thumbnail ?? data.path)}"
                                    alt=""
                                    title="${data.title}"
                                    style="width: 100%; height: 120px; object-fit: cover; border-radius: 6px; border: 1px solid #555;">
                                <button type="button"
                                    class="btn btn-sm btn-danger gallery-detach-btn"
                                    style="opacity: 0.8; transition: opacity 0.3s ease;"
                                    data-media-id="${data.id}"
                                    title="Remove from stream"
                                    id="detach-media-btn-${data.id}"
                                    onclick="galleryDetach('${data.id}', this)">
                                    <i class="fas fa-times"></i>
                                </button>


                            </div>

                            <div class=" col-6">
                                <div class="mb-1 w-100">
                                <button type="button"
                                    class="mb-1 btn btn-sm btn-success gallery-show-on-stream-btn d-block w-100 "
                                    onclick="galleryShowOnStream('${data.id}', this)"
                                    data-media-id="${data.id}"
                                    id="show-media-btn-${data.id}"
                                    title="Show on live stream">
                                    <i class="fas fa-tv"></i> Show

                                </button>
                                <button type="button"
                                    class="mb-1 btn btn-sm btn-warning gallery-hide-on-stream-btn d-block w-100 "
                                    onclick="galleryHideOnStream(this)"
                                    data-media-id="${data.id}"
                                    id="hide-media-btn-${data.id}"
                                    title="Hide on live stream">
                                    <i class="fas fa-eye-slash"></i> Hide

                                </button>

                                </div>
                                <button type="button"
                                    id="preview-media-btn-${data.id}"
                                    class="mb-1 btn btn-sm btn-secondary d-block w-100 " title="Preview"
                                    onclick="openMediaPreviewModal('${data.is_image ? data.path : (data.thumbnail ?? data.path)}')">
                                    <i class="fas fa-eye"></i> Preview
                                </button>


                            </div>
                            <div class="col-12">
                                <div class="mb-1 fw-semibold text-truncate" style="width:100%;" title="${data.title}">
                                    <span class="badge ${data.type === 'video' ? 'bg-primary' : 'bg-warning text-dark'}  top-0 end-0">
                                    ${data.type ?? ''}
                                </span>
                                        ${data.title.length > 15 ? data.title.substring(0, 15) + '...' : data.title || '—'}
                                    </div>
                                    <div class="form-check align-items-center d-flex">
                                    <input
                                        class="form-check-input"
                                        type="radio"
                                        name="play_with_live"
                                        id="playWithLiveRadio_${data.id}"
                                        value="${data.id}"
                                        onchange="askConfirmationWhenSelectThisMediaForLive(this)"
                                        ${data.play_with_live ? 'checked' : ''} >
                                    <label class="form-check-label ms-2 small" for="playWithLiveRadio_${data.id}">
                                        Play on live start
                                    </label>
                                </div>
                            </div>
                            </div>


                        </div>
                    </td>
                </tr>

            `;
             }

             function askConfirmationWhenSelectThisMediaForLive(radio) {
                 console.log('radio:', radio);
                 streamSwalConfirm({
                     title: 'Are you sure you want to play this media on live start?',
                     text: 'This media will be played on live start. You can change it later.',
                     confirmButtonText: 'Yes, play',
                     confirmButtonColor: '#3085d6',
                 }).then(function(result) {
                     if (result.isConfirmed) {
                         radio.checked = true;
                     } else {
                         radio.checked = false;
                     }
                 });
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



             /*
             * QuizMediaMerge : Front-end module that merges the server-rendered Quiz Question
             * slides and the Gallery Media items into a SINGLE Bootstrap carousel, and exposes a
             * drag-to-sort manager in the sidebar. Nothing is persisted to the backend — the chosen
             * order lives in localStorage (per live show) so it survives reloads. The DEFAULT
             * arrangement interleaves one media slide before every group of 3 questions
             * (e.g. M1, Q1, Q2, Q3, M2, Q4, Q5, Q6, ... then any leftover media at the end).
             */
            const QuizMediaMerge = (function() {
                // QuizMediaMerge : how many questions to place after each media slide in the default order
                const GROUP_SIZE = 3;
                // QuizMediaMerge : localStorage key is scoped per live show so different shows keep their own order
                const STORAGE_KEY = 'qmm-order-' + (typeof liveShowId !== 'undefined' ? liveShowId : 'default');

                // QuizMediaMerge : runtime state
                let mediaMap = {}; // id -> media payload from the gallery endpoint
                let mediaNodes = {}; // id -> built <div.carousel-item> node (cached so drag just moves nodes)
                let quizNodesMap = {}; // id -> existing server-rendered <div.carousel-item> node
                let order = []; // [{ type:'quiz'|'media', id:'..' }] the single source of truth for slide order

                // QuizMediaMerge : DOM helpers
                function carouselEl() {
                    return document.getElementById('quizQuestionsCarousel');
                }

                function innerEl() {
                    return document.querySelector('#quizQuestionsCarousel .carousel-inner');
                }

                function indicatorsEl() {
                    return document.querySelector('#quizQuestionsCarousel .carousel-indicators');
                }

                function listEl() {
                    return document.getElementById('quizMediaMergeList');
                }

                function escapeHtml(str) {
                    return String(str == null ? '' : str)
                        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
                        .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
                }

                // QuizMediaMerge : read the quiz slides Blade already rendered (tagged with data-qmm-* attrs)
                function collectQuizItems() {
                    const nodes = document.querySelectorAll(
                        '#quizQuestionsCarousel .carousel-item[data-qmm-type="quiz"]');
                    const list = [];
                    quizNodesMap = {};
                    nodes.forEach(function(n) {
                        const id = String(n.dataset.qmmId);
                        quizNodesMap[id] = n;
                        list.push({
                            id: id,
                            index: n.dataset.qmmIndex || '',
                            question: n.dataset.qmmQuestion || ''
                        });
                    });
                    return list;
                }

                // QuizMediaMerge : build the DEFAULT interleaved order (media, then GROUP_SIZE questions, repeat)
                function buildDefaultOrder(quizIds, mediaIds) {
                    const out = [];
                    let qi = 0;
                    let mi = 0;
                    while (qi < quizIds.length) {
                        if (mi < mediaIds.length) {
                            out.push({ type: 'media', id: mediaIds[mi++] });
                        }
                        for (let k = 0; k < GROUP_SIZE && qi < quizIds.length; k++) {
                            out.push({ type: 'quiz', id: quizIds[qi++] });
                        }
                    }
                    // QuizMediaMerge : drop any media that did not fit into a group at the end
                    while (mi < mediaIds.length) {
                        out.push({ type: 'media', id: mediaIds[mi++] });
                    }
                    return out;
                }

                function loadSavedOrder() {
                    try {
                        const raw = localStorage.getItem(STORAGE_KEY);
                        if (!raw) return null;
                        const parsed = JSON.parse(raw);
                        return Array.isArray(parsed) ? parsed : null;
                    } catch (e) {
                        return null;
                    }
                }

                function saveOrder() {
                    try {
                        localStorage.setItem(STORAGE_KEY, JSON.stringify(order.map(function(i) {
                            return { type: i.type, id: String(i.id) };
                        })));
                    } catch (e) {
                        /* QuizMediaMerge : ignore storage failures (private mode / quota) */
                    }
                }

                // QuizMediaMerge : merge the saved order with what actually exists right now.
                // Keeps saved positions for still-present items, appends brand-new items using the
                // default arrangement, and silently drops items that no longer exist.
                function reconcile(quizIds, mediaIds) {
                    const def = buildDefaultOrder(quizIds, mediaIds);
                    const available = {};
                    def.forEach(function(i) {
                        available[i.type + ':' + i.id] = true;
                    });
                    const saved = loadSavedOrder();
                    const result = [];
                    const seen = {};
                    if (saved) {
                        saved.forEach(function(i) {
                            const key = i.type + ':' + String(i.id);
                            if (available[key] && !seen[key]) {
                                result.push({ type: i.type, id: String(i.id) });
                                seen[key] = true;
                            }
                        });
                    }
                    def.forEach(function(i) {
                        const key = i.type + ':' + i.id;
                        if (!seen[key]) {
                            result.push(i);
                            seen[key] = true;
                        }
                    });
                    return result;
                }

                // QuizMediaMerge : build a carousel slide for a media item (mirrors the quiz card shell)
                function buildMediaNode(m) {
                    const src = m.is_image ? m.path : (m.thumbnail || m.path);
                    const typeBadge = m.type === 'video' ? 'bg-primary' : 'bg-warning text-dark';
                    const div = document.createElement('div');
                    div.className = 'carousel-item px-2 qmm-media-slide';
                    div.setAttribute('data-qmm-type', 'media');
                    div.setAttribute('data-qmm-id', String(m.id));
                    div.innerHTML =
                        '<div class="mb-5 border card">' +
                        '  <div class="position-relative card-body text-center" style="height:auto; overflow-y:hidden">' +
                        '    <div class="mb-3 fw-bold">' +
                        '      <span class="badge ' + typeBadge + '">' + escapeHtml(m.type || 'media') + '</span>' +
                        '      <div class="mt-2 question-text" style="text-align:center;">' + escapeHtml(m.title || 'Untitled media') + '</div>' +
                        '    </div>' +
                        '    <div class="qmm-media-frame">' +
                        '      <img src="' + escapeHtml(src) + '" alt="' + escapeHtml(m.title || '') + '">' +
                        '    </div>' +
                        '    <div class="mt-4 d-flex justify-content-center gap-2 flex-wrap">' +
                        '      <button type="button" class="btn btn-success" onclick="galleryShowOnStream(\'' + m.id + '\', this)"><i class="fas fa-tv me-2"></i>Show on stream</button>' +
                        '      <button type="button" class="btn btn-warning" onclick="galleryHideOnStream(this)"><i class="fas fa-eye-slash me-2"></i>Hide</button>' +
                        '      <button type="button" class="btn btn-secondary" onclick="openMediaPreviewModal(\'' + escapeHtml(src) + '\')"><i class="fas fa-eye me-2"></i>Preview</button>' +
                        '    </div>' +
                        '  </div>' +
                        '</div>';
                    return div;
                }

                function nodeFor(item) {
                    if (item.type === 'quiz') {
                        return quizNodesMap[item.id] || null;
                    }
                    if (!mediaNodes[item.id] && mediaMap[item.id]) {
                        mediaNodes[item.id] = buildMediaNode(mediaMap[item.id]);
                    }
                    return mediaNodes[item.id] || null;
                }

                // QuizMediaMerge : physically re-order the carousel DOM to match `order`
                function reorderCarousel() {
                    const inner = innerEl();
                    const el = carouselEl();
                    if (!inner || !el) return;

                    // QuizMediaMerge : tear down the live carousel instance before shuffling its children
                    if (typeof bootstrap !== 'undefined') {
                        const inst = bootstrap.Carousel.getInstance(el);
                        if (inst) inst.dispose();
                    }

                    // QuizMediaMerge : remove media slides that are no longer part of the order (detached media)
                    const orderedKeys = {};
                    order.forEach(function(i) {
                        orderedKeys[i.type + ':' + i.id] = true;
                    });
                    inner.querySelectorAll('.carousel-item[data-qmm-type="media"]').forEach(function(n) {
                        if (!orderedKeys['media:' + n.dataset.qmmId]) n.remove();
                    });

                    // QuizMediaMerge : append each node in the desired order (appendChild moves existing nodes)
                    order.forEach(function(item) {
                        const node = nodeFor(item);
                        if (!node) return;
                        node.classList.add('carousel-item');
                        node.classList.remove('active');
                        inner.appendChild(node);
                    });

                    // QuizMediaMerge : exactly one slide must be active
                    const first = inner.querySelector('.carousel-item');
                    if (first) first.classList.add('active');

                    rebuildIndicators();
                    ensureControls();

                    if (typeof bootstrap !== 'undefined') {
                        bootstrap.Carousel.getOrCreateInstance(el, {
                            interval: false,
                            wrap: false,
                            touch: true
                        });
                    }
                }

                // QuizMediaMerge : rebuild the dot indicators to match the current slide count
                function rebuildIndicators() {
                    const inner = innerEl();
                    const el = carouselEl();
                    if (!inner || !el) return;
                    const items = inner.querySelectorAll('.carousel-item');
                    let ind = indicatorsEl();

                    if (items.length <= 1) {
                        if (ind) ind.innerHTML = '';
                        return;
                    }
                    if (!ind) {
                        ind = document.createElement('div');
                        ind.className = 'carousel-indicators';
                        el.insertBefore(ind, el.firstChild);
                    }
                    let html = '';
                    items.forEach(function(_, i) {
                        html += '<button type="button" data-bs-target="#quizQuestionsCarousel" data-bs-slide-to="' +
                            i + '"' + (i === 0 ? ' class="active" aria-current="true"' : '') +
                            ' aria-label="Slide ' + (i + 1) + '"></button>';
                    });
                    ind.innerHTML = html;
                }

                // QuizMediaMerge : make sure prev/next controls exist when there is more than one slide
                function ensureControls() {
                    const inner = innerEl();
                    const el = carouselEl();
                    if (!inner || !el) return;
                    const multiple = inner.querySelectorAll('.carousel-item').length > 1;
                    const hasPrev = el.querySelector('.carousel-control-prev');
                    if (multiple && !hasPrev) {
                        const prev = document.createElement('button');
                        prev.className = 'carousel-control-prev';
                        prev.type = 'button';
                        prev.setAttribute('data-bs-target', '#quizQuestionsCarousel');
                        prev.setAttribute('data-bs-slide', 'prev');
                        prev.innerHTML =
                            '<span class="carousel-control-prev-icon" aria-hidden="true"></span><span class="visually-hidden">Previous</span>';
                        const next = document.createElement('button');
                        next.className = 'carousel-control-next';
                        next.type = 'button';
                        next.setAttribute('data-bs-target', '#quizQuestionsCarousel');
                        next.setAttribute('data-bs-slide', 'next');
                        next.innerHTML =
                            '<span class="carousel-control-next-icon" aria-hidden="true"></span><span class="visually-hidden">Next</span>';
                        el.appendChild(prev);
                        el.appendChild(next);
                    }
                }

                // QuizMediaMerge : render the sidebar sort manager list from `order`
                function renderManagerList() {
                    const list = listEl();
                    const empty = document.getElementById('quizMediaMergeEmpty');
                    if (!list) return;

                    if (!order.length) {
                        list.innerHTML = '';
                        if (empty) empty.classList.remove('d-none');
                        return;
                    }
                    if (empty) empty.classList.add('d-none');

                    let html = '';
                    order.forEach(function(item) {
                        if (item.type === 'quiz') {
                            const q = quizNodesMap[item.id];
                            const idx = q ? (q.dataset.qmmIndex || '') : '';
                            const question = q ? (q.dataset.qmmQuestion || '') : '';
                            html +=
                                '<div class="qmm-item qmm-type-quiz" data-qmm-type="quiz" data-qmm-id="' + item.id + '">' +
                                '  <span class="qmm-handle"><i class="fas fa-grip-vertical"></i></span>' +
                                '  <span class="qmm-icon"><i class="fas fa-question-circle text-primary"></i></span>' +
                                '  <span class="qmm-label">Q' + escapeHtml(idx) + ' &middot; ' + escapeHtml(question) + '</span>' +
                                '</div>';
                        } else {
                            const m = mediaMap[item.id];
                            const src = m ? (m.is_image ? m.path : (m.thumbnail || m.path)) : '';
                            const title = m ? (m.title || 'Untitled media') : ('Media #' + item.id);
                            html +=
                                '<div class="qmm-item qmm-type-media" data-qmm-type="media" data-qmm-id="' + item.id + '">' +
                                '  <span class="qmm-handle"><i class="fas fa-grip-vertical"></i></span>' +
                                '  <img class="qmm-thumb" src="' + escapeHtml(src) + '" alt="">' +
                                '  <span class="qmm-label"><i class="fas fa-photo-film text-warning me-1"></i>' + escapeHtml(title) + '</span>' +
                                '</div>';
                        }
                    });
                    list.innerHTML = html;
                    initManagerSortable();
                }

                // QuizMediaMerge : make the manager list drag-sortable (separate handle from gallery table)
                function initManagerSortable() {
                    const list = listEl();
                    if (!list || typeof Sortable === 'undefined') return;
                    if (list._qmmSortable) {
                        list._qmmSortable.destroy();
                    }
                    list._qmmSortable = new Sortable(list, {
                        handle: '.qmm-handle',
                        animation: 150,
                        ghostClass: 'qmm-ghost',
                        chosenClass: 'qmm-chosen',
                        onEnd: function() {
                            applyOrderFromManager();
                        }
                    });
                }

                // QuizMediaMerge : read the manager list DOM back into `order`, persist, then re-sort the carousel
                function applyOrderFromManager() {
                    const list = listEl();
                    if (!list) return;
                    const rows = list.querySelectorAll('.qmm-item');
                    order = Array.from(rows).map(function(r) {
                        return { type: r.dataset.qmmType, id: String(r.dataset.qmmId) };
                    });
                    saveOrder();
                    reorderCarousel();
                }

                // QuizMediaMerge : full paint (carousel + manager list)
                function render() {
                    reorderCarousel();
                    renderManagerList();
                }

                // QuizMediaMerge : called every time gallery media is (re)loaded from the server
                function onMediaLoaded(media) {
                    mediaMap = {};
                    (media || []).forEach(function(m) {
                        mediaMap[String(m.id)] = m;
                    });
                    // QuizMediaMerge : forget cached slide nodes for media that no longer exists
                    Object.keys(mediaNodes).forEach(function(id) {
                        if (!mediaMap[id]) delete mediaNodes[id];
                    });

                    const quizItems = collectQuizItems();
                    const quizIds = quizItems.map(function(q) {
                        return q.id;
                    });
                    const mediaIds = (media || []).map(function(m) {
                        return String(m.id);
                    });

                    order = reconcile(quizIds, mediaIds);
                    render();
                }

                // QuizMediaMerge : reset back to the default interleaved arrangement
                function resetToDefault() {
                    try {
                        localStorage.removeItem(STORAGE_KEY);
                    } catch (e) {}
                    const quizItems = collectQuizItems(); // refresh in case DOM changed
                    order = buildDefaultOrder(quizItems.map(function(q) {
                        return q.id;
                    }), Object.keys(mediaMap));
                    saveOrder();
                    render();
                }

                // QuizMediaMerge : wire the "Default" reset button
                (function bindResetButton() {
                    const btn = document.getElementById('qmmResetOrderBtn');
                    if (btn) {
                        btn.addEventListener('click', function() {
                            resetToDefault();
                        });
                    }
                })();

                return {
                    onMediaLoaded: onMediaLoaded,
                    resetToDefault: resetToDefault
                };
            })();

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

                 const liveShowStatusCardBody = document.getElementById('liveShowStatusCardBody');
                 const liveShowStatusToggle = document.getElementById('liveShowStatusToggle');
                 liveShowStatusToggle.addEventListener('click', function() {
                     console.log('liveShowStatusToggle clicked', liveShowStatusToggle.getAttribute(
                         'data-toggle-status'));
                     if (liveShowStatusToggle.getAttribute('data-toggle-status') == 'opened') {
                         liveShowStatusToggle.setAttribute('data-toggle-status', 'closed');
                         liveShowStatusToggle.innerHTML = '<i class="fa fa-angle-down"></i>';
                     } else {
                         liveShowStatusToggle.setAttribute('data-toggle-status', 'opened');
                         liveShowStatusToggle.innerHTML = '<i class="fa fa-angle-up"></i>';
                     }

                 });

                 const toggleLeftSidebarBtn = document.getElementById('toggleLeftSidebarBtn');
                 const leftSidebar = document.getElementById('left-sidebar');
                 const mainContent = document.getElementById('main-content-stream');
                 if (toggleLeftSidebarBtn && leftSidebar) {
                     toggleLeftSidebarBtn.addEventListener('click', function() {
                         const hidden = leftSidebar.classList.toggle('d-none');
                         toggleLeftSidebarBtn.classList.toggle('active', hidden);
                         if (mainContent) {
                             mainContent.classList.toggle('col-lg-7', !hidden);
                             mainContent.classList.toggle('col-lg-9', hidden);
                         }

                         if (hidden) {
                             console.log('hidden');
                             toggleLeftSidebarBtn.innerHTML = '<i class="fa fa-angle-double-left"></i>';
                         } else {
                             toggleLeftSidebarBtn.innerHTML = '<i class="fa fa-angle-double-right"></i>';
                         }
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

             .question-text {
                 font-size: 1.3rem;
                 font-weight: 600;
                 color: #fff;
                 text-align: left;

                 margin-bottom: 20px;
             }

             .gallery-detach-btn {
                 position: absolute;
                 top: 0px;
                 left: 10px;
                 opacity: 0.5;
                 transition: opacity 0.3s ease;
             }

            .gallery-detach-btn:hover {
                opacity: 1;
            }

            /* QuizMediaMerge : styling for the unified sort manager list */
            .qmm-list {
                display: flex;
                flex-direction: column;
                gap: 6px;
            }

            .qmm-item {
                display: flex;
                align-items: center;
                gap: 8px;
                padding: 6px 8px;
                border: 1px solid #444;
                border-radius: 6px;
                background: #2b2b2b;
                color: #fff;
            }

            .qmm-item .qmm-handle {
                cursor: grab;
                color: #9aa0a6;
            }

            .qmm-item .qmm-handle:active {
                cursor: grabbing;
            }

            .qmm-thumb {
                width: 40px;
                height: 40px;
                object-fit: cover;
                border-radius: 4px;
                border: 1px solid #555;
                flex: 0 0 auto;
            }

            .qmm-icon {
                width: 40px;
                height: 40px;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 4px;
                background: #1f1f1f;
                flex: 0 0 auto;
            }

            .qmm-label {
                flex: 1 1 auto;
                font-size: .82rem;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .qmm-type-quiz {
                border-left: 3px solid #0d6efd;
            }

            .qmm-type-media {
                border-left: 3px solid #ffc107;
            }

            .qmm-ghost {
                opacity: .4;
            }

            .qmm-chosen {
                background: rgba(90, 16, 172, 0.2) !important;
            }

            /* QuizMediaMerge : media slide inside the quiz carousel */
            .qmm-media-slide .qmm-media-frame {
                display: flex;
                align-items: center;
                justify-content: center;
                min-height: 300px;
            }

            .qmm-media-slide .qmm-media-frame img {
                max-height: 340px;
                max-width: 100%;
                object-fit: contain;
                border-radius: 8px;
                border: 1px solid #555;
            }
        </style>
    @endpush

 </x-app-dashboard-layout>
