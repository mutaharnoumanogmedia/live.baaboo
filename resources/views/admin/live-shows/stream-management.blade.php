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
            <main class="col-md-4 p-4">
                <!-- Stream / Question Control -->
                <div class="card mb-4" style="height: 70vh">
                    <div class="card-header bg-primary text-white">
                        Live Stream & Question Control
                    </div>
                    <div class="card-body text-center">
                        <!-- Placeholder for video stream -->
                        <div class="bg-dark text-white d-flex align-items-center justify-content-center mb-3"
                            style="height:150px;">
                            <img src="{{ $liveShow->thumbnail }}" class="img-fluid"
                                style="width: auto; height: 100%; object-fit: contain;" alt="">
                        </div>
                        <div class="alert">
                            <!-- Slick Slider for Questions -->
                            <h4 class="text-center mb-4">Choose Question to Show</h4>
                            <div class="question-slider d-inline-flex w-100 justify-content-center">
                                @foreach ($liveShow->quizzes as $quiz)
                                    <div>
                                        <h5 class="mb-2">{{ $quiz->question }}</h5>
                                        <p>{{ $quiz->body }}</p>
                                        @if ($quiz->options)
                                            <ul
                                                class="list-group list-group-horizontal-md w-100 justify-content-center mb-3">
                                                @foreach ($quiz->options as $option)
                                                    <li
                                                        class="list-group-item  {{ $option->is_correct == 1 ? 'bg-success text-white' : '' }}">
                                                        {{ $option->option_text }}
                                                    </li>
                                                @endforeach
                                            </ul>
                                            <div class="mb-3 w-auto">
                                                <center>
                                                    <form method="POST" action="" class="input-group"
                                                        style="max-width: 450px"
                                                        id="quiz-timer-form-{{ $quiz->id }}"
                                                        onsubmit="submitQuizTimerForm(event, {{ $quiz->id }})">
                                                        @csrf

                                                        <span class="input-group-text">Seconds:</span>
                                                        <input type="number" min="1" name="seconds"
                                                            id="timer-{{ $quiz->id }}" value="10"
                                                            class="form-control form-control-sm text-center" required>

                                                        @if ($loop->last)
                                                            <input type="hidden" name="is_last" value="1">
                                                        @endif

                                                        <button type="submit" class="btn btn-sm btn-success">
                                                            Set Timer & Show Question
                                                        </button>

                                                        <button class="btn btn-sm btn-danger" type="button"
                                                            onclick="removeQuiz({{ $quiz->id }})">
                                                            Remove
                                                        </button>
                                                    </form>

                                                </center>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>


                        </div>


                    </div>
                </div>
            </main>

            <!-- Chat Section -->
            <aside class="col-md-2 p-4 bg-secondary rounded">
                <h5 class="mb-3">Live Chat</h5>
                <div id="live-chat-messages" style="">


                </div>
                <div class="input-group">
                    <input type="text" class="form-control" placeholder="Type a message...">
                    <button class="btn btn-primary">Send</button>
                </div>
            </aside>
            <aside class="col-md-4 ">
                <div class="p-4 bg-secondary rounded">
                    <h5 class="mb-3">Live Show View</h5>
                    <div id="live-show-view" style="">
                        <iframe style="width:100%; height:70vh; pointer-events:none;"
                            src="{{ url('live-show-play/' . $liveShow->id . '?mute=1') }}" frameborder="0"></iframe>
                    </div>
                </div>
            </aside>
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
                border-bottom: 1px solid #ddd;
                margin-bottom: 10px;
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

                        data = data.map(player => {
                            return {
                                name: player.name,
                                is_online: player.pivot.is_online,
                                is_winner: player.is_winner
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
                if (!confirm('Are you sure you want to remove this quiz question?')) {
                    return;
                }

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
        </script>
    @endpush

</x-app-dashboard-layout>
