-- Active: 1764218239848@@127.0.0.1@3306@live_baaboo
<x-app-dashboard-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Stream Management - {{ $liveShow->title }}
        </h2>
    </x-slot>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar: Active Players -->
            <nav class="col-md-2 bg-dark text-white p-3">
                <h5 class="mb-3">Active Players</h5>
                <ul class="list-group list-group-flush" id="activePlayersList">
                </ul>
            </nav>

            <!-- Main Content -->
            <main class="col-md-6 p-4">
                <!-- Stream / Question Control -->
                <div class="card mb-4">
                    <div
                        class="card-header bg-info text-dark d-inline-flex   justify-content-between align-items-center">
                        <div>
                            Live Stream & Question Control
                        </div>

                        <div class="">
                            <a href="{{ route('admin.live-shows.edit', $liveShow->id) }}" class="btn btn-success">Edit
                                Game Show</a>
                            <button class="btn btn-light text-primary" id="resetGameButton">
                                <i class="fas fa-redo me-2"></i>
                                Reset Game
                            </button>

                        </div>
                    </div>
                    <div class="card-body text-center">
                        <!-- Placeholder for video stream -->
                        <div class="row mb-3">
                            <div class="col-lg-6">
                                <img src="{{ $liveShow->thumbnail }}" class="img-fluid"
                                    style="width: auto; height: 200px; object-fit: contain;" alt="">
                            </div>
                            <div class="col-lg-6">
                                <div id="qrcode" style="width: 200px; height: 200px;background:whitesmoke"></div>
                                <a href="{{ url('live-show-play/' . $liveShow->id) }}">
                                    {{ url('live-show-play/' . $liveShow->id) }}
                                </a>
                            </div>
                        </div>
                        <div class="">
                            <!-- Slick Slider for Questions -->
                            <h4 class="text-center mb-4">Choose Question to Show</h4>
                            <div class="question-slider px-3  w-100 justify-content-center">
                                @foreach ($liveShow->quizzes as $quiz)
                                    <div>
                                        <h5 class="mb-5">{{ $quiz->question }}</h5>

                                        <div class=" ">




                                            @if ($quiz->options)
                                                <div class="w-100 px-5">
                                                    <div class="row w-100 w-100  p-4  mb-3 bg-light rounded"
                                                        style="border-rad">
                                                        @foreach ($quiz->options as $option)
                                                            <div
                                                                class="mb-4 col-lg-6 position-relative text-start {{ $option->is_correct == 1 ? 'bg-success text-white' : 'text-dark' }} rounded p-2">
                                                                {{ $option->option_text }}
                                                                <div class="option-result-container position-relative  "
                                                                    style="width: 100%; background: #eee; border-radius: 5px;">
                                                                    <div id="option-result-bar-{{ $option->id }}"
                                                                        class="option-result-bar"></div>
                                                                    <span id="option-result-label-{{ $option->id }}"
                                                                        style="position: absolute; top: 0; left: 50%; transform: translateX(-50%); font-size: 12px; color: black;">
                                                                        0%
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                                <div class="w-100 my-3 mb-3">

                                                    <form method="POST" action="" class="row"
                                                        id="quiz-timer-form-{{ $quiz->id }}"
                                                        onsubmit="submitQuizTimerForm(event, {{ $quiz->id }})">
                                                        @csrf
                                                        <div class="col-lg-4">
                                                            <div class="input-group w-100 ">
                                                                <span class="input-group-text">
                                                                    <i class="fa fa-clock"></i>
                                                                </span>
                                                                <input type="number" min="1" name="seconds"
                                                                    id="timer-{{ $quiz->id }}" value="10"
                                                                    style=""
                                                                    class="form-control form-control-sm text-center"
                                                                    required>

                                                            </div>
                                                        </div>


                                                        @if ($loop->last)
                                                            <input type="hidden" name="is_last" value="1">
                                                        @endif
                                                        <div class="col-lg-8">
                                                            <button type="submit" class="btn btn-sm btn-success">
                                                                <i class="fa fa-play"></i> Show Question
                                                            </button>

                                                            <button class="btn btn-sm btn-danger" type="button"
                                                                onclick="removeQuiz({{ $quiz->id }})">
                                                                <i class="fa fa-stop"></i> Hide Question
                                                            </button>

                                                            <button type="button"
                                                                onclick="viewResponses({{ $liveShow->id }}, {{ $quiz->id }})"
                                                                class="btn btn-sm btn-info" target="_blank">

                                                                Show Responses

                                                            </button>
                                                        </div>
                                                    </form>

                                                    <div class="my-2">

                                                    </div>
                                                </div>
                                            @endif

                                        </div>
                                    </div>
                                @endforeach
                            </div>


                        </div>

                        <div class="d-block  justify-items-start">
                            <div class="mb-4 flex-1 me-3 d-flex justify-content-start">
                                <button type="button" class="btn btn-warning" onclick="updateWinners()">
                                    Announce Winners
                                </button>
                            </div>



                            <div class="mb-2 flex-1 me-3 d-flex justify-content-start">
                                <form action="" method="post" id="live-show-status-form">
                                    <select class="form-select w-auto d-inline-block me-2" id="liveShowStatusSelect"
                                        onchange="">
                                        <option value="live" {{ $liveShow->status == 'live' ? 'selected' : '' }}>Live
                                        </option>
                                        <option value="completed"
                                            {{ $liveShow->status == 'completed' ? 'selected' : '' }}>
                                            Completed</option>
                                    </select>
                                    <button type="submit" class="btn btn-danger" onclick="">
                                        Update Live Show Status
                                    </button>
                                </form>
                            </div>
                        </div>

                    </div>


                </div>
            </main>

            <div class="col-lg-4">
                <ul class="nav nav-tabs">
                    <li class="nav-item">
                        <a href="#chat-tab" class="nav-link active" data-bs-toggle="tab">
                            Live Chat
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#live-show-preview" class="nav-link" data-bs-toggle="tab">
                            Live Show Preview
                        </a>
                    </li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="chat-tab">
                        <div class="w-100">
                            <aside class="w-100 p-4 bg-secondary rounded">
                                <h5 class="mb-3">Live Chat</h5>
                                <div id="live-chat-messages" style="">


                                </div>

                                <form onsubmit="event.preventDefault() ; sendMessage(event)" class="input-group">
                                    <input type="text" class="form-control" id="messageInput"
                                        placeholder="Type a message...">
                                    <button class="btn btn-primary" type="submit">
                                        Send
                                    </button>
                                </form>

                            </aside>
                        </div>
                    </div>



                    <div class="tab-pane" id="live-show-preview">
                        <div class="w-100">
                            <aside class="p-4 bg-secondary rounded">
                                <h5 class="mb-3">Live Show View</h5>
                                <div id="live-show-view" style="">
                                    {{-- <iframe style="width:100%; height:70vh; pointer-events:none;"
                                        src="{{ url('live-show-play/' . $liveShow->id . '?mute=1') }}"
                                        frameborder="0"></iframe> --}}
                                </div>

                            </aside>

                        </div>
                    </div>
                </div>

            </div>

            <!-- Chat Section -->




        </div>
    </div>

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
                        alert(data.message);
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
                    width: 200,
                    height: 200,
                    colorDark: "#000000",
                    colorLight: "#ffffff",
                    correctLevel: QRCode.CorrectLevel.H // High error correction level
                });

                console.log('QR Code generated for:', dataToEncode);
            }
        </script>
    @endpush

</x-app-dashboard-layout>
