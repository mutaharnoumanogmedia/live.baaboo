-- Active: 1764218239848@@127.0.0.1@3306@live_baaboo
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
                <button class="btn btn-outline-danger" id="resetGameButton">
                    <i class="fas fa-undo me-1"></i> Reset
                </button>
            </div>
        </div>
        <div class="row g-4">
            <div class="col-lg-2">
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
                        <div class="d-flex justify-content-end mb-3 p-1">
                            <button type="button" class="btn btn-primary btn-sm me-2" id="fetchPlayersButton">
                                <i class="fas fa-sync"></i> Refresh Players
                            </button>

                            <a href="{{ route('admin.live-shows.export-all-users-as-csv', $liveShow->id) }}"
                                title="Export Users" class="btn btn-primary btn-sm" id="exportUsersBtn"
                                data-bs-toggle="tooltip" data-bs-placement="top" title="Export Users">
                                <i class="fas fa-file-export"></i>
                            </a>
                        </div>
                        <ul class="list-group list-group-flush" id="activePlayersList"
                            style=" overflow-y: scroll; max-height: 80vh; padding-bottom: 30px;">
                            <li class="list-group-item d-flex align-items-center border-0 px-3">
                                <span class="position-relative me-3">
                                    <div class="bg-secondary rounded-circle" style="width: 32px; height: 32px;"></div>
                                    <span
                                        class="position-absolute bottom-0 end-0 p-1 bg-success border border-light rounded-circle"></span>
                                </span>
                                <div class="small fw-medium">Loading...</div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <main class="col-lg-7">
                <div class="card border-0 shadow-sm mb-4">


                    <div class="card-body ">
                        <div class="row align-items-center">

                            <div class="col-md-6 text-center">
                                <label class="small text-muted d-block mb-2">Join via QR Code</label>
                                <div id="qrcode" class="mx-auto p-2  border rounded"
                                    style="width: 180px; height: 180px;"></div>
                                <div class="mt-4 d-flex justify-content-center align-items-center">
                                    <a href="{{ url('live-show-play/' . $liveShow->id) }}" target="_blank"
                                        class="text-decoration-none small text-truncate d-block px-3">
                                        {{ url('live-show-play/' . $liveShow->id) }}

                                    </a>

                                    <button type="button" class="btn btn-sm btn-link" id="copyLiveShowLinkBtn"
                                        data-bs-toggle="tooltip" data-bs-placement="top" title="Copy link to clipboard">
                                        <i class="fas fa-copy"></i>
                                    </button>
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
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="row ">
                                    <div class="col-md-12 mb-3">
                                        <div class="card border-0 shadow-sm">
                                            <div class="card-body">
                                                <h6 class="text-muted small text-uppercase fw-bold mb-3">Winners
                                                    Ceremony</h6>
                                                <button type="button"
                                                    class="btn btn-warning w-100 py-2 fw-bold text-white shadow-sm"
                                                    onclick="updateWinners()">
                                                    <i class="fas fa-trophy me-2"></i> Announce Winners
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="card border-0 shadow-sm">
                                            <div class="card-body">
                                                <h6 class="text-muted small text-uppercase fw-bold mb-3">Show Status
                                                </h6>
                                                <form action="" method="post" id="live-show-status-form"
                                                    class="d-flex gap-2">
                                                    <select class="form-select fw-bold" id="liveShowStatusSelect">
                                                        <option value="scheduled"
                                                            {{ $liveShow->status == 'scheduled' ? 'selected' : '' }}>⏳
                                                            Scheduled</option>
                                                        <option value="live"
                                                            {{ $liveShow->status == 'live' ? 'selected' : '' }}>🟢
                                                            Live</option>
                                                        <option value="completed"
                                                            {{ $liveShow->status == 'completed' ? 'selected' : '' }}>🔴
                                                            Completed
                                                        </option>
                                                    </select>
                                                    <button type="submit"
                                                        class="btn btn-dark text-nowrap px-3">Update</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-light py-3">
                        <ul class="nav nav-tabs card-header-tabs" id="quizGalleryTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="quiz-tab" data-bs-toggle="tab"
                                    data-bs-target="#quizTabPane" type="button" role="tab"
                                    aria-controls="quizTabPane" aria-selected="true">
                                    Quiz Questions
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="gallery-tab" data-bs-toggle="tab"
                                    data-bs-target="#galleryTabPane" type="button" role="tab"
                                    aria-controls="galleryTabPane" aria-selected="false">
                                    Gallery Media


                                    <span id="gallery-show-status" class="ms-3">

                                    </span>
                                </button>


                            </li>
                        </ul>
                    </div>
                    <div class="card-body position-relative">
                        <div class="tab-content" id="quizGalleryTabsContent">
                            <div class="tab-pane fade show active" id="quizTabPane" role="tabpanel"
                                aria-labelledby="quiz-tab">
                                <div class="question-slider px-2">
                                    @foreach ($liveShow->quizzes as $index => $quiz)
                                        <div class="px-2">
                                            <div class="card border mb-3">
                                                <div class="card-body" style="height: 450px; overflow-y:scroll">
                                                    <div class="text-center mb-4 fw-bold">
                                                        <div class="mb-2">Question {{ $index + 1 }} /
                                                            {{ $liveShow->quizzes->count() }}</div>
                                                        <div class="fw-bold h3">{{ $quiz->question }}</div>
                                                    </div>

                                                    @if ($quiz->options)
                                                        <div class="row g-3 mb-4">
                                                            @foreach ($quiz->options as $option)
                                                                <div class="col-md-6">
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
                                                                        <div class="progress" style="height: 8px;">
                                                                            <div id="option-result-bar-{{ $option->id }}"
                                                                                class="progress-bar @if ($option->is_correct) bg-success @else bg-primary @endif"
                                                                                role="progressbar" style="width: 0%">
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>

                                                        <form method="POST" id="quiz-timer-form-{{ $quiz->id }}"
                                                            onsubmit="submitQuizTimerForm(event, {{ $quiz->id }})"
                                                            class="row g-2 align-items-center justify-content-center">
                                                            @csrf
                                                            <div class="col-auto">
                                                                <div class="input-group">
                                                                    <span class="input-group-text bg-white"><i
                                                                            class="fas fa-stopwatch text-muted"></i></span>
                                                                    <input type="number" min="1"
                                                                        name="seconds" id="timer-{{ $quiz->id }}"
                                                                        value="10"
                                                                        class="form-control text-center fw-bold"
                                                                        style="width: 80px;" required>
                                                                </div>
                                                            </div>
                                                            @if ($loop->last)
                                                                <input type="hidden" name="is_last" value="1">
                                                            @endif
                                                            <div class="col-auto">
                                                                <div class="btn-group shadow-sm">
                                                                    <button type="submit"
                                                                        class="btn btn-success px-3">
                                                                        <i class="fas fa-play me-2"></i> Start
                                                                    </button>
                                                                    <button type="button"
                                                                        onclick="viewResponses({{ $liveShow->id }}, {{ $quiz->id }})"
                                                                        class="btn btn-info px-3 text-white">
                                                                        <i class="fas fa-chart-bar me-2"></i> Show
                                                                        Responses
                                                                    </button>
                                                                    <button class="btn btn-danger px-3" type="button"
                                                                        onclick="removeQuiz({{ $quiz->id }})">
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
                            <div class="tab-pane fade" id="galleryTabPane" role="tabpanel"
                                aria-labelledby="gallery-tab">

                                <div class="w-100">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="text-muted small text-uppercase fw-bold mb-0">
                                            Attached to this stream</h6>
                                        <button type="button"
                                            class="btn btn-sm btn-outline-secondary gallery-hide-on-stream-btn"
                                            title="Hide image/video overlay on live stream">
                                            <i class="fas fa-eye-slash"></i> Hide on stream
                                        </button>
                                    </div>
                                    <div id="gallery-attached-list" class="table-responsive mb-3"
                                        style="max-height: 520px; overflow-y: auto;">
                                        <table class="table table-sm table-dark table-hover align-middle mb-0">
                                            <thead>
                                                <tr>
                                                    <th scope="col" style="width: 30px;"></th>
                                                    <th scope="col" style="width: 40px;">#</th>
                                                    <th scope="col" style="width: 45px;">Thumb</th>
                                                    <th scope="col">Title</th>
                                                    <th scope="col" style="width: 65px;">Type</th>
                                                    <th scope="col" style="min-width: 160px; text-align: right;">
                                                        Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody id="attached-media-list">

                                            </tbody>
                                        </table>
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
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body p-0">
                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="chat-tab">
                                <div class="p-3">
                                    <button class="btn btn-primary" id="resetChatBtn" title="Reset Chat"
                                        data-bs-toggle="tooltip" data-bs-placement="top">
                                        <i class="fas fa-eraser"></i>
                                    </button>
                                    <a href="{{ route('admin.live-shows.export-all-chats-as-csv', $liveShow->id) }}"
                                        class="btn btn-primary" id="exportChatsBtn" title="Export Chats"
                                        data-bs-toggle="tooltip" data-bs-placement="top">
                                        <i class="fas fa-file-export"></i>
                                    </a>
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
                                                <th>Winners Prizes</th>
                                                <td>
                                                    @foreach ($liveShow->winnerPrizes as $winner)
                                                        <div
                                                            class="d-flex justify-content-start align-items-center mb-3">
                                                            <span class="badge bg-success">#{{ $winner->rank }}</span>
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
                                <div class="p-2">
                                    <iframe src="{{ url('live-show-play/' . $liveShow->id) }}?preview=true"
                                        class="live-show-preview-iframe"
                                        style="min-width: 100%; min-height: 844px; max-width: 390px; max-height: 844px; pointer-events: none; border-radius: 30px; border: 1px solid #ccc;"
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
        <script>
            Pusher.logToConsole = true;

            var pusher = new Pusher('{{ env('PUSHER_APP_KEY', '2a66d003a7ded9fe567a') }}', {
                cluster: '{{ env('PUSHER_APP_CLUSTER', 'eu') }}',
            });

            document.addEventListener('DOMContentLoaded', function() {
                fetchActivePlayers().then(data => {
                    appendPlayerList(data);
                });

                fetchChatMessages().then(messages => {
                    appendChatMessages(messages);
                });

                document.getElementById('resetChatBtn').addEventListener('click', function() {
                    if (confirm('Are you sure you want to reset the chat? All messages will be removed.')) {
                        resetChat();
                    }
                });

                document.querySelector('.gallery-hide-on-stream-btn')?.addEventListener('click', function(e) {
                    e.preventDefault();
                    galleryHideOnStream(e.currentTarget);
                    turnTrClassToTableSuccess(e.currentTarget);
                });


                fetchGalleryMediaItems();
                fetchGalleryShowStatus();
            });

            function fetchChatMessages() {
                // Simulate an API call to fetch chat messages
                return fetch(`{{ url('api/live-show') }}/{{ $liveShow->id }}/get-live-show-messages`)
                    .then(response => response.json())
                    .then(data => {
                        console.log('Chat messages:', data);
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
                if (!confirm('Are you sure you want to ' + action + ' this player?')) {
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
                            alert(data.message);
                            fetchActivePlayers().then(data => {
                                appendPlayerList(data);
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error updating player block status:', error);
                        alert('Error updating player block status: ' + error.message);
                    });
            }
            //every 20 seconds execute fetchActivePlayers and appendPlayerList
            // setInterval(() => {
            //     fetchActivePlayers().then(data => {
            //         appendPlayerList(data);
            //     });
            // }, 20000);

            //onlick #fetchPlayersButton execute fetchActivePlayers and appendPlayerList
            document.getElementById('fetchPlayersButton').addEventListener('click', function() {

                fetchActivePlayers().then(data => {
                    appendPlayerList(data);
                });
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
                        }
                    })
                    .catch(error => {
                        console.error('Error resetting chat:', error);
                    });
            }

            function updateChatAfterReset() {
                const chatContainer = document.querySelector('#live-chat-messages');
                if (chatContainer) {
                    chatContainer.innerHTML = '<p class="text-muted">No messages yet.</p>';
                }
            }



            function fetchActivePlayers() {
                // Simulate an API call to fetch active players
                var activePlayerUlElement = document.getElementById('activePlayersList');
                activePlayerUlElement.innerHTML =
                    '<li class="list-group-item bg-dark text-white d-flex align-items-center justify-content-center"><i class="fas fa-spinner fa-spin me-2"></i> Loading...</li>';

                return fetch(`{{ url('api/live-show') }}/{{ $liveShow->id }}/get-live-show-users`)
                    .then(response => response.json())
                    .then(data => {
                        // Assuming data is an array of player names
                        console.log('Active Players Data:', data);
                        users = data.users.map(player => {
                            return {
                                name: player.name,
                                id: player.id,
                                is_online: player.is_online,
                                is_winner: player.is_winner,
                                prize_won: player.prize_won,
                                status: player.status,
                                score: player.score,
                                is_blocked: player.is_blocked
                            }
                        });
                        return {
                            users: users,
                            totalUsers: data.totalUsers
                        };
                    })
                    .catch(error => {
                        console.error('Error fetching active players:', error);
                        return [];
                    });
            }

            function appendPlayerList(data) {
                console.log('Appending player list:', data);
                const activePlayersList = document.getElementById('activePlayersList');
                activePlayersList.innerHTML = ''; // Clear existing list
                if (data.totalUsers === 0) {
                    activePlayersList.innerHTML = '<li class="list-group-item bg-dark text-white">No active players</li>';
                    return;
                }

                data.users.forEach((player, index) => {
                    const li = `<li class="list-group-item bg-dark d-flex justify-content-between align-items-center">
                       
                        <div class='text-white'>
                            ${index + 1}.
                            <strong class='${player.status != 'eliminated' ? 'text-white' : 'text-secondary'}'>${player.name}</strong>
                            <span class="ms-2 ${player.is_online == 1 ? 'text-success' : 'text-secondary'}">
                           <i class="bi bi-circle-fill" style="font-size: 0.5rem;"></i>
                            </span>
                            <span class='ms-2 text-white'>
                                ${player.score !== null ? ` ${player.score}` : ''}
                                </span>
                            ${player.is_winner ? '<i class="bi bi-trophy-fill text-warning"></i>' : ''}
                            <div class='text-white'>
                                ${player.is_winner &&player.prize_won ? `Prize:<br> <span class='badge bg-primary'> ${player.prize_won} </span>` : ''}
                                </div>

                        </div>

                        <div>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" id="playerDropdownMenuButton${player.id}" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-three-dots"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end"
                                id="playerDropdownMenu${player.id}"
                                aria-labelledby="playerDropdownMenuButton">
                                    <li>
                                        <a class="dropdown-item" target="_blank" href="#">
                                            <i class="fas fa-eye"></i>
                                            View Details
                                        </a>
                                    </li>
                                    <li id="dd_option_toggleBlockStatusForPlayer${player.id}">
                                        <a  class="dropdown-item" href="javascript:void(0)"
                                        onclick="toggleBlockStatusForPlayer('${player.id}', '${player.is_blocked ? 'unblock' : 'block'}')"
                                        > 
                                        <i class="fas fa-ban"></i>
                                        ${player.is_blocked ? 'Unblock Player' : 'Block Player'}
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item"
                                        href="javascript:void(0)"
                                        onclick="resetScore('${player.id}')"
                                        > 
                                        <i class="fas fa-sync"></i>
                                        Reset Score
                                        </a>
                                    </li>  
                                </ul>
                            </div>
                        </div>
                    </li>`;
                    activePlayersList.innerHTML += li;
                });

                document.getElementById('total-users-count').innerText = `(${data.totalUsers})`;
            }

            const timerDiv = document.querySelector('#quizTimer');


            // Function to display a countdown timer in the div#quizTimer and hide it after countdown finishes
            function showQuizTimer(seconds) {
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



                            fetchActivePlayers().then(data => {
                                appendPlayerList(data);
                            });
                        }, 500); // Give a short delay before hiding
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
                    });
                showQuizTimer(seconds);
            }
        </script>


        <script>
            var channel2 = pusher.subscribe('live-show-message.{{ $liveShow->id }}');

            // System subscription event
            channel2.bind('pusher:subscription_succeeded', function() {
                console.log('Subscribed message event successfully!');
            });

            channel2.bind('LiveShowMessageEvent', function(data) {
                console.log('new message:', data.data);
                appendSingleMessage(data.data);
            });

            var channelResetChat = pusher.subscribe('reset-chat.{{ $liveShow->id }}');
            channelResetChat.bind('pusher:subscription_succeeded', function() {
                console.log('Reset chat channel subscribed successfully!');
            });
            channelResetChat.bind('ResetChatEvent', function() {
                console.log('Chat reset event received');
                const chatContainer = document.querySelector('#live-chat-messages');
                if (chatContainer) {
                    chatContainer.innerHTML = '<p class="text-muted">No messages yet.</p>';
                }
            });



            function removeQuiz(quizId) {
                // if (!confirm('Are you sure you want to remove this quiz question?')) {
                //     return;
                // }

                fetch(`{{ url('admin/live-shows/stream-management') }}/{{ $liveShow->id }}/quizzes/${quizId}/remove-quiz-question`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log('Quiz question removed:', data);
                        hideQuizTimer();
                        // Optionally, remove the quiz from the UI
                    })
                    .catch(error => {
                        console.error('Error removing quiz question:', error);
                    });
            }

            function updateWinners() {
                if (!confirm('Are you sure you want to announce winners?')) {
                    return;
                }

                fetch(`{{ route('admin.live-shows.update-winners', ['liveShowId' => $liveShow->id]) }}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log('Winners updated:', data);
                        // alert(data.message);
                        // Optionally, refresh the player list to show winners
                        fetchActivePlayers().then(data => {
                            appendPlayerList(data);
                        });
                    })
                    .catch(error => {
                        console.error('Error updating winners:', error);
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
                if (!confirm('Are you sure you want to update the status to ' + status + '?')) {
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
                        console.log("Live show updated:", data);
                        alert(data.message);
                        // Optionally redirect or update the UI
                    },
                    error: function(xhr, status, error) {
                        console.error("Error ending live show:", error);
                        console.log(xhr.responseText);
                    }
                });


            }


            function viewResponses(liveShowId, quizId) {
                fetch(`{{ url('admin/live-shows') }}/${liveShowId}/get-users-quiz-responses/${quizId}`, {
                        method: 'GET',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                    }).then(response => response.json())
                    .then(data => {
                        console.log('Quiz responses:', data);
                        // Handle displaying the responses in the UI
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
                    });
            }



            document.getElementById('resetGameButton').addEventListener('click', function() {
                if (!confirm(
                        'Are you sure you want to reset the game? This will remove all players current progress.')) {
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
                        console.log('Game reset:', data);
                        alert(data.message);
                        // Optionally, refresh the player list to show all players as active
                        fetchActivePlayers().then(data => {
                            appendPlayerList(data);
                        });
                    })
                    .catch(error => {
                        console.error('Error resetting game:', error);
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
                    alert('Please enter some text or a URL to generate the QR code.');
                    return;
                }

                // 4. Generate the QR Code using the QRCode constructor
                // Syntax: new QRCode(element, options);
                const qrcode = new QRCode(qrcodeContainer, {
                    text: dataToEncode,
                    width: 160,
                    height: 160,
                    colorDark: "#000000",
                    colorLight: "#ffffff",
                    correctLevel: QRCode.CorrectLevel.H // High error correction level
                });

                console.log('QR Code generated for:', dataToEncode);
            }

            function announcementEventTest() {
                fetch('{{ route('admin.announcement.send') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                }).then(response => response.text()).then(data => {
                    console.log('Announcement sent:', data);
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
                btn.disabled = true;
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
                            const label = btn.querySelector('i');
                            if (label) {
                                const orig = label.className;
                                label.className = 'fas fa-check';
                                setTimeout(() => {
                                    label.className = orig;
                                }, 800);
                            }
                            //update gallery show status to showing
                            updateGalleryShowStatus('showing');
                        }
                    })
                    .catch(err => console.error('Show on stream error:', err))
                    .finally(() => {
                        btn.disabled = false;
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
                btn.disabled = true;
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
                            const label = btn.querySelector('i');
                            if (label) {
                                const orig = label.className;
                                label.className = 'fas fa-check';
                                setTimeout(() => {
                                    label.className = orig;
                                }, 800);
                            }
                            //update gallery show status to hidden
                            updateGalleryShowStatus('hidden');
                        }
                    })
                    .catch(err => console.error('Hide on stream error:', err))
                    .finally(() => {
                        btn.disabled = false;
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
                            alert(data.message);
                        }
                    })
                    .catch(err => console.error('Gallery attach error:', err))
                    .finally(() => {
                        btn.disabled = false;
                    });
            }

            function galleryDetach(mediaId, btn) {
                const card = btn.closest('.gallery-media-card');
                if (!card) return;
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
                    .then(r => r.json())
                    .then(data => {

                        if (data.success) {
                            card.setAttribute('data-attached', '0');
                            const body = card.querySelector('.card-body');
                            if (body) {
                                let typeTag = '';
                                if (data.media && data.media.type) {
                                    if (data.media.type === 'video') {
                                        typeTag = '<span class="badge bg-primary mb-1">' +
                                            data.media.type.charAt(0).toUpperCase() + data.media.type.slice(1) +
                                            '</span>';
                                    } else if (data.media.type === 'image') {
                                        typeTag = '<span class="badge bg-warning text-dark mb-1">' +
                                            data.media.type.charAt(0).toUpperCase() + data.media.type.slice(1) +
                                            '</span>';
                                    } else {
                                        typeTag = '<span class="badge bg-info mb-1">' +
                                            data.media.type.charAt(0).toUpperCase() + data.media.type.slice(1) +
                                            '</span>';
                                    }
                                }
                                let titleHtml = '';
                                if (data.media && data.media.title) {
                                    titleHtml = '<div class="mt-2 text-muted small">' + data.media.title + '</div>';
                                }
                                body.innerHTML =
                                    typeTag +
                                    '<button type="button" class="btn btn-sm btn-outline-primary w-100 gallery-attach-btn" data-media-id="' +
                                    mediaId + '" title="Attach to stream"><i class="fas fa-plus"></i> Attach</button>' +
                                    titleHtml;
                            }
                            document.getElementById('gallery-available-list').appendChild(card);
                            const availableEmpty = document.getElementById('gallery-available-empty');
                            if (availableEmpty) availableEmpty.remove();
                            const attachedList = document.getElementById('gallery-attached-list');
                            if (attachedList && !attachedList.querySelector('.gallery-media-card')) {
                                const empty = document.createElement('div');
                                empty.className = 'col-12 text-muted small';
                                empty.id = 'gallery-attached-empty';
                                empty.innerHTML = 'None attached yet.'; // changed to innerHTML for consistency, here too
                                attachedList.appendChild(empty);
                            }
                        }
                    })
                    .catch(err => console.error('Gallery detach error:', err))
                    .finally(() => {
                        btn.disabled = false;
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
                                        media.gallery_media, idx));
                                });
                            }
                            initSortable();
                        } else {
                            alert(data.message);
                            return [];
                        }
                    })
                    .catch(err => console.error('Gallery media items error:', err))
            }

            function attachGalleryMediaItemRow(data, idx) {
                return `
                <tr class="gallery-media-card"
                    data-media-id="${data.id}" data-attached="1">
                    <td class="drag-handle text-center" style="cursor: grab;">
                        <i class="fas fa-grip-vertical text-muted"></i>
                    </td>
                    <td class="row-index text-muted small">
                        ${idx + 1}
                    </td>
                    <td class="p-1">
                        <img src="${data.is_image ? data.path : (data.thumbnail ?? data.path)}"
                            alt=""
                            style="width: 34px; height: 34px; object-fit: cover; border-radius: 4px; border: 1px solid #555;">
                    </td>
                    <td>
                        <span class="d-block" style="max-width: 370px;">
                            ${data.title || '—'}
                        </span>
                    </td>
                    <td>
                        <span class="badge ${data.type === 'video' ? 'bg-primary' : 'bg-warning text-dark'}">
                            ${data.type ?? ''}
                        </span>
                    </td>
                    <td>
                        <div class="d-flex gap-2 flex-wrap justify-content-end">
                            <button type="button"
                                class="btn btn-sm btn-success gallery-show-on-stream-btn"
                                onclick="galleryShowOnStream('${data.id}', this)"
                                data-media-id="${data.id}"
                                title="Show on live stream">
                                <i class="fas fa-tv"></i>
                                Show
                            </button>
                            <button type="button"
                                class="btn btn-sm btn-warning gallery-hide-on-stream-btn me-3"
                                onclick="galleryHideOnStream(this)"
                                data-media-id="${data.id}"
                                title="Hide on live stream">
                                <i class="fas fa-eye-slash"></i>
                                Hide
                            </button>
                            <button type="button"
                                class="btn btn-sm btn-outline-danger gallery-detach-btn"
                                data-media-id="${data.id}"
                                title="Remove from stream">
                                <i class="fas fa-times"></i>
                            </button>
                            <button type="button"
                                class="btn btn-sm btn-secondary" title="Preview"
                                onclick="openMediaPreviewModal('${data.is_image ? data.path : (data.thumbnail ?? data.path)}')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
            }

            function allMediaItemCard(data, idx) {
                return `
                <div class="col-6 col-md-3 col-lg-2">
                    <div class="card">
                        <img src="${data.is_image ? data.path : (data.thumbnail ?? data.path)}" class="card-img-top" alt="">    
                        <div class="card-body">
                            <h5 class="card-title">${data.title || '—'}</h5>
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
                        console.log('Gallery show status:', data);

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
                    console.log('All media:', data);
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
