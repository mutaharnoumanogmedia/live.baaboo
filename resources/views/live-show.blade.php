<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>baaboo Live Game Show {{ $liveShow->id ?? '' }}</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css?family=Bitcount+Grid+Single');

        :root {
            --primary-color: #ff5f00;
            --accent-color: #ffb380;
            --light-bg: #fff8f5;
            --text-dark: #2c3e50;
            --border-light: #ffe6d9;
        }

        body {
            background-color: #000;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 0;
            margin: 0;
            height: 100vh;
            overflow: hidden;

            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        nav.navbar {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            z-index: 50;
            padding: 5px 15px;
        }

        .main-container {
            position: relative;
            height: 100vh;
            width: 100%;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .main-container.quiz-mode {
            transform: scale(0.3) translateY(-150px);
            border-radius: 0px;
            overflow: hidden;
        }

        .video-container {
            position: relative;
            width: 100%;
            height: 100vh;

            overflow: hidden;
        }

        .video-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            background: linear-gradient(45deg, #1e3c72, #2a5298);
            flex-direction: column;

        }

        .video-container iframe {
            border-radius: 00px;
            width: 100%;
            height: 100%;

            background: #000;
        }

        .video-container.question-activated {
            background: linear-gradient(45deg, #764ba2, #667eea);
            transition: background 0.5s ease;
            height: 100px;
            width: 100px;
            z-index: 50;
            position: absolute;
            top: 100px;
            left: 50%;
            transform: translate(-50%, -50%);
            border-radius: 100%;
        }

        .live-indicator {
            /* position: absolute; */
            top: 20px;
            left: 15px;
            background: #dc3545;
            color: white;
            padding: 6px 12px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 5px;
            z-index: 10;
        }

        .live-dot {
            width: 8px;
            height: 8px;
            background: white;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }

            100% {
                opacity: 1;
            }
        }

        .user-count {
            position: ;
            top: 20px;
            left: 15px;
            margin-top: 45px;
            background: rgba(0, 0, 0, 0.6);
            color: white;
            padding: 6px 12px;
            border-radius: 15px;
            font-size: 0.8rem;
            z-index: 10;
            backdrop-filter: blur(10px);
        }

        .logo {

            top: 10px;
            right: 10px;
            width: 70px;
            height: 30px;

            border-radius: 30px;

        }

        .logo img {
            width: 100%;
            height: auto;
            background-color: #ffe6d9;
            border-radius: 30px;
        }

        .show-question-btn {
            position: absolute;
            top: 50px;
            right: 5px;
            background: rgba(255, 95, 0, 0.9);
            color: white;
            border: none;
            padding: 5px;
            border-radius: 25px;
            font-size: 0.9rem;
            font-weight: 600;
            z-index: 10;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 20px rgba(255, 95, 0, 0.3);
        }

        .show-question-btn:hover {
            background: rgba(255, 95, 0, 1);
            transform: scale(1.05);
        }

        .show-question-btn.active {
            background: rgba(229, 84, 0, 0.9);
        }

        /* TikTok-style overlay chat */
        .overlay-chat {
            position: absolute;
            bottom: 80px;
            left: 1px;
            right: 1px;
            z-index: 5;
            padding: 30px 0px;
            background: linear-gradient(to bottom, rgba(0, 0, 0, 0) 0%, rgba(0, 0, 0, 0.8) 50%, rgba(0, 0, 0, 1) 100%);
            height: 250px;
            opacity: 0.7;
            overflow-y: scroll;
            scrollbar-width: none;
            /* Firefox */
            -ms-overflow-style: none;
            /* IE and Edge */
        }

        .overlay-chat::-webkit-scrollbar {
            display: none;
            /* Chrome, Safari, Opera */
        }

        .chat-message-overlay {
            border-radius: 20px;
            padding: 2px 12px;
            margin-bottom: 2px;

            word-wrap: break-word;
            animation: slideInLeft 0.5s ease-out;
        }

        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-100px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .chat-message-overlay .message-user {
            font-size: 0.8rem;
            color: var(--accent-color);
            font-weight: 600;
        }

        .chat-message-overlay .message-text {
            font-size: 0.9rem;
            color: white;
            margin-top: 2px;
        }

        /* Chat input at bottom */
        .bottom-chat-input {
            position: absolute;
            bottom: 20px;
            left: 0;
            right: 0;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(20px);
            padding: 15px;
            z-index: 10;
        }

        .chat-input-group {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .chat-input-field {
            flex: 1;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 25px;
            padding: 12px 16px;
            color: white;
            font-size: 0.9rem;
            outline: none;
            backdrop-filter: blur(10px);
        }

        .chat-input-field::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }

        .chat-input-field:focus {
            border-color: var(--primary-color);
            background: rgba(255, 255, 255, 0.15);
        }

        .send-btn-overlay {
            background: var(--primary-color);
            border: none;
            color: white;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .send-btn-overlay:hover {
            background: #e55400;
            transform: scale(1.1);
        }

        /* Quiz overlay */
        .quiz-overlay {
            position: fixed;
            top: 0px;
            left: 0;
            right: 0;
            bottom: 0;

            z-index: 20;
            transform: translateY(100%);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            overflow-y: auto;

            padding: 10px;

        }

        .quiz-overlay.active {
            transform: translateY(0);
            height: 580px;
        }

        .quiz-content {
            padding: 0px;
            height: 100%;
            background: var(--light-bg);
            border-radius: 20px;
        }

        .quiz-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .close-quiz-btn {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--text-dark);
            cursor: pointer;
            padding: 10px;
            border-radius: 50%;
            transition: all 0.3s ease;
        }

        .close-quiz-btn:hover {
            background: rgba(255, 95, 0, 0.1);
            color: var(--primary-color);
        }

        .quiz-section {
            background: white;
            border-radius: 15px;
            padding: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .quiz-question {
            color: var(--text-dark);
            font-weight: 600;
            margin-bottom: 20px;
            font-size: 1.2rem;
            line-height: 1.4;
        }

        .quiz-option {
            margin-bottom: 12px;
        }

        .quiz-option input[type="radio"] {
            display: none;
        }

        .quiz-option label {
            display: block;
            padding: 8px;
            background: var(--light-bg);
            border: 2px solid var(--border-light);
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            color: var(--text-dark);
            font-weight: 500;
        }

        .quiz-option label:hover {
            border-color: var(--accent-color);
            background: var(--accent-color);
            color: white;
            transform: translateY(-2px);
        }

        .quiz-option input[type="radio"]:checked+label {

            color: #fff;
            background: var(--primary-color);
            transform: translateY(-2px);
        }

        .quiz-option.correct input[type="radio"]:checked+label {
            background: #28a745;
            border-color: #28a745;
            color: white;
        }

        .quiz-option.correct label {
            background: #28a745;
            border-color: #28a745;
            color: white;
        }

        .quiz-option.incorrect label {
            background: #dc3545;
            border-color: #dc3545;
            color: white;
        }

        .submit-btn {
            background: var(--primary-color);
            border: none;
            color: white;
            padding: 15px 40px;
            border-radius: 25px;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 20px;
            box-shadow: 0 4px 15px rgba(255, 95, 0, 0.3);
        }

        .submit-btn:hover {
            background: #e55400;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 95, 0, 0.4);
        }


        #evaluationStatus {
            padding: 50px 2px;
        }

        @media (max-width: 767px) {}

        .main-container.quiz-mode {
            transform: scale(1) translateY(-0px);
        }




        .quiz-timer {
            width: 180px;
            height: 180px;
            margin: 0 auto 10px;
            /* center above question */
            position: relative;
        }

        .timer-svg {
            transform: rotate(0deg);
            /* start from top */
            width: 100%;
            height: 100%;
        }

        .timer-bg {
            fill: none;
            stroke: #ddd;
            stroke-width: 8;
        }

        .timer-progress {
            fill: none;
            stroke: #007bff;
            stroke-width: 8;
            stroke-linecap: round;
            stroke-dasharray: 283;
            /* 2 * π * r (2 * 3.14 * 45) */
            stroke-dashoffset: 0;
            transition: stroke-dashoffset 1s linear;
        }

        .timer-text {
            font-size: 4rem;
            font-family: 'Bitcount Grid Single';
            font-weight: bold;
            fill: #333;

            position: absolute;
            top: 42px;
            left: 2px;
            width: 180px;
            text-align: center;
        }


        @media (min-width: 992px) {
            .navbar-expand-lg {
                justify-content: space-between
            }
        }

        input[type="radio"]:disabled+label {
            background: #f0f0f0;
            border-color: #ddd;
            color: #aaa;
            cursor: not-allowed;
            pointer-events: none;
            opacity: 0.7;
        }
    </style>
</head>

<body>
    <div class="main-container" id="mainContainer">
        <!-- Quiz Overlay -->
        <div class="quiz-overlay" id="quizOverlay">
            <div class="quiz-content">
                <!-- <div class="quiz-header">
                <h3 style="color: var(--text-dark); margin: 0;">
                    <i class="fas fa-brain text-primary me-2"></i>
                    Live Quiz
                </h3>
                <button class="close-quiz-btn" onclick="toggleQuiz()">
                    <i class="fas fa-times"></i>
                </button>
            </div> -->
                <div style="height: 200px">
                    <div class="quiz-timer" id="quizTimer">
                        <svg class="timer-svg" viewBox="0 0 180 180" width="180" height="180">
                            <!-- Background circle -->
                            <circle class="timer-bg" cx="90" cy="90" r="60" stroke="#ddd"
                                stroke-width="10" fill="none" />

                            <!-- Progress circle -->
                            <circle class="timer-progress" cx="90" cy="90" r="60"
                                stroke="rgb(220, 53, 69)" stroke-width="10" fill="none" stroke-dasharray="376.991"
                                stroke-dashoffset="376.991" transform="rotate(-90 90 90)" />
                        </svg>

                        <div class="timer-text" id="timerText">10</div>
                    </div>

                    <div id="evaluationStatus"></div>
                </div>

                <div class="quiz-section" id="quizSection">
                    <div>
                        <input type="hidden" id="quizId" value="${quiz.id}">
                        <div class="quiz-question">
                            <i class="fas fa-question-circle text-primary me-2"></i>
                            ${quiz.question}
                        </div>

                        <div class="quiz-options row">
                            ${quiz.options.map((option, index) =>
                            `<div class="quiz-option"> <input type="radio" id="option${option.id}" name="option"
                                    value="${option.id}"> <label for="option${option.id}">${option.option_text}</label>
                            </div> `).join('')}
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <nav class="navbar navbar-expand-lg navbar-light" style="">
            <!-- Live Indicator -->
            <div class="live-indicator">
                <div class="live-dot"></div>
                LIVE
            </div>
            <div class="register-button" style="position: ; top: 20px; left: 90px;">
                @guest
                    <button class="btn btn-warning btn-sm" data-bs-target="#registerModal" data-bs-toggle="modal"
                        style="background-color: #ff5f00; border: none; font-weight: 600; padding: 5px 10px; border-radius: 20px; box-shadow: 0 4px 15px rgba(255, 95, 0, 0.3);">
                        <i class="fas fa-user-plus me-2"></i>REGISTER
                    </button>
                @else
                    <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#userInfoModal"
                        style="background-color: #28a745; border: none; font-weight: 600; padding: 5px 10px; border-radius: 20px; box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);">
                        <i class="fas fa-user me-2"></i>{{ Auth::user()->name }} <span id="auth-player-points">( pts)</span>
                    </button>
                @endguest
            </div>
            <div class="logo">
                <img src="https://baaboo.com/cdn/shop/files/baaboo-logo_1_256x.svg?v=1745568771" alt="">
            </div>

            <!-- User Count -->


            <!-- Logo -->

        </nav>
        <!-- Video Container -->
        <div class="video-container" id="videoContainer">
            <div class="video-placeholder" id="videoPlaceholder">
                <!-- <i class="fas fa-play-circle fa-4x mb-3"></i>
                <span>Live Stream</span>
                <small style="opacity: 0.8; margin-top: 10px;">Portrait Mode</small> -->
                <iframe width="320" height="568"
                    src="{{ $liveShow->stream_link }}?autoplay=1&mute={{ $_GET['mute'] ?? '0' }}&playsinline=1&controls=0&modestbranding=1&rel=0&showinfo=0"
                    title="YouTube video player" frameborder="0" allow="autoplay; encrypted-media; picture-in-picture"
                    allowfullscreen style="">
                </iframe>
            </div>



            <!-- Show Question Button -->
            {{-- <button class="show-question-btn" id="showQuestionBtn" onclick="toggleQuiz()">
                <i class="fas fa-question-circle me-2"></i>Show Question
            </button> --}}


        </div>


        <div class="chat-container" id="chatContainer">
            <!-- TikTok-style Overlay Chat -->
            <div class="overlay-chat" id="overlayChat">

            </div>

            <!-- Bottom Chat Input -->
            <div class="bottom-chat-input">
                <div class="chat-input-group">
                    <input type="text" class="chat-input-field" placeholder="Add a comment..." id="chatInput">
                    <button class="send-btn-overlay" onclick="sendMessage()">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </div>

        </div>
    </div>


    <!-- Register Modal -->
    <div class="modal fade" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 20px;">
                <div class="modal-header" style="border-bottom: none;">
                    <h5 class="modal-title" id="registerModalLabel">
                        <i class="fas fa-user-plus me-2 text-warning"></i>Register to Participate
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="registerForm" autocomplete="off">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="registerUsername" class="form-label">Username</label>
                            <input type="text" class="form-control" id="registerUsername" name="name" required
                                maxlength="32" placeholder="Enter username">
                        </div>
                        <div class="mb-3">
                            <label for="registerEmail" class="form-label">Email address</label>
                            <input type="email" class="form-control" id="registerEmail" name="email" required
                                placeholder="Enter email">
                        </div>
                        <div id="registerError" class="text-danger small" style="display:none;"></div>
                    </div>
                    <div class="modal-footer" style="border-top: none;">
                        <button type="submit" class="btn btn-warning w-100"
                            style="background-color: #ff5f00; border: none;">
                            <i class="fas fa-paper-plane me-2"></i>Register
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- User Info Modal -->
    <div class="modal fade" id="userInfoModal" tabindex="-1" aria-labelledby="userInfoModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 20px;">
                <div class="modal-header" style="border-bottom: none;">
                    <h5 class="modal-title" id="userInfoModalLabel">
                        <i class="fas fa-user me-2 text-success"></i>User Profile
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <div class="mb-3">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name ?? 'User') }}&background=ffb380&color=fff&size=96"
                            alt="Avatar" class="rounded-circle mb-2" width="80" height="80">
                    </div>
                    <h6 class="mb-1">{{ Auth::user()->name ?? 'Guest' }}</h6>
                    <div class="text-muted mb-3" style="font-size: 0.95rem;">
                        {{ Auth::user()->email ?? '' }}
                    </div>
                    <div class="mb-3">
                        <span class="badge bg-success" style="font-size: 1rem;">
                            <i class="fas fa-star me-1"></i>
                            {{ Auth::user()->points ?? 0 }} pts
                        </span>
                    </div>
                    <form method="POST" action="{{ route('livestream.logout', [$liveShow->id]) }}">
                        @csrf
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <!-- Winner Dialog -->
    <div id="winnerDialog"
        style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; z-index:9999; background:rgba(0,0,0,0.5); align-items:center; justify-content:center;">
        <div
            style="background:#fff; border-radius:20px; padding:40px 30px; text-align:center; max-width:350px; margin:auto; margin-top: 20%; box-shadow:0 8px 32px rgba(0,0,0,0.2);">
            <i class="fas fa-trophy fa-3x text-warning mb-3"></i>
            <h3 class="mb-2" style="color:#ff5f00;">Congratulations!</h3>
            <p class="mb-3" style="font-size:1.1rem;">You are selected as a winner!</p>
            <p class="mb-3" style="font-size:1.1rem;">Your prize money is: <span id="prizeAmount"></span></p>
            <button class="btn btn-success" onclick="document.getElementById('winnerDialog').style.display='none';">
                <i class="fas fa-check me-2"></i>Close
            </button>
        </div>
    </div>




    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>

    <script>
        let quizMode = false;
        let timer = 5;

        let isCurrentAnswerCorrect = null;
        let isEliminated = false;

        Pusher.logToConsole = true;
        var pusher = new Pusher('{{ env('PUSHER_APP_KEY', '2a66d003a7ded9fe567a') }}', {
            cluster: '{{ env('PUSHER_APP_CLUSTER', 'eu') }}',
        });


        $(document).ready(function() {
            // Initialize Pusher

            fetchMessages();




        });
        // Toggle quiz mode
        function toggleQuiz() {
            const mainContainer = document.getElementById('mainContainer');
            const quizOverlay = document.getElementById('quizOverlay');
            // const showQuestionBtn = document.getElementById('showQuestionBtn');
            const videoContainer = document.querySelector('#videoContainer');

            quizMode = !quizMode;

            if (quizMode) {
                mainContainer.classList.add('quiz-mode');
                quizOverlay.classList.add('active');
                videoContainer.classList.add('question-activated');
                // showQuestionBtn.style.display = 'none';
                // showQuestionBtn.classList.add('active');
            } else {
                mainContainer.classList.remove('quiz-mode');
                quizOverlay.classList.remove('active');
                videoContainer.classList.remove('question-activated');
                // showQuestionBtn.style.display = 'block';
                // showQuestionBtn.classList.remove('active');
            }
        }


        function showRegisterModal() {
            var registerModal = new bootstrap.Modal(document.getElementById('registerModal'));
            registerModal.show();
        }

        function appendQuizQuestion(quiz) {
            //reset current answer status

            console.log('Appending quiz question:', quiz);

            const quizSection = document.getElementById('quizSection');
            quizSection.innerHTML = `
            <div>
                <input type="hidden" id="quizId" value="${quiz.id}">
                    <div class="quiz-question">
                        <i class="fas fa-question-circle text-primary me-2"></i>
                        ${quiz.question}
                    </div>
                    <div class="quiz-options row">
                        ${quiz.options.map((option, index) =>
                        `<div class="quiz-option col-md-6"> <input ${isEliminated ? 'disabled' : ''} type="radio" id="option${option.id}" name="option" value="${option.id}">  <label for="option${option.id}">${option.option_text}</label>  </div> `).join('')}
                    </div>
             </div>
            `;

            // Re-attach event listeners for auto-submit on radio change
            document.querySelectorAll('input[name="option"]').forEach(option => {
                option.addEventListener('change', submitQuiz);
            });


        }

        // Quiz functionality
        function submitQuiz() {
            const selected = document.querySelector('input[name="option"]:checked');
            if (selected) {
                console.log('Selected option:', selected.value);

                //disable all options to prevent multiple submissions
                document.querySelectorAll('input[name="option"]').forEach(option => {
                    option.disabled = true;
                });
                const option = selected.value;
                console.log('Submitting option:', option);

                fetch('{{ url('live-show/' . $liveShow->id . '/submit-quiz') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            option: option,
                            quiz_id: document.getElementById('quizId').value
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log('Quiz submission response:', data);
                        if (data.success) {
                            console.log('Quiz submitted successfully:', data);
                            // Show correct/incorrect feedback
                            //using some instead of forEach to break the loop when correct answer is found, converting nodelist to array

                            [...document.querySelectorAll('.quiz-option')].some(optionDiv => {
                                const input = optionDiv.querySelectorAll('input')[0];
                                console.log('data.is_correct:', data.is_correct, 'input.value:', input
                                    .value,
                                    'data.correct_option_id:', data.correct_option_id);
                                if (data.is_correct && input.value == data.correct_option_id) {
                                    // optionDiv.classList.add('correct');

                                    isCurrentAnswerCorrect = true;
                                    console.log('Current answer is correct.');
                                    //stop lopping by returning true
                                    return true;


                                } else {
                                    // optionDiv.classList.add('incorrect');
                                    // add .correct to the correct option
                                    isCurrentAnswerCorrect = false;

                                    // const correctOption = document.querySelectorAll(
                                    //     `input[value="${data.correct_option_id}"]`)[0].parentElement;
                                    // if (correctOption) {
                                    //     correctOption.classList.add('correct');
                                    // }
                                }
                                input.disabled = true; // Disable further changes

                            });
                        } else {
                            //if authStatus
                            if (data.message && data.message == "unauthorized") {
                                //open register modal
                                var registerModal = new bootstrap.Modal(document.getElementById(
                                    'registerModal'));
                                registerModal.show();

                                //uncheck the selected option and enable all options
                                uncheckAndEnableOptions();

                            } else {
                                alert(data.message || 'Failed to submit quiz. Please try again.');
                            }
                        }
                    })
            } else {
                alert('Please select an answer first!');
            }
        }
        //auto submit the quiz when radio option is selected
        const quizOptions = document.querySelectorAll('input[name="quiz"]');
        quizOptions.forEach(option => {
            option.addEventListener('change', submitQuiz);
        });

        //uncheck and enable options
        function uncheckAndEnableOptions() {
            document.querySelectorAll('input[name="option"]').forEach(option => {
                option.checked = false;
                option.disabled = false;
                option.parentElement.classList.remove('correct', 'incorrect');
            });
        }

        // Chat functionality
        function sendMessage() {
            const input = document.getElementById('chatInput');
            const message = input.value.trim();

            if (message) {
                //ajax
                $.ajax({
                    url: '{{ url('live-show/' . $liveShow->id . '/send-message') }}',
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    data: {
                        'message': message,
                    },
                    success: function(response) {
                        // Handle success
                        addOverlayMessage('@You', message);
                        input.value = '';
                    },
                    error: function(xhr) {
                        // Handle error
                        console.error(xhr.responseText);
                        let errorMessage = xhr.responseJSON?.message || 'Failed to send message.';
                        if (errorMessage == "unauthorized") {
                            //open register modal
                            showRegisterModal();
                        }
                    }
                });
            }
        }

        function fetchMessages() {
            fetch('{{ url('/live-show/' . $liveShow->id . '/messages') }}')
                .then(response => response.json())
                .then(data => {
                    const overlayChat = document.getElementById('overlayChat');
                    overlayChat.innerHTML = ''; // Clear existing messages
                    data.messages.forEach(msg => {
                        addOverlayMessage('@' + msg.user.name, msg.message);
                    });
                })
                .catch(error => console.error('Error fetching messages:', error));
        }

        function addOverlayMessage(user, text) {
            const overlayChat = document.getElementById('overlayChat');
            const messageDiv = document.createElement('div');
            messageDiv.className = 'chat-message-overlay';
            messageDiv.innerHTML = `
                <div class="message-user">${user}</div>
                <div class="message-text">${text}</div>
            `;
            overlayChat.appendChild(messageDiv);
            overlayChat.scrollTop = overlayChat.scrollHeight;
        }

        // Allow Enter key to send message
        document.getElementById('chatInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });



        // Update viewer count periodically
        function updateViewerCount() {
            console.log('Updating viewer count...');

            const viewerElement = document.querySelector('#user-count');
            fetch('{{ url('api/live-show/' . $liveShow->id . '/get-live-show-users') }}')
                .then(response => response.json())
                .then(data => {
                    const newCount = data.length;
                    viewerElement.innerHTML =
                        `<i class="fas fa-users"></i> ${newCount.toLocaleString()} viewers`;
                })
                .catch(() => {
                    // Optionally handle error or fallback
                });
        }
        updateViewerCount();


        setInterval(updateViewerCount, 100000);

        // Prevent quiz overlay from closing when clicking inside
        document.getElementById('quizOverlay').addEventListener('click', function(e) {
            if (e.target === this) {
                toggleQuiz();
            }
        });
    </script>
    <script>
        // Bootstrap 5 modal trigger for register button

        document.getElementById('registerForm').addEventListener('submit', function(e) {
            e.preventDefault();
            //add loading state to the register button
            const registerButton = this.querySelector('button[type="submit"]');
            registerButton.disabled = true;
            registerButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Registering...';
            const username = document.getElementById('registerUsername').value.trim();
            const email = document.getElementById('registerEmail').value.trim();
            const errorDiv = document.getElementById('registerError');
            errorDiv.style.display = 'none';

            if (!username || !email) {
                errorDiv.textContent = 'Please fill in all fields.';
                errorDiv.style.display = 'block';
                return;
            }

            fetch('{{ url('live-show/' . $liveShow->id . '/user/register') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        name: username,
                        email: email
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        var modal = bootstrap.Modal.getInstance(document.getElementById('registerModal'));
                        modal.hide();
                        // Optionally: update UI to reflect logged-in user
                        addOverlayMessage('@' + username, 'has joined the chat!');
                        replaceRegisterButtonWithUsername(username);
                    } else {
                        errorDiv.textContent = typeof data.message === 'object' ? JSON.stringify(data.message) :
                            (data.message || 'Registration failed.');
                        errorDiv.style.display = 'block';
                    }
                })
                .catch(() => {
                    errorDiv.textContent = 'An error occurred. Please try again.';
                    errorDiv.style.display = 'block';
                });
        });

        //function to replace the register button with username after successful registration
        function replaceRegisterButtonWithUsername(username) {
            const registerButtonDiv = document.querySelectorAll('.register-button')[0];
            registerButtonDiv.innerHTML = `
                <button class="btn btn-success btn-sm" style="background-color: #28a745; border: none; font-weight: 600; padding: 5px 10px; border-radius: 20px; box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);" data-bs-toggle="modal" data-bs-target="#userInfoModal">
                    <i class="fas fa-user me-2"></i>${username}
                </button>
            `;
        }
    </script>
    <script>
        // Enable Pusher logging - disable in production


        var channel = pusher.subscribe('live-show-quiz.{{ $liveShow->id }}');

        // System subscription event
        channel.bind('pusher:subscription_succeeded', function() {
            console.log('Quiz Subscribed successfully!');
        });

        // Your Laravel broadcast event (drop the dot)
        channel.bind('LiveShowQuizQuestionEvent', function(data) {
            console.log('Quiz Question:', data);


            timer = data.timer;

            let quizQuestion = data.quizQuestion;
            showQuestionAndSetTimer(quizQuestion, timer);
            quizMode = false;
            toggleQuiz();
            // Auto close quiz after timer seconds
            // setTimeout(() => {
            //     toggleQuiz();
            // }, timer * 1000);

        });


        const quizDummy = {
            id: 1,
            question: "What is the capital of France?",
            options: [{
                    id: 1,
                    option_text: "Berlin"
                },
                {
                    id: 2,
                    option_text: "Madrid"
                },
                {
                    id: 3,
                    option_text: "Paris"
                },
                {
                    id: 4,
                    option_text: "Rome"
                }
            ]
        };


        function showQuestionAndSetTimer(quiz, timer) {
            isCurrentAnswerCorrect = null; //reset current answer status

            console.log('Showing quiz question:', quiz, 'with timer:', timer);
            appendQuizQuestion(quiz);
            startTimer(timer, evaluateElinimation);
            quizMode = false;
            toggleQuiz();
            // Auto close quiz after timer seconds
            // setTimeout(() => {
            //     toggleQuiz();
            // }, timer * 1000);
        }

        // showQuestionAndSetTimer(quizDummy, 7);



        function startTimer(duration, onComplete) {
            document.querySelector('#quizTimer').style.display = "block";
            const circle = document.querySelector(".timer-progress");
            const text = document.querySelector(".timer-text");

            const radius = 60;
            const circumference = 2 * Math.PI * radius;
            circle.style.strokeDasharray = circumference;

            let timeLeft = duration;

            function updateTimer() {
                text.textContent = timeLeft;
                const offset = -circumference + (timeLeft / duration) * circumference;
                circle.style.strokeDashoffset = offset;

                //if 5 seconds left change color to red
                if (timeLeft <= 5) {
                    circle.style.stroke = "#dc3545"; // Red color
                    document.querySelector('#videoContainer').style.display = "none";
                } else {
                    circle.style.stroke = "#007bff"; // Default color
                    document.querySelector('#videoContainer').style.display = "block";
                }

                if (timeLeft <= 0) {
                    clearInterval(timer);
                    //add  delay before calling onComplete
                    setTimeout(() => {
                        if (onComplete) onComplete();
                    }, 1500);
                }
                timeLeft--;
            }

            updateTimer();
            const timer = setInterval(updateTimer, 1000);
        }

        // Example: start a 10 second timer


        function evaluateElinimation() {
            document.querySelector('#quizTimer').style.display = "none";

            console.log('Evaluating elimination. isCurrentAnswerCorrect:', isCurrentAnswerCorrect);
            if (isCurrentAnswerCorrect === true) {
                fireConfetti();
                appendEvaluationStatus('success');
            } else if (isCurrentAnswerCorrect === false) {
                isEliminated = true;
                appendEvaluationStatus('fail');
            } else {
                appendEvaluationStatus('warning');
            }
            // Reset for next question
            // isCurrentAnswerCorrect = null;
            //uncheckAndEnableOptions();
        }


        function appendEvaluationStatus(type) {
            const evaluationDiv = document.getElementById('evaluationStatus');
            let alertClass = 'alert-info';

            let message = ``;

            if (type === 'success') {
                alertClass = 'alert-success';
                message = `<i class="fas fa-check-circle me-2"></i>Correct Answer!`;
            } else if (type === 'fail') {
                alertClass = 'alert-danger';
                message = `<i class="fas fa-times-circle me-2"></i> Eliminated!`;
                updateEliminatedStatus();

            } else {
                alertClass = 'alert-warning';
                message = `<i class="fas fa-exclamation-circle me-2"></i> Eliminated!`;
                updateEliminatedStatus();

            }

            evaluationDiv.innerHTML = `
                    <div class="alert ${alertClass} text-center w-auto mx-auto rounded" style='font-size: 1.2rem;' role="alert">
                        ${message}
                    </div>
                `;


            // Clear message after 5 seconds
            setTimeout(() => {
                evaluationDiv.innerHTML = '';
                document.querySelector('#quizTimer').style.display = "none";
                document.querySelector('#videoContainer').style.display = "block";

            }, 3000);
        }


        function updateEliminatedStatus() {
            if (isEliminated) {
                // Disable all options
                document.querySelectorAll('input[name="option"]').forEach(option => {
                    option.disabled = true;

                });
            }
            //ajax to update server about elimination status
            fetch('{{ url('live-show/' . $liveShow->id . '/update-elimination-status') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    is_eliminated: isEliminated
                })
            });
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.3/dist/confetti.browser.min.js"></script>
    <script>
        function fireConfetti() {
            confetti({
                particleCount: 300,
                spread: 100,
                origin: {
                    y: 0.6
                }
            });

        }
    </script>


    <script>
        // var pusher = new Pusher('{{ env('PUSHER_APP_KEY', '2a66d003a7ded9fe567a') }}', {
        //     cluster: '{{ env('PUSHER_APP_CLUSTER', 'eu') }}',
        // });

        var channelRemoveQuestion = pusher.subscribe('remove-live-show-quiz.{{ $liveShow->id }}');

        // System subscription event
        channelRemoveQuestion.bind('pusher:subscription_succeeded', function() {
            console.log('Quiz Removed successfully!');
        });

        // Your Laravel broadcast event (drop the dot)
        channelRemoveQuestion.bind('RemoveLiveShowQuizQuestionEvent', function(data) {
            console.log('Remove Quiz Question:', data);
            toggleQuiz();
            // Auto close quiz after timer seconds
            // setTimeout(() => {
            //     toggleQuiz();
            // }, timer * 1000);

        });




        @auth
        var channelShowWinner = pusher.subscribe('live-show-winner-user.{{ $liveShow->id }}.{{ Auth::user()->id }}');
        // System subscription event
        channelShowWinner.bind('pusher:subscription_succeeded', function() {
            console.log('Winner Subscribed successfully!');
        });
        // Your Laravel broadcast event (drop the dot)
        channelShowWinner.bind('ShowPlayerAsWinnerEvent', function(data) {
            console.log('You are a winner!', data);
            addOverlayMessage('@System', 'Congratulations! You are selected as a winner!');
            fireConfetti();

            document.getElementById('prizeAmount').textContent = data.prizeMoney + ' EUR';
            showWinnerDialogDiv();
            // Optionally, you can add more UI feedback here, like a popup or sound effect.
        });

        function showWinnerDialogDiv() {
            // Show the winner dialog
            document.querySelector('#winnerDialog').style.display = 'block';
        }
        @endauth
    </script>
</body>

</html>
