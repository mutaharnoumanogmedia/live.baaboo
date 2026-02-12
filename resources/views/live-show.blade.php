<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, interactive-widget=resizes-content">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <title>baaboo Live | {{ $liveShow->title ?? '' }}</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="baaboo Live | {{ $liveShow->title ?? '' }}">
    <meta property="og:description" content="Join the baaboo Live Game Show and compete for prizes!">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ asset('og-image.webp') }}">
    <meta property="og:site_name" content="baaboo Live Game Show">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="baaboo Live | {{ $liveShow->title ?? '' }}">
    <meta name="twitter:description" content="Join the baaboo Live Game Show and compete for prizes!">
    <meta name="twitter:image" content="{{ asset('og-image.webp') }}">

    <link href='https://fonts.googleapis.com/css?family=Outfit' rel='stylesheet'>

    <style>
        :root {
            --primary-color: #ff5f00;
            --accent-color: #ffb380;
            --light-bg: #fff8f5;
            --text-dark: #2c3e50;
            --border-light: #ffe6d9;
        }

        body {
            background-color: #000;
            font-family: 'Outfit', sans-serif;
            padding-bottom: 76px;
            margin: 0;
            height: 100vh;
            overflow-y: scroll;


            background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%);
        }

        input,
        textarea,
        select {
            font-size: 16px !important;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #e55400;
        }

        .btn-outline-primary {
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        body .end-show {
            color: rgb(255, 255, 255);
            font-size: 2.5rem;
            position: absolute;
            top: 50%;
            width: 100%;
            text-align: center;
        }


        /* Bottom fixed navbar styling */
        .bottom-nav {
            background: #ffffff;
            border-top: 1px solid rgba(0, 0, 0, 0.08);
            box-shadow: 0 -6px 18px rgba(0, 0, 0, 0.06);
            height: 60px;
            background: white;
            z-index: 1030;
            /* above most stuff */
            padding: 0;

            /* iPhone home bar safe area */
        }

        /* Inner layout */
        .bottom-nav-inner {
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            align-items: center;
            height: 100%;
            padding: 0 12px;
        }

        /* Left / Center / Right areas */
        .bottom-nav-left {
            display: flex;
            justify-content: flex-start;
            align-items: center;
            min-width: 40px;
        }

        .bottom-nav-center {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .bottom-nav-right {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 8px;
        }

        /* Logo size */
        .bottom-nav-logo {
            height: 30px;
            width: auto;
        }

        /* Nice “app-like” button feel */
        .btn-register,
        .btn-user-profile {
            font-size: 0.85rem;
            letter-spacing: 0.2px;
            white-space: nowrap;
        }

        /* Slightly smaller on tiny screens */
        @media (max-width: 360px) {
            .bottom-nav-logo {
                height: 26px;
            }

            .btn-register,
            .btn-user-profile {
                font-size: 0.78rem;
                padding: 0.25rem 0.6rem !important;
            }
        }


        /* nav.navbar {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            z-index: 50;
            padding: 2px;
            padding-top: 15px;
            height: auto;
            background: bisque;
            transition: background 0.4s ease;
        } */

        .quiz-mode nav.navbar {
            background: transparent;
            transition: background 0.4s ease;
        }

        .main-container {
            position: absolute;
            height: calc(100vh - 100px);

            /* dynamic viewport */
            width: 100%;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
        }

        .main-container.quiz-mode {
            height: 92dvh;
            transform: scale(0.3) translateY(-150px);
            border-radius: 0px;
            overflow: hidden;
        }

        .option-result-container {
            display: none;
            width: 95% !important;
            position: absolute;
            border-radius: 5px;
            top: 1px;
            left: 16px;
            height: 40px;
            border-radius: 12px;
            opacity: 1;
        }

        .option-result-label {
            position: absolute;
            top: 6px;
            right: 0%;
            transform: translateX(-50%);
            font-size: 16px;
            color: black;
            font-weight: 600;
            z-index: 10;
        }

        .option-result-bar {
            height: 40px;
            background: #1e8fff48;
            width: 0;
            transition: width 0.4s ease-in-out;
            border-radius: 12px;
        }

        .video-container {
            position: relative;
            width: 100%;
            height: 100%;

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

        .video-container #player,
        .video-container iframe {
            border-radius: 00px;
            width: 100%;
            height: 100%;
            background: #000;
            object-fit: cover;
            pointer-events: all;
        }




        .video-container.question-activated .video-placeholder {
            background: linear-gradient(45deg, #764ba2, #667eea);
            transition: background 0.5s ease;
            height: 110px;
            width: 110px;
            z-index: 50;
            position: absolute;
            top: 100px;
            left: 50%;
            transform: translate(-50%, -50%);
            border-radius: 100%;
            overflow: hidden;
        }

        .live-indicator {
            /* position: absolute; */
            top: 20px;
            left: 15px;
            background: #dc354540;
            color: white;
            padding: 6px 12px;
            border-radius: 15px;
            font-size: 0.6rem;
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
            position: relative;
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


        #liveShowTabContainer {
            position: relative;
            bottom: 00px;
            width: 100%;
            z-index: 10;
            background: linear-gradient(to bottom, rgba(0, 0, 0, 0) 0%, rgba(0, 0, 0, 0.8) 50%, rgba(0, 0, 0, 1) 100%);
            height: auto;
            overflow: hidden;
        }

        #liveShowTabs li.nav-item {
            list-style: none;

        }

        #liveShowTabs {
            padding: 0px !important;
            display: block;
            text-align: center;
            border: none
        }

        #liveShowTabs li.nav-item .nav-link {
            color: white;
            background: rgba(255, 95, 0, 0.3);
            border: none;
            margin: 5px;
            border-radius: 20px;
            font-weight: 600;
            padding: 5px 15px;
        }

        #liveShowTabs li.nav-item .nav-link.active {
            background: rgba(255, 95, 0, 0.8);
            color: white;
        }

        .players-list-group-container {


            padding: 10px;
            background: #ffffff;
            border-radius: 15px;
            margin-bottom: 10px;
        }

        .players-list-group-container ul.list-group {
            max-height: 300px;
            overflow-y: auto;
        }

        /* TikTok-style overlay chat */
        .overlay-chat {
            z-index: 5;
            padding: 10px 00px 40px 0px;
            opacity: 0.7;
            overflow-y: scroll;
            scrollbar-width: none;
            height: 25vh;
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
            font-size: 1rem;
            color: white;
            margin-top: 2px;
        }

        /* Chat input at bottom */
        .bottom-chat-input {
            position: relative;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0, 0, 0, 0.8) !important;
            backdrop-filter: blur(20px);
            padding: 15px;
            padding-bottom: 25px;
            z-index: 10;
            /* height: 120px; */
            background: black;
            margin-bottom: 0px;
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
            font-size: 16px !important;
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
            max-height: 100%;
            overflow: auto;
        }

        .quiz-content {
            position: relative;
            padding: 0px;
            height: 100%;
            background: transparent !important;
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
            position: absolute;
            width: 100%;
            bottom: 0;
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
            background: #28a74580;
            border-color: #28a74580;
            color: white;
        }

        .quiz-option.correct label {
            background: #28a74580;
            border-color: #28a74580;
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
            z-index: 99999;
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


        .btn-register {

            padding: 10px 28px !important
        }

        .btn-user-profile {
            background-color: #28a74580;
            color: white;
            font-size: 0.8rem;
            border: none;
        }

        #playButtonOverlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 999999;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: rgba(0, 0, 0, 0.6);
        }

        #playButtonOverlay #playButton {
            background: var(--primary-color);
            border: none;
            border-radius: 50%;
            width: 100px;
            height: 100px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 20px rgba(255, 95, 0, 0.3);
            cursor: pointer;
        }

        #playButtonOverlay #playButton:hover {
            background: #e55400;
            transform: scale(1.1);
        }

        #playButtonOverlay #tapToPlayLabel {
            color: white;
            font-size: 0.9rem;
            margin-top: 15px;
            opacity: 0.8;
            text-align: center;
            width: 100px;
        }



        .alert-popup-middle {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);

            z-index: 9999;

            background: #ffffff !important;
            color: #000;
            padding: 20px 30px;

            border-radius: 8px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);

            width: auto;
            max-width: 90%;
            text-align: center;

            animation: fadeInScale 0.3s ease-out;
        }

        @keyframes fadeInScale {
            from {
                opacity: 0;
                transform: translate(-50%, -50%) scale(0.8);
            }

            to {
                opacity: 1;
                transform: translate(-50%, -50%) scale(1);
            }
        }

        .blinking-dot {
            color: red;
            animation: glow-blink 1.2s infinite;
        }

        @keyframes glow-blink {

            0%,
            100% {
                opacity: 1;
                text-shadow: 0 0 5px red, 0 0 10px red;
            }

            50% {
                opacity: 0.3;
                text-shadow: none;
            }
        }

        .typing-fade {
            display: flex;
            gap: 4px;
        }

        .typing-fade span {
            width: 8px;
            height: 8px;
            background: #999;
            border-radius: 50%;
            animation: fade 1s infinite;
        }

        .typing-fade span:nth-child(2) {
            animation-delay: 0.2s;
        }

        .typing-fade span:nth-child(3) {
            animation-delay: 0.4s;
        }

        @keyframes fade {

            0%,
            100% {
                opacity: 0.2;
            }

            50% {
                opacity: 1;
            }
        }

        .mobile-nav .nav-link {
            display: flex;
            flex-direction: column;
            align-items: center;
            font-size: 0.8rem;
            color: #333;
            transition: all 0.3s ease;
            border-radius: 20px;
        }

        .mobile-nav .nav-link.active {
            background: var(--primary-color);
            color: white;


        }

        #registerModal {
            z-index: 200000;
        }

        #registerModal+.modal-backdrop {
            z-index: 199000;
        }
    </style>
