-- Active: 1764218239848@@127.0.0.1@3306@live_baaboo
<x-app-dashboard-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Stream Management - {{ $liveShow->title }}
        </h2>
    </x-slot>

    <div class="container-fluid  min-vh-100 py-3">
        <div class="row g-4">
            <nav class="col-lg-2">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header  border-bottom py-3">
                        <h6 class="mb-0 fw-bold text-uppercase small text-muted">
                            <i class="fas fa-users me-2 text-primary"></i>Active Players
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush" id="activePlayersList"
                            style="max-height: 80vh; overflow-y: auto;">
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
            </nav>

            <main class="col-lg-7">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center py-3">
                        <div>
                            <h4 class="mb-0 fw-bold  ">{{ $liveShow->title }}</h4>
                            <span class="badge bg-soft-info text-primary mt-1">Prize: {{ $liveShow->currency }}
                                {{ number_format($liveShow->prize_amount, 2) }}</span>
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

                    <div class="card-body ">
                        <div class="row align-items-center">
                            <div class="col-md-6 border-end text-center">
                                <label class="small text-muted d-block mb-2">Stream Thumbnail</label>
                                <img src="{{ $liveShow->thumbnail }}" class="rounded shadow-sm img-fluid"
                                    style="max-height: 180px; width: 100%; object-fit: cover;" alt="Thumbnail">
                            </div>
                            <div class="col-md-6 text-center">
                                <label class="small text-muted d-block mb-2">Join via QR Code</label>
                                <div id="qrcode" class="mx-auto p-2  border rounded"
                                    style="width: 160px; height: 160px;"></div>
                                <div class="mt-2">
                                    <a href="{{ url('live-show-play/' . $liveShow->id) }}"
                                        class="text-decoration-none small text-truncate d-block px-3">
                                        {{ url('live-show-play/' . $liveShow->id) }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-success py-3">
                        <h5 class="mb-0 fw-bold text-center">Question Management</h5>
                    </div>
                    <div class="card-body">
                        <div class="question-slider px-2">
                            @foreach ($liveShow->quizzes as $quiz)
                                <div class="px-2">
                                    <div class="card border mb-3">
                                        <div class="card-body">
                                            <h5 class="text-center mb-4 fw-bold">{{ $quiz->question }}</h5>

                                            @if ($quiz->options)
                                                <div class="row g-3 mb-4">
                                                    @foreach ($quiz->options as $option)
                                                        <div class="col-md-6">
                                                            <div
                                                                class="p-3 border rounded @if ($option->is_correct) border-success  @endif">
                                                                <div class="d-flex justify-content-between mb-2">
                                                                    <span
                                                                        class="fw-bold @if ($option->is_correct) text-success @endif">
                                                                        {{ $option->option_text }}
                                                                        @if ($option->is_correct)
                                                                            <i class="fas fa-check-circle ms-1"></i>
                                                                        @endif
                                                                    </span>
                                                                    <span class="small fw-bold"
                                                                        id="option-result-label-{{ $option->id }}">0%</span>
                                                                </div>
                                                                <div class="progress" style="height: 8px;">
                                                                    <div id="option-result-bar-{{ $option->id }}"
                                                                        class="progress-bar @if ($option->is_correct) bg-success @else bg-primary @endif"
                                                                        role="progressbar" style="width: 0%"></div>
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
                                                            <input type="number" min="1" name="seconds"
                                                                id="timer-{{ $quiz->id }}" value="10"
                                                                class="form-control text-center fw-bold"
                                                                style="width: 80px;" required>
                                                        </div>
                                                    </div>
                                                    @if ($loop->last)
                                                        <input type="hidden" name="is_last" value="1">
                                                    @endif
                                                    <div class="col-auto">
                                                        <div class="btn-group shadow-sm">
                                                            <button type="submit" class="btn btn-success px-3">
                                                                <i class="fas fa-play me-1"></i> Start
                                                            </button>
                                                            <button class="btn btn-danger px-3" type="button"
                                                                onclick="removeQuiz({{ $quiz->id }})">
                                                                <i class="fas fa-times me-1"></i> Hide
                                                            </button>
                                                            <button type="button"
                                                                onclick="viewResponses({{ $liveShow->id }}, {{ $quiz->id }})"
                                                                class="btn btn-info px-3 text-white">
                                                                <i class="fas fa-chart-bar me-1"></i> Show Responses
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
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h6 class="text-muted small text-uppercase fw-bold mb-3">Winners Ceremony</h6>
                                <button type="button" class="btn btn-warning w-100 py-2 fw-bold text-white shadow-sm"
                                    onclick="updateWinners()">
                                    <i class="fas fa-trophy me-2"></i> Announce Winners
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h6 class="text-muted small text-uppercase fw-bold mb-3">Show Status</h6>
                                <form action="" method="post" id="live-show-status-form" class="d-flex gap-2">
                                    <select class="form-select fw-bold" id="liveShowStatusSelect">
                                        <option value="live" {{ $liveShow->status == 'live' ? 'selected' : '' }}>🟢
                                            Live</option>
                                        <option value="completed"
                                            {{ $liveShow->status == 'completed' ? 'selected' : '' }}>🔴 Completed
                                        </option>
                                    </select>
                                    <button type="submit" class="btn btn-dark text-nowrap px-3">Update</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </main>

            <div class="col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header p-0">
                        <ul class="nav nav-tabs nav-fill border-0" id="rightPanelTabs">
                            <li class="nav-item">
                                <a href="#chat-tab" class="nav-link active py-3 border-0 border-bottom fw-bold"
                                    data-bs-toggle="tab">Chat</a>
                            </li>
                            <li class="nav-item">
                                <a href="#live-show-preview" class="nav-link py-3 border-0 border-bottom fw-bold"
                                    data-bs-toggle="tab">Live Show Details</a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body p-0">
                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="chat-tab">
                                <div id="live-chat-messages" class="p-3 "
                                    style="height: 65vh; overflow-y: auto;">
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

                            <div class="tab-pane fade" id="live-show-preview">
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
                                                    @if($liveShow->status == 'live')
                                                        <span class="badge bg-success">Live</span>
                                                    @elseif($liveShow->status == 'completed')
                                                        <span class="badge bg-danger">Completed</span>
                                                    @else
                                                        <span class="badge bg-secondary">{{ ucfirst($liveShow->status) }}</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Prize</th>
                                                <td>{{ $liveShow->currency }} {{ number_format($liveShow->prize_amount, 2) }}</td>
                                            </tr>
                                            <tr>
                                                <th>Start Time</th>
                                                <td>{{ $liveShow->start_time ? $liveShow->start_time->format('Y-m-d H:i') : '-' }}</td>
                                            </tr>
                                            <tr>
                                                <th>End Time</th>
                                                <td>{{ $liveShow->end_time ? $liveShow->end_time->format('Y-m-d H:i') : '-' }}</td>
                                            </tr>
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
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
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
                justify-content: flex-end;

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
        </style>
    @endpush

    @push('scripts')
        <script>
            Pusher.logToConsole = true;

            var pusher = new Pusher('{{ env('PUSHER_APP_KEY', '2a66d003a7ded9fe567a') }}', {
                cluster: '{{ env('PUSHER_APP_CLUSTER', 'eu') }}',
            });

            document.addEventListener('DOMContentLoaded', function() {
                fetchActivePlayers().then(activePlayers => {
                    appendPlayerList(activePlayers);
                });

                fetchChatMessages().then(messages => {
                    appendChatMessages(messages);
                });
            });

            function fetchChatMessages() {
                // Simulate an API call to fetch chat messages
                return fetch(`{{ url('api/live-show') }}/{{ $liveShow->id }}/get-live-show-messages`)
                    .then(response => response.json())
                    .then(data => {
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
                const alertBg = ['alert-primary', 'alert-secondary', 'alert-success', 'alert-danger', 'alert-warning',
                    'alert-info', 'alert-light', 'alert-dark'
                ];
                const chatContainer = document.querySelector('#live-chat-messages');
                let bgClass = alertBg[Math.floor(Math.random() * alertBg.length)];
                const messageDiv =
                    ` <div class="message alert ${bgClass} d-flex justify-content-between">
                                <div><strong>${message.user.name}:</strong> ${message.message}</div>
                                    <div class='px-2'> <button class="btn btn-sm btn-outline-danger rounded-circle" style=''> <i class="fas fa-ban"></i> </button> </div>
                            </div>`;

                chatContainer.insertAdjacentHTML('beforeend', messageDiv);
            }



            function fetchActivePlayers() {
                // Simulate an API call to fetch active players
                return fetch(`{{ url('api/live-show') }}/{{ $liveShow->id }}/get-live-show-users`)
                    .then(response => response.json())
                    .then(data => {
                        // Assuming data is an array of player names
                        console.log('Active Players Data:', data);

                        data = data.map(player => {
                            return {
                                name: player.name,
                                is_online: player.is_online,
                                is_winner: player.is_winner,
                                status: player.status
                            }
                        });
                        console.log(data);

                        return data;
                    })
                    .catch(error => {
                        console.error('Error fetching active players:', error);
                        return [];
                    });
            }

            function appendPlayerList(players) {
                const activePlayersList = document.getElementById('activePlayersList');
                activePlayersList.innerHTML = ''; // Clear existing list
                if (players.length === 0) {
                    activePlayersList.innerHTML = '<li class="list-group-item bg-dark text-white">No active players</li>';
                    return;
                }

                players.forEach(player => {
                    const li = `<li class="list-group-item bg-dark d-flex justify-content-between align-items-center">
                        <div>
                            <strong class='${player.status != 'eliminated' ? 'text-white' : 'text-secondary'}'>${player.name}</strong>
                            <span class="ms-2 ${player.is_online == 1 ? 'text-success' : 'text-secondary'}">
                           <i class="bi bi-circle-fill" style="font-size: 0.75rem;"></i>
                            </span>
                            ${player.is_winner ? '<i class="bi bi-trophy-fill text-warning"></i>' : ''}
                        </div>
                    </li>`;
                    activePlayersList.innerHTML += li;
                });
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
            }
        </script>


        <script>
            // Enable Pusher logging - disable in production


            var channel = pusher.subscribe('live-show-online-users.{{ $liveShow->id }}');

            // System subscription event
            channel.bind('pusher:subscription_succeeded', function() {
                console.log('Subscribed successfully!');
            });

            // Your Laravel broadcast event (drop the dot)
            channel.bind('LiveShowOnlineUsersEvent', function(data) {
                console.log('Active Users:', data.activeUsers);

                appendPlayerList(data.activeUsers);
                // You can also update DOM here:
                // document.getElementById('onlineUsers').innerHTML = JSON.stringify(data.activeUsers);
            });

            var channel2 = pusher.subscribe('live-show-message.{{ $liveShow->id }}');

            // System subscription event
            channel2.bind('pusher:subscription_succeeded', function() {
                console.log('Subscribed message event successfully!');
            });

            // Your Laravel broadcast event (drop the dot)
            channel2.bind('LiveShowMessageEvent', function(data) {
                console.log('new message:', data.data);

                appendSingleMessage(data.data);
                // You can also update DOM here:
                // document.getElementById('onlineUsers').innerHTML = JSON.stringify(data.activeUsers);
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
                        fetchActivePlayers().then(activePlayers => {
                            appendPlayerList(activePlayers);
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
                        fetchActivePlayers().then(activePlayers => {
                            appendPlayerList(activePlayers);
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
        </script>
    @endpush

</x-app-dashboard-layout>