</head>

<body>

    <!-- Centered Play Button Overlay -->
    <div id="playButtonOverlay" style="">
        <button id="playButton" style="">
            <i class="fas fa-play fa-3x" style="color:white;"></i>
        </button>
        <div id="tapToPlayLabel" style="">
            Tap to Play
        </div>
    </div>
    <div class="main-container" id="mainContainer">
        <!-- Quiz Overlay -->
        <div class="quiz-overlay" id="quizOverlay">
            <div class="quiz-content">

                <div style="height: auto">
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



        <!-- Video Container -->
        <div class="video-container" id="videoContainer">
            <div class="video-placeholder" id="videoPlaceholder">
                {{-- <div id="player"></div> --}}
                <iframe id="live-broadcast-iframe" src="{{ route('show-live-broadcast', [$liveShow->id]) }}"
                    frameborder="0"></iframe>
            </div>

        </div>
    </div>

    <div class="fixed-bottom">
        <div id="liveShowTabContainer">

            <div class="tab-content" id="liveShowTabsContent">
                <div class="tab-pane fade show active" id="chatTab" role="tabpanel" aria-labelledby="chatTab-tab">
                    <div class="chat-container" id="chatContainer">
                        <!-- TikTok-style Overlay Chat -->
                        <div class="overlay-chat" id="overlayChat"></div>

                        <!-- Bottom Chat Input -->
                        <div class="bottom-chat-input">
                            <div class="chat-input-group">
                                <input type="text" class="chat-input-field" maxlength="120"
                                    placeholder="write something..." id="chatInput">
                                <button class="send-btn-overlay" id="send-btn-overlay" onclick="sendMessage()">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="tab-pane fade  " id="playerTab" role="tabpanel" aria-labelledby="playerTab-tab">
                    <!-- Player List -->
                    <div class="container-fluid ">
                        <div class="players-list-group-container">
                            <h5 class="mb-3"><i class="fas fa-users me-2 text-primary"></i>Players & Scores</h5>
                            <ul class="list-group" id="players-leaderbord">
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <nav class="navbar  mobile-nav bottom-nav bg-white border-top">
            <ul
                class="nav d-flex flex-row flex-nowrap w-100 justify-content-between align-items-center text-center px-2">

                <!-- 1) Logo -->
                <li class="nav-item flex-fill">
                    <a href="#"
                        class="nav-link d-flex flex-column align-items-center justify-content-center py-2 px-0">
                        <img src="https://baaboo.com/cdn/shop/files/baaboo-logo_1_256x.svg?v=1745568771"
                            alt="Logo" style="height:26px;width:auto;">
                    </a>
                </li>

                <!-- 2) Chat -->
                <li class="nav-item flex-fill" role="presentation">
                    <a class="nav-link active d-flex flex-column align-items-center justify-content-center py-2 px-0"
                        id="chatTab-tab" data-bs-toggle="tab" href="#chatTab" role="tab"
                        aria-controls="chatTab" aria-selected="true">
                        <i class="fas fa-comments fs-5"></i>
                        <small class="mt-1">Chat</small>
                    </a>
                </li>

                <!-- 3) Players -->
                <li class="nav-item flex-fill" role="presentation">
                    <a class="nav-link d-flex flex-column align-items-center justify-content-center py-2 px-0"
                        id="playerTab-tab" data-bs-toggle="tab" href="#playerTab" role="tab"
                        aria-controls="playerTab" aria-selected="false" onclick="updatePlayersLeaderboard()">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-users fs-5 me-1"></i>
                            <span id="user-count" class="fw-semibold small">0</span>
                        </div>
                        <small class="mt-1">Players</small>
                    </a>
                </li>

                <!-- 4) Register / Profile -->
                <li class="nav-item flex-fill" id="register-profile-item">
                    @guest('web')
                        <a href="#"
                            class="nav-link d-flex flex-column align-items-center justify-content-center py-2 px-0"
                            data-bs-target="#registerModal" data-bs-toggle="modal">
                            <i class="fas fa-user-plus fs-5"></i>
                            <small class="mt-1">Join</small>
                        </a>
                    @elseauth('web')
                        <a href="#"
                            class="nav-link d-flex flex-column align-items-center justify-content-center py-2 px-0"
                            data-bs-toggle="modal" data-bs-target="#userInfoModal">
                            <i class="fas fa-user fs-5"></i>
                            <small class="mt-1 text-truncate" style="max-width:70px;">
                                {{ Auth::guard('web')->user()->name }}
                            </small>
                        </a>
                    @endauth
                </li>

            </ul>
        </nav>
    </div>




    <!-- Register Modal -->
    <div class="modal fade" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 20px;">
                <div class="modal-header" style="border-bottom: none;">
                    <h5 class="modal-title" id="registerModalLabel">
                        <i class="fas fa-user-plus me-2 text-warning"></i>Register to Participate
                        <div>
                            <span style="font-size: 12px"> Already have an account? Login to participate.</span>
                        </div>
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
                        <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::guard('web')->user()->name ?? 'User') }}&background=ffb380&color=fff&size=96"
                            alt="Avatar" class="rounded-circle mb-2" width="80" height="80">
                    </div>
                    <h6 class="mb-1">{{ Auth::guard('web')->user()->name ?? 'Guest' }}</h6>
                    <div class="text-muted mb-3" style="font-size: 0.95rem;">
                        {{ Auth::guard('web')->user()->email ?? '' }}
                    </div>
                    <div class="mb-3">
                        <span class="badge bg-success" style="font-size: 1rem;">
                            <i class="fas fa-star me-1"></i>
                            {{ Auth::guard('web')->user()->points ?? 0 }} pts
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
        style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; z-index:9999; background:rgba(0,0,0,0.5); align-items:center; justify-content:center;max-height:100vh; overflow-y:auto;">
        <div
            style="background:#fff; border-radius:20px; padding:40px 30px; text-align:center; max-width:350px; margin:auto; margin-top: 20%; box-shadow:0 8px 32px rgba(0,0,0,0.2); ">
            <i class="fas fa-trophy fa-3x text-warning mb-3"></i>
            <h3 class="mb-2" style="color:#ff5f00;">Congratulations!</h3>
            <p class="mb-3" style="font-size:1.1rem;">You are selected as a winner!</p>
            <p class="mb-3" style="font-size:1.1rem;">Your prize money is: </p>
            <div class="text-center " style="font-size: 1.3rem; color:rgba(229, 84, 0, 1)" id="prizeAmount"></div>
            <button class="btn btn-success" onclick="document.getElementById('winnerDialog').style.display='none';">
                <i class="fas fa-check me-2"></i>Close
            </button>
        </div>
    </div>





    {{-- <button id="enable-push">
        Enable Notifications
    </button> --}}



    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>

    <script>
        let quizMode = false;
        let timer = 5;
        let currentCountdownSeconds = 0;

        let isCurrentAnswerCorrect = null;

        let isEliminated = {{ $isEliminated ? 'true' : 'false' }};

        const VAPID_PUBLIC_KEY = "{{ env('VAPID_PUBLIC_KEY') }}";
        const csrfToken = "{{ csrf_token() }}";

        let isLoggedIn = {{ Auth::guard('web')->check() ? 'true' : 'false' }};
        let userId = {{ Auth::guard('web')->check() ? Auth::guard('web')->user()->id : -1 }};
        console.log("initial val of issLoggedIn ", isLoggedIn, userId);


        if (isLoggedIn === true) {
            console.log("user is logged in, fetching player points");

        }

        console.log('isEliminated:', isEliminated);
        console.log('isLoggedIn:', isLoggedIn);

        Pusher.logToConsole = true;
        var pusher = new Pusher('{{ env('PUSHER_APP_KEY', '2a66d003a7ded9fe567a') }}', {
            cluster: '{{ env('PUSHER_APP_CLUSTER', 'eu') }}',
        });


        $(document).ready(function() {
            // Initialize Pusher
            fetchMessages();
            updatePlayersLeaderboard();
        });
        // Toggle quiz mode
        function toggleQuiz(action) {
            const mainContainer = document.getElementById('mainContainer');
            const quizOverlay = document.getElementById('quizOverlay');
            // const showQuestionBtn = document.getElementById('showQuestionBtn');
            const videoContainer = document.querySelector('#videoContainer');

            quizMode = !quizMode;

            if (action == "show") {
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
                        `<div class="quiz-option col-md-6 position-relative mb-3">  <div class="option-result-container " style=""> <div id="option-result-bar-${option.id}" class="option-result-bar"></div>  <span id="option-result-label-${option.id}" class="option-result-label"  style=""> 0% </span>  </div><input ${isEliminated ? 'disabled' : ''} type="radio" id="option${option.id}" name="option" value="${option.id}">  <label for="option${option.id}">${option.option_text}</label>  </div> `).join('')}
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
                            quiz_id: document.getElementById('quizId').value,
                            seconds_to_submit: currentCountdownSeconds
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

            if (isLoggedIn == false) {
                showRegisterModal();
                return;
            }
            const input = document.getElementById('chatInput');
            const message = input.value.trim();

            input.disabled = true;
            document.querySelector('#send-btn-overlay').disabled = true;

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
                        // addOverlayMessage('@You', message);
                        input.value = '';
                        input.disabled = false;
                        document.querySelector('#send-btn-overlay').disabled = false;
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
                        if (userId != msg.user.id) {
                            addOverlayMessage('@' + msg.user.name, msg.message);

                        }
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
            //console.log('Updating viewer count...');

            const viewerElement = document.querySelector('#user-count');
            fetch('{{ url('api/live-show/' . $liveShow->id . '/get-live-show-users') }}')
                .then(response => response.json())
                .then(data => {
                    const newCount = data.length;
                    viewerElement.innerHTML =
                        `${newCount.toLocaleString()} `;
                })
                .catch(() => {
                    // Optionally handle error or fallback
                });
        }
        updateViewerCount();


        @if ($liveShow->status == 'live')
            // setInterval(
            //     function() {
            //         updateViewerCount();
            //         updatePlayersLeaderboard();

            //     }, 10000);
        @endif

        // Prevent quiz overlay from closing when clicking inside
        document.getElementById('quizOverlay').addEventListener('click', function(e) {
            if (e.target === this) {
                toggleQuiz("show");
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
            errorDiv.innerHTML = '';


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

                        isLoggedIn = true;
                        userId = data.user.id;


                        enabledRegisterButton();

                        isEliminated = data.isEliminated == true ? true : false;

                        console.log('User registered successfully:', data, 'isEliminated:', isEliminated,
                            'isLoggedIn:', isLoggedIn, 'userId:', userId);

                        playerAsWinnerEventTrigger();
                    } else {
                        let errorMessages = data.messages || ['Registration failed. Please try again.'];

                        const ul = document.createElement('ul');
                        ul.classList.add('text-danger', 'mt-2'); // optional bootstrap styling

                        errorMessages.forEach(msg => {
                            const li = document.createElement('li');
                            li.textContent = msg;
                            ul.appendChild(li);
                        });

                        errorDiv.appendChild(ul);


                        errorDiv.style.display = 'block';

                        enabledRegisterButton();
                    }
                })
                .catch((error) => {


                    errorDiv.textContent = "Error : " + (error.message ||
                        'Failed to register. Please try again.');
                    errorDiv.style.display = 'block';

                    enabledRegisterButton();
                });
        });


        function enabledRegisterButton() {
            const registerButton = document.querySelector('#registerForm button[type="submit"]');
            registerButton.disabled = false;
            registerButton.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Register';
        }

        //function to replace the register button with username after successful registration
        function replaceRegisterButtonWithUsername(username) {
            const registerButtonDiv = document.querySelector('#register-profile-item');
            registerButtonDiv.innerHTML = `
                  <a href="#"
                        class="nav-link d-flex flex-column align-items-center justify-content-center py-2 px-0"
                        data-bs-toggle="modal" data-bs-target="#userInfoModal">
                        <i class="fas fa-user fs-5"></i>
                        <small class="mt-1 text-truncate" style="max-width:70px;">
                            ${username}
                        </small>
                    </a>
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
            toggleQuiz("show");

            //disable chatInput
            document.getElementById('chatInput').disabled = true;
            document.getElementById('send-btn-overlay').disabled = true;


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
            $(".option-result-container").css("display", "none");

            console.log('Showing quiz question:', quiz, 'with timer:', timer);
            appendQuizQuestion(quiz);
            startTimer(timer, evaluateAnswerWithTimeToSubmit);
            quizMode = false;
            toggleQuiz("show");

        }

        // showQuestionAndSetTimer(quizDummy, 100);



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
                currentCountdownSeconds = duration - timeLeft;
            }

            updateTimer();
            const timer = setInterval(updateTimer, 1000);
        }

        // Example: start a 10 second timer


        function evaluateAnswerWithTimeToSubmit() {
            document.querySelector('#quizTimer').style.display = "none";
            //disable all options
            document.querySelectorAll('input[name="option"]').forEach(option => {
                option.disabled = true;
            });

            console.log('Evaluating elimination. isCurrentAnswerCorrect:', isCurrentAnswerCorrect);
            if (!isEliminated && isLoggedIn) {
                if (isCurrentAnswerCorrect === true) {
                    appendQuestionResponseStatus('success');
                    fireConfetti();
                } else {
                    appendQuestionResponseStatus('fail');
                }
            }
            showVideoContainer();
        }


        function evaluateElinimation() {
            document.querySelector('#quizTimer').style.display = "none";

            console.log('Evaluating elimination. isCurrentAnswerCorrect:', isCurrentAnswerCorrect);
            if (!isEliminated && isLoggedIn) {
                if (isCurrentAnswerCorrect === true) {
                    fireConfetti();
                    appendQuestionResponseStatus('success');
                } else if (isCurrentAnswerCorrect === false) {
                    appendQuestionResponseStatus('fail');

                    isEliminated = true;
                } else {
                    isEliminated = true;
                    appendQuestionResponseStatus('warning');
                }
            } else {
                showVideoContainer();
            }

            // Reset for next question
            // isCurrentAnswerCorrect = null;
            //uncheckAndEnableOptions();
        }


        function appendQuestionResponseStatus(type) {
            const evaluationDiv = document.getElementById('evaluationStatus');
            let alertClass = 'alert-info';

            let message = ``;

            if (type === 'success') {
                alertClass = 'text-success';
                message = `<i class="fas fa-check-circle me-2"></i>Hurray!<br>Correct Answer.`;
            } else if (type === 'fail') {
                alertClass = 'text-danger';
                message = `<i class="fas fa-times-circle me-2"></i>Oops!<br>Wrong Answer`;
                // updateEliminatedStatus();

            } else {
                alertClass = 'text-warning';
                message = `<i class="fas fa-exclamation-circle me-2"></i> Wrong Answer!`;
                // updateEliminatedStatus();

            }

            evaluationDiv.innerHTML = `
                    <div class="alert alert-popup-middle ${alertClass} text-center w-auto mx-auto rounded" style='font-size: 1.2rem;' role="alert">
                        ${message}
                    </div>
                `;


            // Clear message after 5 seconds
            setTimeout(() => {
                evaluationDiv.innerHTML = '';
                document.querySelector('#quizTimer').style.display = "none";
                showVideoContainer();
                updatePlayersLeaderboard();
            }, 3000);
        }

        function showVideoContainer() {
            document.querySelector('#videoContainer').style.display = "block";
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
            toggleQuiz("remove");
            document.getElementById('chatInput').disabled = false;
            document.getElementById('send-btn-overlay').disabled = false;
        });



        function playerAsWinnerEventTrigger() {
            var channelShowWinner = pusher.subscribe(
                'live-show-winner-user.{{ $liveShow->id }}');
            // System subscription event
            channelShowWinner.bind('pusher:subscription_succeeded', function() {
                console.log('Winner Subscribed successfully!');
            });
            // Your Laravel broadcast event (drop the dot)
            channelShowWinner.bind('ShowPlayerAsWinnerEvent', function(data) {

                fireConfetti();

                let prizeMoney = parseFloat(data.prizeMoney).toFixed(2);

                document.getElementById('prizeAmount').textContent = prizeMoney + ' EUR';
                if (data.userId == userId) {
                    console.log('You are a winner!', data);

                    addOverlayMessage('@System', 'Congratulations! You have won ' + prizeMoney + ' EUR!');
                    showWinnerDialogDiv();
                }
                document.getElementById('playerTab-tab').click();


                // Optionally, you can add more UI feedback here, like a popup or sound effect.
            });

        }

        function showWinnerDialogDiv() {
            // Show the winner dialog
            document.querySelector('#winnerDialog').style.display = 'block';
            //hide question
            toggleQuiz("remove");

        }
        playerAsWinnerEventTrigger()
    </script>

    <script>
        // 1. Load the IFrame Player API
        var tag = document.createElement('script');
        tag.src = "https://www.youtube.com/iframe_api";
        var firstScriptTag = document.getElementsByTagName('script')[0];
        firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

        var player;

        // 2. Create player
        // function onYouTubeIframeAPIReady() {
        //     player = new YT.Player('player', {
        //         height: '315',
        //         width: '560',
        //         videoId: '{{ $liveShow->stream_id ?? 0 }}',
        //         playerVars: {
        //             autoplay: 1,
        //             controls: 0, // Hide play/pause bar
        //             disablekb: 1, // Disable keyboard shortcuts (e.g. spacebar)
        //             modestbranding: 1,
        //             rel: 0,
        //             playsinline: 1
        //         }
        //     });
        // }

        // 3. Play with sound after user click
        function playWithSoundAfterDelay() {
            setTimeout(function() {
                if (player && typeof player.unMute === 'function' && typeof player.playVideo === 'function') {
                    player.unMute();
                    player.playVideo();
                }
            }, 100);
        }

        // Example: call after user interaction
        document.getElementById('playButton').onclick = function() {
            document.getElementById('playButtonOverlay').style.display = 'none';
            playWithSoundAfterDelay();
        };

        var channelUpdateLiveShow = pusher.subscribe('update-live-show.{{ $liveShow->id }}');
        // System subscription event
        channelUpdateLiveShow.bind('pusher:subscription_succeeded', function() {
            console.log('Update Live Show Subscribed successfully!');
        });
        // Your Laravel broadcast event (drop the dot)
        channelUpdateLiveShow.bind('UpdateLiveShowEvent', function(data) {
            console.log('Update Live Show:', data);

            if (data.status && data.status != 'live') {
                emptyTheBodyWithEndShow('The live show status has changed to "' + data.status +
                    '". You will be redirected shortly.');
            } else {
                //reload the page to reflect the changes
                location.reload();
            }
            emptyTheBodyWithEndShow();
        });


        function emptyTheBodyWithEndShow(messageText = 'The live show has ended. Thank you for participating!') {
            document.body.innerHTML = '';
            document.body.style.backgroundColor = '#000';
            const endDiv = document.createElement('div');
            endDiv.className = 'end-show';
            endDiv.innerHTML = messageText;
            document.body.appendChild(endDiv);
        }


        function revealResponses(data) {
            $(".option-result-container").css("display", "block");
            console.log('Quiz responses:', data);
            // Handle displaying the responses in the UI
            let stats = data.statistics;

            stats.forEach(stat => {
                try {
                    let bar = document.getElementById(`option-result-bar-${stat.quiz_option_id}`);
                    let label = document.getElementById(`option-result-label-${stat.quiz_option_id}`);
                    if (bar) {
                        bar.style.width = `${stat.percentage}%`;
                    }
                    if (label) {
                        label.textContent = `${stat.percentage}% (${stat.total_response_for_option})`;
                    }
                    //make correct option green
                    console.log('Correct option id:', data.correctOptionId, 'Current option id:', stat
                        .quiz_option_id);
                    if (data.correctOptionId == stat.quiz_option_id) {
                        console.log("green for correct applying");

                        bar.style.background = '#28a74580'; // Green for correct
                    }
                } catch (e) {
                    console.error('Error revealing responses:', stat);
                }
            });

        }



        var channelUsersQuizResponses = pusher.subscribe('live-show-quiz-users-responses.{{ $liveShow->id }}');
        // System subscription event
        channelUsersQuizResponses.bind('pusher:subscription_succeeded', function() {
            console.log('Quiz Users responses successfully!');
        });
        // Your Laravel broadcast event (drop the dot)
        channelUsersQuizResponses.bind('LiveShowQuizUserResponses', function(data) {
            console.log('User Responses:', data);
            revealResponses(data);
        });


        @if ($liveShow->status != 'live')
            emptyTheBodyWithEndShow(
                'The status of this live show is "{{ $liveShow->status }}". You will be redirected shortly.'
            );
        @endif




        var channelGameReset = pusher.subscribe('live-show-game-reset.{{ $liveShow->id }}');
        // System subscription event
        channelGameReset.bind('pusher:subscription_succeeded', function() {
            console.log('Game reset channel subscribed successfully!');
        });
        // Your Laravel broadcast event (drop the dot)
        channelGameReset.bind('GameResetEvent', function(data) {
            console.log('Game reset event received:', data);


            // FORCE LOGOUT OR REDIRECT
            localStorage.clear();
            sessionStorage.clear();

            fetch('{{ route('livestream.logout', [$liveShow->id]) }}', {
                    method: 'POST',
                })
                .then(data => {
                    // Fetch was successful → now reload
                    alert('The game has been reset by the admin. You will be redirected.');
                    location.reload();
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        });


        var channel2 = pusher.subscribe('live-show-message.{{ $liveShow->id }}');

        // System subscription event
        channel2.bind('pusher:subscription_succeeded', function() {
            console.log('Subscribed message event successfully!');
        });

        // Your Laravel broadcast event (drop the dot)
        channel2.bind('LiveShowMessageEvent', function(data) {
            console.log('new message:', data.data);
            addOverlayMessage('@' + data.data.user.name, data.data.message);
        });

        function updatePlayersLeaderboard() {

            //fetch users list with scores
            fetch('{{ url('live-show/' . $liveShow->id . '/get-live-show-users-with-scores') }}')
                .then(response => response.json())
                .then(data => {


                    const users = data.users;
                    // console.log('Players with scores:', users);

                    const playersListContainer = document.getElementById('players-leaderbord');
                    playersListContainer.innerHTML = '';

                    users.forEach((user, index) => {

                        let bgColor = '';
                        switch (index) {
                            case 0:
                                bgColor =
                                    'background: linear-gradient(90deg, #FFD700 0%, #FFF8DC 100%);'; // Gold
                                break;
                            case 1:
                                bgColor =
                                    'background: linear-gradient(90deg, #C0C0C0 0%, #F5F5F5 100%);'; // Silver
                                break;
                            case 2:
                                bgColor =
                                    'background: linear-gradient(90deg, #CD7F32 0%, #FFE4C4 100%);'; // Bronze
                                break;
                            default:
                                bgColor = '';
                        }
                        const userDiv = document.createElement('div');
                        userDiv.className =
                            'player-list-item d-flex justify-content-between align-items-center mb-2 p-2 rounded ';
                        if (user.score > 0) {
                            userDiv.style = bgColor;
                        }

                        userDiv.innerHTML = `
                       
                                    <div >
                                <span style="margin-right: 20px;">${index + 1}</span>
                                        <strong>${user.name}</strong>
                                    
                                        <span class="ms-2">${user.is_winner ? '<i class="fas fa-trophy text-warning ms-2" title="Winner"></i>' : ''}</span>
                                    </div>
                                    <div>
                                        Score: ${user.score || 0}
                                    </div>
                                `;
                        playersListContainer.appendChild(userDiv);
                    });
                })
                .catch(error => console.error('Error fetching players with scores:', error));


        }
    </script>


    <script>
        /* REQUIRED conversion */
        function urlBase64ToUint8Array(base64String) {
            const padding = '='.repeat((4 - base64String.length % 4) % 4);
            const base64 = (base64String + padding)
                .replace(/-/g, '+')
                .replace(/_/g, '/');

            const rawData = atob(base64);
            return Uint8Array.from([...rawData].map(c => c.charCodeAt(0)));
        }

        // document.getElementById("playButton").addEventListener("click", function() {
        //     // enablePush();

        //     unmuteAndHide();
        // });

        async function enablePush() {
            if (!('serviceWorker' in navigator)) {
                console.log('Service workers not supported');
                return;
            }

            const permission = await Notification.requestPermission();
            if (permission !== 'granted') {
                console.log('Permission denied');
                return;
            }

            const registration = await navigator.serviceWorker.register('/sw.js');

            const subscription = await registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: urlBase64ToUint8Array(VAPID_PUBLIC_KEY)
            });

            await fetch('{{ url('/') }}/api/push/subscribe', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(subscription)
            });

            console.log('Push notifications enabled');
        }

        document.getElementById("playButton").addEventListener("click", function() {
            console.log('Tap to play clicked');
            // enablePush();
            if (isLoggedIn == false)
                showRegisterModal();

        });
        /**
         * Pusher test event subscription for debugging
         */
        var channelTest = pusher.subscribe('announcement-event-channel');
        channelTest.bind('pusher:subscription_succeeded', function() {
            console.log('Subscribed to announcement-event-channel successfully!');
        });
        channelTest.bind('AnnouncementEvent', function(data) {
            console.log('Received AnnouncementEvent:', data);
            alert('Pusher AnnouncementEvent received: ' + JSON.stringify(data));
        });
    </script>

    <!-- Safari: show "Touch to unmute" overlay and unmute videos inside broadcast iframe -->
    {{-- <script>
        (function() {
            // Uncomment to limit to Safari only:
            // var isSafari = /^((?!chrome|android|crios|fxios).)*safari/i.test(navigator.userAgent) ||
            //     /iPhone|iPad|iPod/.test(navigator.userAgent) ||
            //     (navigator.vendor && navigator.vendor.indexOf('Apple') > -1);
            // if (!isSafari) return;

            var style = document.createElement('style');
            style.textContent = [
                '.safari-unmute-overlay{',
                '  position:fixed;inset:0;z-index:9999;',
                '  display:flex;flex-direction:column;align-items:center;justify-content:center;',
                '  background:rgba(0,0,0,0.6);color:#fff;',
                '  font-family:-apple-system,system-ui,sans-serif;cursor:pointer;',
                '  -webkit-tap-highlight-color:transparent;',
                '}',
                '.safari-unmute-overlay .icon{',
                '  width:70px;height:70px;margin-bottom:15px;',
                '  border:3px solid #fff;border-radius:50%;',
                '  display:flex;align-items:center;justify-content:center;',
                '}',
                '.safari-unmute-overlay .icon svg{width:35px;height:35px;fill:#fff;}',
                '.safari-unmute-overlay .text{font-size:18px;font-weight:500;}',
                '.safari-unmute-overlay.hidden{display:none !important;}'
            ].join('');
            document.head.appendChild(style);

            var overlay = document.createElement('div');
            overlay.className = 'safari-unmute-overlay';
            overlay.innerHTML = [
                '<div class="icon">',
                '  <svg viewBox="0 0 24 24"><path d="M3 9v6h4l5 5V4L7 9H3zm13.5 3c0-1.77-1.02-3.29-2.5-4.03v8.05c1.48-.73 2.5-2.25 2.5-4.02zM14 3.23v2.06c2.89.86 5 3.54 5 6.71s-2.11 5.85-5 6.71v2.06c4.01-.91 7-4.49 7-8.77s-2.99-7.86-7-8.77z"/></svg>',
                '</div>',
                '<div class="text">Tap to enable sound</div>'
            ].join('');
            document.body.appendChild(overlay);

            var hasUnmuted = false;
            var retryInterval = null;
            var observer = null;

            // Get iframe document safely
            function getIframeDoc() {
                var iframe = document.getElementById('live-broadcast-iframe');
                try {
                    return iframe && iframe.contentDocument;
                } catch (e) {
                    return null; // Cross-origin error
                }
            }

            // Force unmute all videos in iframe
            function forceUnmute() {
                var doc = getIframeDoc();
                if (!doc) return false;

                var videos = doc.querySelectorAll('#root video');
                if (videos.length === 0) return false;

                var success = false;
                videos.forEach(function(v) {
                    console.log('Attempting unmute on video:', v);

                    // Multiple approaches
                    if (v.muted) {
                        v.muted = false;
                        success = true;
                    }
                    v.removeAttribute('muted');
                    v.volume = 1;

                    // Try to play if paused
                    if (v.paused) {
                        v.play().catch(function() {});
                    }
                });

                return success;
            }

            // Check if audio is actually enabled
            function isAudioWorking() {
                var doc = getIframeDoc();
                if (!doc) return false;

                var videos = doc.querySelectorAll('#root video');
                for (var i = 0; i < videos.length; i++) {
                    if (!videos[i].muted && videos[i].volume > 0) {
                        return true;
                    }
                }
                return false;
            }

            // Hide overlay and cleanup
            function hideOverlay() {
                overlay.classList.add('hidden');
                if (retryInterval) {
                    clearInterval(retryInterval);
                    retryInterval = null;
                }
                if (observer) {
                    observer.disconnect();
                    observer = null;
                }
            }

            // Start watching for videos in iframe (after user tap)
            function watchForVideos() {
                var doc = getIframeDoc();
                if (!doc) return;

                var root = doc.querySelector('#root');
                if (!root) return;

                observer = new MutationObserver(function() {
                    if (forceUnmute() && isAudioWorking()) {
                        hasUnmuted = true;
                        hideOverlay();
                    }
                });
                observer.observe(root, {
                    childList: true,
                    subtree: true
                });
            }

            // Main unmute handler - called on user tap
            function onUserTap(e) {
                if (e) e.preventDefault();

                console.log('User tapped - attempting unmute');

                // Attempt 1: Immediate
                forceUnmute();

                // Attempt 2-5: Quick retries
                setTimeout(forceUnmute, 100);
                setTimeout(forceUnmute, 300);
                setTimeout(forceUnmute, 600);
                setTimeout(forceUnmute, 1000);

                // Start polling every 500ms until success (max 30 seconds)
                var attempts = 0;
                retryInterval = setInterval(function() {
                    attempts++;
                    console.log('Retry attempt', attempts);

                    forceUnmute();

                    if (isAudioWorking()) {
                        console.log('Audio unmuted successfully!');
                        hasUnmuted = true;
                        hideOverlay();
                    } else if (attempts >= 60) { // 30 seconds max
                        console.log('Max retries reached');
                        clearInterval(retryInterval);
                        retryInterval = null;
                    }
                }, 500);

                // Also watch for new videos being added
                watchForVideos();

                // Remove tap listeners (user already tapped)
                overlay.removeEventListener('click', onUserTap);
                overlay.removeEventListener('touchend', onUserTap);

                // Hide overlay after tap regardless (user initiated action)
                setTimeout(function() {
                    overlay.classList.add('hidden');
                }, 500);
            }

            overlay.addEventListener('click', onUserTap);
            overlay.addEventListener('touchend', onUserTap, {
                passive: false
            });

            // Auto-hide if audio works without user tap (desktop browsers)
            var autoCheckInterval = setInterval(function() {
                if (isAudioWorking()) {
                    console.log('Audio already working - hiding overlay');
                    hideOverlay();
                    clearInterval(autoCheckInterval);
                }
            }, 1000);

            // Stop auto-check after 30 seconds
            setTimeout(function() {
                clearInterval(autoCheckInterval);
            }, 30000);

        })();
    </script> --}}
</body>

</html>
