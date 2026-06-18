<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, interactive-widget=resizes-content">
    <meta name="google" content="notranslate">
    <meta name="translation" content="notranslate">



    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link rel="preconnect" href="https://js.pusher.com" crossorigin>
    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>


    <link rel="shortcut icon" href="{{ asset('images/favicon.ico') }}" type="image/x-icon">
    <title>{{ __('de.main_ui.title', ['title' => $liveShow->title ?? '']) }}</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">


    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('styles/live-show.css?' . time()) }}" rel="stylesheet">

    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="{{ __('de.main_ui.title', ['title' => $liveShow->title ?? '']) }}">
    <meta property="og:description" content="{{ __('de.main_ui.subtitle') }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ asset('og-image.webp') }}">
    <meta property="og:site_name" content="{{ __('de.main_ui.game_show') }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ __('de.main_ui.title', ['title' => $liveShow->title ?? '']) }}">
    <meta name="twitter:description" content="{{ __('de.main_ui.subtitle') }}">
    <meta name="twitter:image" content="{{ asset('og-image.webp') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">


    @include('partials.gtm', ['part' => 'head'])


    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.15.10/dist/sweetalert2.all.min.js"></script>

    {{-- ZEGOCLOUD ZIM (In-App Chat) Web SDK - exposes window.ZIM --}}
    {{-- <script src="https://unpkg.com/zego-zim-web@2.29.0/index.js"></script> --}}

    <style>
        #inactiveTabOverlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.94);
            z-index: 999999;
            display: none;
            align-items: center;
            justify-content: center;
            color: #fff;
            text-align: center;
            font-family: 'Outfit', sans-serif;
            backdrop-filter: blur(8px);
        }

        .inactive-tab-content {
            max-width: 420px;
            padding: 2.5rem 2rem;
        }

        .inactive-tab-icon {
            font-size: 3rem;
            margin-bottom: 1.2rem;
            color: #ff6b6b;
        }

        .inactive-tab-content h3 {
            font-weight: 700;
            margin-bottom: 0.75rem;
            font-size: 1.4rem;
        }

        .inactive-tab-content p {
            color: #b0b0b0;
            font-size: 0.95rem;
            line-height: 1.5;
            margin-bottom: 1.5rem;
        }

        .btn-use-here {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: #fff;
            padding: 0.75rem 2rem;
            border-radius: 50px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .btn-use-here:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 20px rgba(102, 126, 234, 0.4);
            color: #fff;
        }

        .main-container {
            position: relative;
            max-width: 720px;
            margin: 0 auto;
            height: 100vh;
            overflow: hidden;
        }

        /* Embedded Zego player (replaces iframe) */
        #zego-live-root {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            background: #000;
            overflow: hidden;
        }

        #zego-live-root iframe {
            width: 100%;
            height: 100%;
            border: none;
        }

        /* #zego-live-root video {
            object-fit: cover !important;
            width: 100% !important;
            height: 100% !important;
            pointer-events: none !important;

        } */

        .dIzgYQV4CBbzZxzJbwbS,
        #ZegoRoomFooter,
        #ZegoRoomHeader {
            display: none !important;
        }

        .QAHxuJxRZWb3P_cbR8QA {
            display: block !important;
        }

        /* .zg_autoplay_mask {
            display: none !important;
            background: rgba(0, 0, 0, 0.7) !important;
            font-family: -apple-system, system-ui, sans-serif !important;
            font-size: 18px !important;
        } */

        .IoC1lj0UQIKqG1pNh5vE,
        #zego_left_notify_wrapper,
        #zego_right_notify_wrapper,
        #ZegoRoomMobileLeaveButton,
        #ZegoRoomHeader,
        #ZegoRoomCssMobileMore,
        #ZegoRoomFooter,
        #ZegoVideoPlayerName {
            display: none !important;
        }

        #zego-live-root #ZegoRoomHeader,
        #zego-live-root #ZegoRoomFooter,
        #zego-live-root #ZegoRoomCssMobileMore,
        #zego-live-root #ZegoRoomMobileLeaveButton,
        #zego-live-root #zego_left_notify_wrapper,
        #zego-live-root #zego_right_notify_wrapper,
        #zego-live-root .PutMpvQbQ8ffCLR93qIq {
            display: none !important;
            visibility: hidden !important;
            height: 0 !important;
            max-height: 0 !important;
            overflow: hidden !important;
            pointer-events: none !important;
        }

        /*
        #root video {
            object-fit: contain !important;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }

        .quiz-mode #root video {
            object-fit: cover !important;
        } */

        /* ============================================================
           ZEGOCLOUD ZIM Chat tab styles (#zegoChatTab / #zimkit-container)
           ============================================================ */
        .zego-chat-wrapper {
            width: 100%;
            height: 600px;
            max-height: 70vh;
            display: flex;
            flex-direction: column;
            background: #fff;
        }

        .zimkit-container {
            position: relative;
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            background: #f8f9fb;
            border-top: 1px solid #ececec;
            font-family: 'Outfit', sans-serif;
        }

        .zimkit-status {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 14px 12px;
            font-size: 0.9rem;
            color: #555;
            background: #fff8e6;
            border-bottom: 1px solid #f1e3b8;
        }

        .zimkit-status.is-error {
            background: #fdecea;
            color: #b3261e;
            border-bottom-color: #f5c2bd;
        }

        .zimkit-status.is-ready {
            background: #e8f5ee;
            color: #1d6b3a;
            border-bottom-color: #c2e6d0;
        }

        .zimkit-messages {
            flex: 1 1 auto;
            overflow-y: auto;
            padding: 12px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .zimkit-message {
            max-width: 80%;
            padding: 8px 12px;
            border-radius: 14px;
            background: #fff;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.06);
            font-size: 0.92rem;
            line-height: 1.35;
            word-wrap: break-word;
            align-self: flex-start;
        }

        .zimkit-message.is-self {
            align-self: flex-end;
            background: linear-gradient(135deg, #ff8a4c 0%, #ff5f00 100%);
            color: #fff;
        }

        .zimkit-message .zimkit-author {
            display: block;
            font-size: 0.72rem;
            opacity: 0.7;
            margin-bottom: 2px;
        }

        .zimkit-input-bar {
            display: flex;
            gap: 8px;
            padding: 10px;
            background: #fff;
            border-top: 1px solid #ececec;
        }

        .zimkit-input-bar input {
            flex: 1;
            padding: 10px 14px;
            border: 1px solid #d5d7dc;
            border-radius: 999px;
            font-size: 0.95rem;
            outline: none;
        }

        .zimkit-input-bar input:focus {
            border-color: #ff5f00;
            box-shadow: 0 0 0 2px rgba(255, 95, 0, 0.15);
        }

        .zimkit-input-bar button {
            border: none;
            background: linear-gradient(135deg, #ff8a4c 0%, #ff5f00 100%);
            color: #fff;
            padding: 0 18px;
            border-radius: 999px;
            font-weight: 600;
            cursor: pointer;
        }

        .zimkit-input-bar button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
    </style>
</head>

<body>

    @include('partials.gtm', ['part' => 'body'])

    <div class="main-container">
        <!-- Single-Tab Restriction Overlay -->
        <div id="inactiveTabOverlay">
            <div class="inactive-tab-content d-none" id="inactiveTabContent">
                <div class="inactive-tab-icon">
                    <i class="fas fa-tv"></i>
                </div>
                <h3>{{ __('de.main_ui.title', ['title' => $liveShow->title ?? '']) }}</h3>
                <p>Die Live-Show ist in einem anderen Tab geöffnet.<br>Du kannst sie nur in einem Tab gleichzeitig
                    nutzen.
                </p>
                <button id="useHereBtn" class="btn btn-use-here">
                    <i class="fas fa-arrow-right me-2"></i>Hier verwenden
                </button>
            </div>
        </div>

        <!-- Centered Play Button Overlay -->
        <div id="playButtonOverlay" style="">
            <button id="playButton" style="">
                <i class="fas fa-play fa-3x" style="color:white;"></i>
            </button>
            <div id="tapToPlayLabel" style="">
                {{ __('de.main_ui.tap_to_play') }}
            </div>
        </div>
        <div class="main-container" id="mainContainer">
            <!-- Video Container -->
            <div class="video-container" id="videoContainer">
                <div class="video-placeholder" id="videoPlaceholder">
                    {{-- Zego UIKit (embedded; token from server) --}}
                    <div id="zego-live-root">
                        <iframe src="{{ route('live-show-broadcast', ['id' => $liveShow->id]) }}"
                            title="iframe video player" frameborder="0" allow="autoplay; encrypted-media; "
                            style=""></iframe>
                    </div>
                </div>

            </div>
            <!-- Floating heart reactions (TikTok/Instagram style) -->
            <div id="heartReactionsOverlay"></div>
            <!-- Quiz Overlay -->
            <div class="quiz-overlay" id="quizOverlay">
                <div class="quiz-content">

                    <div style="height: auto">
                        <div class="quiz-timer" id="quizTimer">
                            <svg class="timer-svg" viewBox="0 0 180 180" width="120" height="120">
                                <!-- Background circle -->
                                <circle class="timer-bg" cx="90" cy="90" r="80" stroke="#ddd"
                                    stroke-width="10" fill="none" />

                                <!-- Progress circle -->
                                <circle class="timer-progress" cx="90" cy="90" r="80"
                                    stroke="rgb(220, 53, 69)" stroke-width="10" fill="none"
                                    stroke-dasharray="376.991" stroke-dashoffset="376.991"
                                    transform="rotate(-90 90 90)" />
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
                                `<div class="quiz-option"> <input type="radio" id="option${option.id}"
                                        name="option" value="${option.id}"> <label
                                        for="option${option.id}">${option.option_text}</label>
                                </div> `).join('')}
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>

        <!-- Full-Screen Overlay Modal (not closable) -->
        <div id="galleryOverlayModal"
            style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.9); z-index:2000; align-items:center; justify-content:center; flex-direction:column;">
            <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center;">
                <img id="galleryOverlayImage" src="" alt=""
                    style="max-width:100vw; max-height:90vh; object-fit:contain; border-radius:16px; box-shadow:0 2px 32px 0 rgba(0,0,0,0.65); display:none;">
                <video id="galleryOverlayVideo" src="" autoplay muted playsinline preload="metadata"
                    poster=""
                    style="max-width:100vw; max-height:90vh; border-radius:16px; box-shadow:0 2px 32px 0 rgba(0,0,0,0.65); display:none;"></video>
            </div>
        </div>




        <!-- Register Modal -->
        <div class="modal fade" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content" style="border-radius: 20px;">
                    <div class="modal-header" style="border-bottom: none;">
                        <h5 class="modal-title" id="registerModalLabel">
                            <i class="fas fa-user-plus me-2 text-warning"></i>{{ __('de.registration.title') }}
                            <div>
                                <span style="font-size: 12px">{{ __('de.registration.already_account') }}</span>
                            </div>
                        </h5>

                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>

                    <form id="registerForm" autocomplete="off">
                        <div class="modal-body">
                            {{-- <div class="mb-3">
                            <label for="registerUsername" class="form-label">{{ __('de.registration.username') }}</label>
                            <input type="text" class="form-control" id="registerUsername" name="name" required
                                maxlength="32" placeholder="{{ __('de.registration.username_placeholder') }}">
                        </div> --}}
                            <div class="mb-3">
                                <label for="registerEmail"
                                    class="form-label">{{ __('de.registration.email') }}</label>
                                <input type="email" class="form-control" id="registerEmail" name="email"
                                    required placeholder="{{ __('de.registration.email_placeholder') }}">
                            </div>
                            <div class="gap-2 d-flex">
                                <input type="checkbox" class="form-check-input form-control-color" id="agree"
                                    required>
                                <label class="form-check-label" for="agree">{!! __('de.registration.terms') !!}</label>
                            </div>
                            <div id="registerError" class="text-danger small" style="display:none;"></div>
                        </div>
                        <div class="modal-footer" style="border-top: none;">
                            <button type="submit" class="btn btn-warning w-100"
                                style="background-color: #ff5f00; border: none;">
                                <i class="fas fa-paper-plane me-2"></i>{{ __('de.registration.register') }}
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
                            <i class="fas fa-user me-2 text-success"></i>{{ __('de.profile.title') }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="text-center modal-body">
                        <div class="mb-3">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::guard('web')->user()->name ?? 'User') }}&background=ffb380&color=fff&size=96"
                                alt="{{ __('de.profile.avatar') }}" class="mb-2 rounded-circle" width="80"
                                height="80">
                        </div>
                        <h6 class="mb-1">{{ Auth::guard('web')->user()->name ?? __('de.profile.guest') }}</h6>
                        <div class="mb-3 text-muted" style="font-size: 0.95rem;">
                            {{ Auth::guard('web')->user()->email ?? '' }}
                        </div>
                        <div class="mb-3">
                            <span class="badge bg-success" style="font-size: 1rem;">
                                <i class="fas fa-star me-1"></i>
                                <span id="user-points"></span> {{ __('de.profile.points') }}
                            </span>
                        </div>
                        <form method="POST" action="{{ route('livestream.logout', [$liveShow->id]) }}">
                            @csrf
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="fas fa-sign-out-alt me-2"></i>{{ __('de.profile.logout') }}
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
                <i class="mb-3 fas fa-trophy fa-3x text-warning"></i>
                <h3 class="mb-2" style="color:#ff5f00;">{{ __('de.winner.title') }}</h3>
                <p class="mb-2" style="font-size:1.1rem;">{{ __('de.winner.selected') }}</p>
                <p class="mb-2" style="font-size:1.1rem;">{{ __('de.winner.prize') }}</p>
                <div class="text-center mb-3" style="font-size: 1.3rem; color:rgba(229, 84, 0, 1)" id="prizeAmount">
                </div>
                <button class="btn btn-success"
                    onclick="document.getElementById('winnerDialog').style.display='none';">
                    <i class="fas fa-check me-2"></i>{{ __('de.profile.close') }}
                </button>
            </div>
        </div>


    </div>
    <div class="live-show-bottom-fixed" id="liveShowBottomFixed">
        <div id="liveShowTabContainer" class="to-be-hidden-on-mediaplay">

            <div class="tab-content" id="liveShowTabsContent">
                <div class="tab-pane fade show active" id="chatTab" role="tabpanel" aria-labelledby="chatTab-tab">
                    <div class="chat-container" id="chatContainer">
                        <!-- TikTok-style Overlay Chat -->
                        <div class="overlay-chat" id="overlayChat"></div>

                        <!-- Bottom Chat Input -->
                        <div class="bottom-chat-input">

                            <div class="input-group chat-input-group">
                                <input type="text" maxlength="200"
                                    placeholder="{{ __('de.main_ui.placeholder_message') }}" id="chatInput">
                                <button type="button" id="send-btn-overlay" onclick="sendMessage()">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                                <button type="button" id="heartReactionBtn"
                                    title="{{ __('de.main_ui.send_heart') }}">
                                    <i class="fas fa-heart"></i>
                                </button>
                            </div>
                            <div id="chatDisabledMsg"
                                style="display: none; text-align: center; font-size: 0.75rem; color: #999; padding: 4px 0 2px;">
                                Unser Chat macht gerade kurz Pause – hier ist heute richtig was los!
                            </div>

                        </div>

                    </div>
                </div>

                <div class="tab-pane fade " id="playerTab" role="tabpanel"
                    aria-labelledby="playerTab-tab position-relative">
                    <!-- Player List -->
                    <div class="container-fluid ">
                        <div class="players-list-group-container">
                            <h5 class="mb-3"><i
                                    class="fas fa-users me-2 text-primary"></i>{{ __('de.main_ui.players_scores') }}
                            </h5>
                            <ul class="list-group" id="players-leaderbord">
                            </ul>
                        </div>
                    </div>
                    <div id="players-list-loading-spinner" style="display: none;">
                        <i class="fas fa-spinner fa-spin"></i>
                    </div>
                </div>
            </div>
        </div>
        <nav class="navbar mobile-nav bottom-nav bg-nav-radial-top-gradient border-top">
            <ul
                class="flex-row px-2 text-center nav d-flex flex-nowrap w-100 justify-content-between align-items-center">

                <!-- 1) Logo -->
                <li class="nav-item flex-fill to-be-hidden-on-mediaplay d-flex justify-content-center">
                    <a href="#" class="nav-link d-flex flex-column align-items-center justify-content-center">
                        <img src="{{ asset('images/badabing-logo.webp') }}" alt="Logo"
                            style="height:46px;width:auto;">
                    </a>
                </li>

                <!-- 2) Chat -->
                <li class="nav-item flex-fill to-be-hidden-on-mediaplay d-flex justify-content-center"
                    role="presentation">
                    <a class="nav-link active d-flex flex-column align-items-center justify-content-center"
                        id="chatTab-tab" data-bs-toggle="tab" href="#chatTab" role="tab"
                        aria-controls="chatTab" aria-selected="true">
                        <i class="fas fa-comments fs-5"></i>
                        <small class="mt-1">{{ __('de.main_ui.chat') }}</small>
                    </a>
                </li>
                <!-- 3) Players -->
                <li class="nav-item flex-fill to-be-hidden-on-mediaplay d-flex justify-content-center"
                    role="presentation" id="player-tab-nav-item">
                    <a class="nav-link d-flex flex-column align-items-center justify-content-center"
                        id="playerTab-tab" data-bs-toggle="tab" href="#playerTab" role="tab"
                        aria-controls="playerTab" aria-selected="false" onclick="updatePlayersLeaderboard()">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-users fs-6 me-1"></i>
                            <span id="user-count" class="fw-semibold small">0</span>
                        </div>
                        <small class="mt-1">{{ __('de.main_ui.players') }}</small>
                    </a>
                </li>

                <!-- 4) Register / Profile -->
                <li class="nav-item flex-fill d-flex justify-content-center" id="register-profile-item">
                    @guest('web')
                        <a href="#" class="nav-link d-flex flex-column align-items-center justify-content-center"
                            data-bs-target="#registerModal" data-bs-toggle="modal">
                            <i class="fas fa-user-plus fs-5"></i>
                            <small class="mt-1">{{ __('de.main_ui.join') }}</small>
                        </a>
                    @elseauth('web')
                        <a href="#" class="nav-link flex-column align-items-center d-flex justify-content-center"
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



    {{-- Push-notification opt-in is handled by the shared partial included at the
         bottom of this page (resources/views/partials/push-notification.blade.php). --}}

    <script>
        let quizMode = false;
        let timer = 5;


        const $chatInput = document.getElementById('chatInput');
        const $sendBtnOverlay = document.getElementById('send-btn-overlay');
        const $videoContainer = document.querySelector('#videoContainer');
        const $quizOverlay = document.getElementById('quizOverlay');
        const $overlayChat = document.getElementById('overlayChat');
        const OVERLAY_CHAT_MAX_MESSAGES = 100;

        const overlay = document.getElementById('galleryOverlayModal');
        const img = document.getElementById('galleryOverlayImage');
        const vid = document.getElementById('galleryOverlayVideo');


        let currentCountdownSeconds = 0;
        let responseTimeMs = 0;
        let currentCountdownMilliseconds = 0; // total elapsed ms
        let countdownStartTime = null; // set when timer starts

        let isCurrentAnswerCorrect = null;
        let isEliminated = {{ $isEliminated ? 'true' : 'false' }};
        let isChatEnabled = {{ $liveShow->chat_enabled ? 'true' : 'false' }};
        let isUserBlockedFromChat = false;

        let winnerAnnounced = {{ $liveShow->winners_announced ? 1 : 0 }};

        const zegoLiveRoot = document.getElementById('zego-live-root');

        const csrfToken = "{{ csrf_token() }}";


        let isLoggedIn = {{ Auth::guard('web')->check() ? 'true' : 'false' }};
        let userId = {{ Auth::guard('web')->check() ? Auth::guard('web')->user()->id : -1 }};
        console.log("initial val of issLoggedIn ", isLoggedIn, userId);

        let liveShowStatus = '{{ $liveShow->status }}';
        console.log("initial val of liveShowStatus ", liveShowStatus);


        if (isLoggedIn === true) {
            console.log("user is logged in, fetching player points");
        }

        // console.log('isEliminated:', isEliminated);
        console.log('isLoggedIn:', isLoggedIn);
        @if (env('APP_ENV') !== 'production')
            Pusher.logToConsole = true;
        @endif

        var pusher = new Pusher('{{ env('PUSHER_APP_KEY', '2a66d003a7ded9fe567a') }}', {
            cluster: '{{ env('PUSHER_APP_CLUSTER', 'eu') }}',
        });



        // Example: call after user interaction
        var channelUpdateLiveShow = pusher.subscribe('live-show.{{ $liveShow->id }}');
        // System subscription event
        channelUpdateLiveShow.bind('pusher:subscription_succeeded', function() {
            console.log('Update Live Show Subscribed successfully!');
        });
        // Your Laravel broadcast event (drop the dot)
        channelUpdateLiveShow.bind('UpdateLiveShowEvent', function(data) {
            console.log('Update Live Show:', data);
            location.reload();
        });

        function toggleToBeHiddenOnMediaPlay(displayProperty) {
            document.querySelectorAll('.to-be-hidden-on-mediaplay').forEach(function(element) {
                element.style.display = displayProperty;
            });
        }


        function emptyTheBodyWithEndShow(messageText = 'Die Live-Sendung ist beendet. Vielen Dank für Ihre Teilnahme!') {
            document.body.innerHTML = '';
            document.body.style.backgroundColor = '#000';
            const endDiv = document.createElement('div');
            endDiv.style.textAlign = 'center';
            endDiv.style.marginTop = '100px';
            endDiv.style.marginLeft = 'auto';
            endDiv.style.marginRight = 'auto';
            endDiv.style.width = '100%';
            endDiv.style.padding = '20px';
            endDiv.className = 'end-show';
            endDiv.innerHTML = messageText +
                `<br>Weitere Infos: <a href="https://badabing.show" class="text-white" target="_blank">badabing.show</a>`;
            document.body.appendChild(endDiv);
        }


        function revealResponses(data) {
            $(".option-result-container").css("display", "block");
            console.log('Quiz responses:', data);
            // Handle displaying the responses in the UI
            let stats = data.statistics;

            stats.forEach(stat => {
                try {
                    let quizOptionDiv = document.getElementById(`quiz-option-${stat.quiz_option_id}`);
                    let bar = document.getElementById(`option-result-bar-${stat.quiz_option_id}`);
                    let label = document.getElementById(`option-result-label-${stat.quiz_option_id}`);

                    let correctQuizOptionDiv = document.getElementById(`quiz-option-${data.correctOptionId}`);
                    if (bar) {
                        bar.style.width = `${stat.percentage}%`;
                    }
                    if (label) {
                        label.textContent = `${stat.percentage}% (${stat.total_response_for_option})`;
                    }
                    //make correct option green
                    // console.log('Correct option id:', data.correctOptionId, 'Current option id:', stat.quiz_option_id);
                    // if (data.correctOptionId == stat.quiz_option_id) {
                    // console.log("green for correct applying");

                    // Find the parent .quiz-option of the bar and add class 'correct' to it
                    if (correctQuizOptionDiv) {
                        correctQuizOptionDiv.classList.add('correct');

                        //if checked option is the correct option, fire confetti
                        if (document.querySelector('input[name="option"]:checked').value == data.correctOptionId) {
                            console.log('Checked option:', document.querySelector('input[name="option"]:checked')
                                .value, 'Correct option:', data.correctOptionId);

                            fireConfetti();
                            appendQuestionResponseStatus('success');
                            playSound('correct');
                        } else {
                            appendQuestionResponseStatus('fail');
                            playSound('wrong');
                        }
                    }

                    // }
                } catch (e) {
                    console.error('Error revealing responses:', stat);
                }
            });
            updatePlayersLeaderboard();

        }



        var channelUsersQuizResponses = pusher.subscribe('live-show.{{ $liveShow->id }}');
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
            emptyTheBodyWithEndShow('{{ $updateMessage }}');
        @endif
        @if ($liveShow->media_visible)
            // document.getElementById("liveShowBottomFixed").style.display = "none";
            toggleToBeHiddenOnMediaPlay('none');
        @endif




        var channelGameReset = pusher.subscribe('live-show.{{ $liveShow->id }}');
        // System subscription event
        channelGameReset.bind('pusher:subscription_succeeded', function() {
            // console.log('Game reset channel subscribed successfully!');
        });
        // Your Laravel broadcast event (drop the dot)
        channelGameReset.bind('GameResetEvent', function(data) {
            // console.log('Game reset event received:', data);


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


        var channel2 = pusher.subscribe('live-show.{{ $liveShow->id }}');

        // System subscription event
        channel2.bind('pusher:subscription_succeeded', function() {
            // console.log('Subscribed message event successfully!');
        });


        var channelChatMessages = pusher.subscribe('live-show-chat-messages.{{ $liveShow->id }}');
        // Your Laravel broadcast event (drop the dot)
        channelChatMessages.bind('LiveShowChatMessagesEvent', function(data) {
            // console.log('new message:', data.data);
            if (data.data.user.id != userId) {
                addOverlayMessage('@' + data.data.user.name, data.data.message);
            }
        });
        channel2.bind('HideLiveShowWinnersTabEvent', function(data) {
            console.log('Hide winners tab event received:', data);
            hideWinnersTabForParticipants();
        });
        channel2.bind('ShowLiveShowWinnersTabEvent', function(data) {
            console.log('Show winners tab event received:', data);

            showWinnersTabForParticipants();
        });


        var channelChatStatus = pusher.subscribe('live-show-chat-status.{{ $liveShow->id }}');
        channelChatStatus.bind('pusher:subscription_succeeded', function() {
            console.log('Chat status channel subscribed successfully!');
        });
        channelChatStatus.bind('LiveShowChatStatusUpdatedEvent', function(data) {
            console.log('Chat status updated:', data);
            applyChatStatus(!!data.chatEnabled, true);
        });

        // ---------------------------------------------------------------
        // Background audio: preload + play-by-name
        // ---------------------------------------------------------------
        const SOUND_FILES = {
            'time-tick': '{{ asset('/badabing-audio/time-tick.mp3') }}',
            'wrong': '{{ asset('/badabing-audio/wrong-sound.mp3') }}',
            'winner': '{{ asset('/badabing-audio/winner.mp3') }}',
            'correct': '{{ asset('/badabing-audio/correct-sound.mp3') }}',
        };
        let isTimeTickSoundPlaying = false;

        // Cache of preloaded HTMLAudioElement templates, keyed by sound name.
        const SOUND_CACHE = new Map();

        function preloadSounds() {
            Object.entries(SOUND_FILES).forEach(([name, url]) => {
                if (SOUND_CACHE.has(name)) return;
                const audio = new Audio();
                audio.src = url;
                audio.preload = 'auto';
                // Touch load() so browsers begin fetching immediately
                try {
                    audio.load();
                } catch (e) {
                    /* noop */
                }
                SOUND_CACHE.set(name, audio);
            });
        }

        /**
         * Play a preloaded sound in the background by file name.
         * Each call clones the cached element so overlapping plays don't cut each other off.
         *
         * @param {string} name  Key from SOUND_FILES (e.g. 'correct', 'wrong', 'winner', 'time-tick')
         * @param {object} [opts]
         * @param {number} [opts.volume=1.0]  0.0 to 1.0
         * @param {boolean} [opts.loop=false]
         * @returns {HTMLAudioElement|null}  The audio instance (use with stopSound()), or null if unknown name.
         */
        function playSound(name, opts = {}) {
            const template = SOUND_CACHE.get(name);
            if (!template) {
                console.warn('[playSound] unknown sound:', name);
                return null;
            }

            const audio = template.cloneNode();
            audio.volume = typeof opts.volume === 'number' ?
                Math.max(0, Math.min(1, opts.volume)) :
                0.8;
            audio.loop = false;

            const p = audio.play();
            if (p && typeof p.catch === 'function') {
                p.catch(err => {
                    // Browsers block autoplay until the user has interacted with the page.
                    console.warn('[playSound] could not play "' + name + '":', err && err.message);
                });
            }
            return audio;
        }

        /**
         * Stop a sound returned from playSound() and reset its position.
         */
        function stopSound(audioInstance) {
            if (!audioInstance) return;
            try {
                audioInstance.pause();
                audioInstance.currentTime = 0;
            } catch (e) {
                /* noop */
            }
        }

        $(document).ready(function() {
            // onLoadGameShow();
            // Initialize Pusher
            preloadSounds();
            fetchMessages();
            updatePlayersLeaderboard();
            updateChatComposerState();



            if (isLoggedIn) {
                checkIfUserBlockedFromLiveShow();
            }

        });







        function updatePlayersLeaderboard() {
            //fetch users list with scores
            return fetch('{{ url('live-show/' . $liveShow->id . '/get-live-show-users-with-scores') }}')
                .then(response => response.json())
                .then(data => {
                    const users = data.users;
                    const totalUsers = data.totalUsers;
                    // console.log('Players with scores:', users, 'totalUsers:', totalUsers);

                    const playersListContainer = document.getElementById('players-leaderbord');
                    playersListContainer.innerHTML = '';
                    const you = data.you;


                    users.forEach((user, index) => {
                        let winnerBgColorClass = '';
                        switch (index) {
                            case 0:
                                winnerBgColorClass =
                                    'winner-div gold-div'; // Gold
                                break;
                            case 1:
                                winnerBgColorClass =
                                    'winner-div silver-div'; // Silver
                                break;
                            default:
                                winnerBgColorClass =
                                    'winner-div bronze-div'; // Bronze
                                break;

                        }
                        const userDiv = document.createElement('div');
                        userDiv.className =
                            'player-list-item d-flex justify-content-between align-items-center mb-2 p-2 rounded ' +
                            (user.id == userId ? 'sticky-position' : '');
                        if (user.score > 0 && user.is_winner) {
                            userDiv.className += ` ${winnerBgColorClass}`;
                        }

                        userDiv.innerHTML = `

                        <div>
                    <span style="margin-right: 20px;">${user.position}</span>
                             ${user.name} ${user.id == userId ? '(You)' : ''} 

                            <span class="trophy-icon">${user.is_winner ? '<i class="fas fa-trophy " title="Winner"></i>' : ''}</span>
                        </div>
                        
                        <div class="score-text }">
                            ${user.score ? Math.round(user.score) : 0}
                        </div>
                    `;
                        playersListContainer.appendChild(userDiv);
                    });

                    document.getElementById('user-count').innerHTML = totalUsers;

                    if (winnerAnnounced) {
                        // document.querySelectorAll('.score-text').forEach(scoreText => {
                        //     scoreText.classList.remove('blur');
                        // });
                        document.getElementById('players-list-loading-spinner').style.display = 'none';
                    }
                })
                .catch(error => console.error('Error fetching players with scores:', error));

        }

        function hideWinnersTabForParticipants() {
            const playerTabLink = document.getElementById('playerTab-tab');
            const playerTabPane = document.getElementById('playerTab');
            const chatTabLink = document.getElementById('chatTab-tab');
            const chatTabPane = document.getElementById('chatTab');

            // Just inactivate player tab & its content, and make chat tab active
            if (playerTabLink) {
                playerTabLink.classList.remove('active');
                playerTabLink.setAttribute('aria-selected', 'false');
            }
            if (playerTabPane) {
                playerTabPane.classList.remove('show', 'active');
            }

            if (chatTabLink) {
                chatTabLink.classList.add('active');
                chatTabLink.setAttribute('aria-selected', 'true');
            }
            if (chatTabPane) {
                chatTabPane.classList.add('show', 'active');
            }

        }

        function showWinnersTabForParticipants() {




            updatePlayersLeaderboard().then(() => {


                const playerTabLink = document.getElementById('playerTab-tab');
                const playerTabPane = document.getElementById('playerTab');
                const chatTabLink = document.getElementById('chatTab-tab');
                const chatTabPane = document.getElementById('chatTab');

                if (chatTabLink) {
                    chatTabLink.classList.remove('active');
                    chatTabLink.setAttribute('aria-selected', 'false');
                }
                if (chatTabPane) {
                    chatTabPane.classList.remove('show', 'active');
                }

                if (playerTabLink) {
                    playerTabLink.classList.add('active');
                    playerTabLink.setAttribute('aria-selected', 'true');
                }
                if (playerTabPane) {
                    playerTabPane.classList.add('show', 'active');
                }
                // //show a loading spinner
                // document.getElementById('players-list-loading-spinner').style.display = 'block';
                // document.getElementById('players-list-loading-spinner').style.display = 'none';
            });


        }



        // Toggle quiz mode
        function toggleQuiz(action) {
            //scroll to top
            //for safety, scroll to top of the page
            window.scrollTo(0, 0);


            const mainContainer = document.getElementById('mainContainer');


            // const videoContainer = document.querySelector('#videoContainer');

            quizMode = !quizMode;

            if (action == "show") {
                mainContainer.classList.add('quiz-mode');
                $quizOverlay.classList.add('active');
                $videoContainer.classList.add('question-activated');

            } else {
                mainContainer.classList.remove('quiz-mode');
                $quizOverlay.classList.remove('active');
                $videoContainer.classList.remove('question-activated');

            }

            uncheckAndEnableOptions();

        }

        function hideAllModals() {
            const modals = document.querySelectorAll('.modal:not(#winnerDialog)');
            modals.forEach(modal => {
                modal.style.display = 'none';
                //hide backdrop
                document.querySelector('.modal-backdrop').style.display = 'none';
            });
            //also hide all tab-panes
            const tabPanes = document.querySelectorAll('.tab-pane');
            tabPanes.forEach(tabPane => {
                tabPane.classList.remove('active');

            });
            //inactive all tabs
            const tabs = document.querySelectorAll('.nav-link');
            tabs.forEach(tab => {
                tab.classList.remove('active');
            });
        }


        function showRegisterModal() {
            var registerModal = new bootstrap.Modal(document.getElementById('registerModal'));
            registerModal.show();
        }

        function appendQuizQuestion(quiz) {
            //reset current answer status

            // console.log('Appending quiz question:', quiz);

            const quizSection = document.getElementById('quizSection');
            quizSection.innerHTML = `
            <div>
                <input type="hidden" id="quizId" value="${quiz.id}">
                    <div class="quiz-question">
                       <div class="text-center quiz-question-index me-1" style="font-size: 14px; font-weight: bold;">${quiz.index  } von ${quiz.totalQuizQuestions}.</div>
                       <div class="quiz-question-text" translate="no">${quiz.question}</div>
                    </div>
                    <div class="quiz-options row">
                        ${quiz.options.map((option, index) =>
                        `<div class="mb-3 quiz-option col-md-12 position-relative" id="quiz-option-${option.id}">  <div class="option-result-container " id="option-result-container-${option.id}" style=""> <div id="option-result-bar-${option.id}" class="option-result-bar"></div>  <span id="option-result-label-${option.id}" class="option-result-label"  style=""> 0% </span>  </div><input ${isEliminated ? 'disabled' : ''} type="radio" id="option${option.id}" name="option" value="${option.id}">  <label for="option${option.id}" class="quiz-option-label" translate="no">${numberToLetter(index)}. ${option.option_text}</label>  </div> `).join('')}
                    </div>
             </div>
            `;

            // Re-attach event listeners for auto-submit on radio change
            document.querySelectorAll('input[name="option"]').forEach(option => {
                option.addEventListener('change',
                    function() {
                        disableAllOptions();
                        captureResponseTimeMilliseconds();
                        checkAuthStatusAndShowRegisterModal();
                    }
                );
            });


        }

        function checkIfUserBlockedFromLiveShow() {
            fetch('{{ url('live-show/' . $liveShow->id . '/check-if-user-blocked-from-live-show') }}')
                .then(response => response.json())
                .then(data => {
                    if (data.blocked) {
                        // console.log('User blocked from live show:', data);
                        isUserBlockedFromChat = true;
                        alert('You have been blocked from live chat participation.');
                        //disable message input and send button
                        disableMessageInputAndSendButton();
                    } else {
                        isUserBlockedFromChat = false;
                        updateChatComposerState();


                    }
                })
                .catch(error => console.error('Error checking if user blocked from live show:', error));
        }

        function captureResponseTimeMilliseconds() {
            if (!countdownStartTime) {
                responseTimeMs = 1;

                return;
            }

            responseTimeMs = Math.max(1, Date.now() - countdownStartTime);
        }

        function disableAllOptions() {
            document.querySelectorAll('input[name="option"]').forEach(option => {
                option.disabled = true;
            });
        }


        function checkAuthStatusAndShowRegisterModal() {
            if (isLoggedIn == false) {
                showRegisterModal();
                return;
            }
        }

        // Quiz functionality
        function submitQuiz() {


            const selected = document.querySelector('input[name="option"]:checked') ?? -1;
            if (selected) {
                // console.log('Selected option:', selected.value);

                //disable all options to prevent multiple submissions
                document.querySelectorAll('input[name="option"]').forEach(option => {
                    option.disabled = true;
                });
                const option = selected.value ?? -1;
                // console.log('Submitting option:', option);

                fetch('{{ url('live-show/' . $liveShow->id . '/submit-quiz') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            option: option,
                            quiz_id: document.getElementById('quizId').value,
                            milliseconds_to_submit: responseTimeMs
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        // console.log('Quiz submission response:', data);
                        if (data.success) {
                            console.log('Quiz submitted successfully.');
                            // Show correct/incorrect feedback
                            //using some instead of forEach to break the loop when correct answer is found, converting nodelist to array


                            // if (data.is_correct) {
                            //     appendQuestionResponseStatus('success');
                            //     fireConfetti();
                            // } else {
                            //     appendQuestionResponseStatus('fail');
                            // }
                        } else {
                            //if authStatus
                            if (data.message && data.message == "unauthorized") {
                                //open register modal
                                var registerModal = new bootstrap.Modal(document.getElementById(
                                    'registerModal'));
                                registerModal.show();
                                uncheckAndEnableOptions();
                            } else {
                                const message = data.message || 'Failed to submit quiz. Please try again.';
                                console.warn(data.message || 'Failed to submit quiz. Please try again.');
                                appendQuestionResponseStatus('warning', message);
                            }
                        }
                    })
            } else {
                appendQuestionResponseStatus('warning');
            }
        }
        //auto submit the quiz when radio option is selected
        const quizOptions = document.querySelectorAll('input[name="quiz"]');
        quizOptions.forEach(option => {
            option.addEventListener('change',
                function() {
                    disableAllOptions();
                    captureResponseTimeMilliseconds();
                    checkAuthStatusAndShowRegisterModal();
                }
            );
        });

        //uncheck and enable options
        function uncheckAndEnableOptions() {
            document.querySelectorAll('input[name="option"]').forEach(option => {
                option.checked = false;
                option.disabled = false;
                option.parentElement.classList.remove('correct', 'incorrect');
            });
            //blur all the .quiz-option label
            document.querySelectorAll('.quiz-option label').forEach(label => {
                label.blur();
            });


            const dummy = document.createElement('input');
            dummy.style.position = 'fixed';
            dummy.style.opacity = '0';
            dummy.style.height = '0';

            // 2. Add it to the body, focus it, then kill it
            document.body.appendChild(dummy);
            dummy.click();
            dummy.focus();

            dummy.remove();
        }

        // Chat functionality
        function sendMessage() {

            if (isLoggedIn == false) {
                showRegisterModal();
                return;
            }
            if (!isChatEnabled) {
                alert('Unser Chat macht gerade kurz Pause – hier ist heute richtig was los!');
                return;
            }
            let message = $chatInput.value;

            message = sanitizeMessageText(message);


            if (!message || message.length == 0 || message == '') {
                alert('Ungültige Nachricht')
                return;

            }


            $chatInput.disabled = true;
            document.querySelector('#send-btn-overlay').disabled = true;




            addOverlayMessage('@You', message);
            $chatInput.value = '';
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

                        $chatInput.disabled = false;
                        $chatInput.focus();
                        document.querySelector('#send-btn-overlay').disabled = false;
                    },
                    error: function(xhr) {
                        // Handle error
                        console.error(xhr.responseText);
                        //if message ==  "Too Many Attempts."
                        if (xhr.responseJSON?.message == "Too Many Attempts.") {
                            alert('Du kannst nur 40 Nachrichten pro Minute senden 🙂');
                            $chatInput.disabled = false;
                            document.querySelector('#send-btn-overlay').disabled = false;

                        }
                        let errorMessage = xhr.responseJSON?.message || 'Failed to send message.';
                        if (errorMessage == "unauthorized") {
                            //open register modal
                            showRegisterModal();
                        }
                    }
                });
            }
        }

        function trimOverlayChatToMax(overlayChat) {
            if (!overlayChat) {
                return;
            }
            while (overlayChat.querySelectorAll('.chat-message-overlay').length > OVERLAY_CHAT_MAX_MESSAGES) {
                const oldest = overlayChat.querySelector('.chat-message-overlay');
                if (!oldest) {
                    break;
                }
                oldest.remove();
            }
        }

        function fetchMessages() {
            fetch('{{ url('/live-show/' . $liveShow->id . '/messages') }}')
                .then(response => response.json())
                .then(data => {

                    const overlayChat = document.getElementById('overlayChat');
                    if (!overlayChat) {
                        return;
                    }
                    overlayChat.innerHTML = ''; // Clear existing messages
                    const messages = (data.messages || []).slice(-OVERLAY_CHAT_MAX_MESSAGES);
                    messages.forEach(msg => {
                        // console.log(msg.user.id == userId ? 'your message retrived' : '');
                        if (msg.user !== null) {
                            addOverlayMessage('@' + msg.user.name, msg.message);
                        }
                    });
                })
                .catch(error => console.error('Error fetching messages:', error));
        }

        function addOverlayMessage(user, text) {
            const overlayChat = document.getElementById('overlayChat');
            if (!overlayChat) {
                return;
            }
            const emptyHint = overlayChat.querySelector('p.text-muted');
            if (emptyHint) {
                emptyHint.remove();
            }
            const messageDiv = document.createElement('div');
            messageDiv.className = 'chat-message-overlay';
            // messageDiv.innerHTML = `
        //     <div class="message-user">${user}</div>
        //     <div class="message-text">` + text + `</div>
        // `;
            const messageUser = document.createElement('div');
            messageUser.className = 'message-user';
            messageUser.textContent = user;
            messageDiv.appendChild(messageUser);

            const messageText = document.createElement('div');
            messageText.className = 'message-text';

            messageText.innerHTML = text;
            messageDiv.appendChild(messageText);




            overlayChat.appendChild(messageDiv);
            trimOverlayChatToMax(overlayChat);
            overlayChat.scrollTop = overlayChat.scrollHeight;

        }

        // Allow Enter key to send message
        $chatInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });




        @if ($liveShow->status == 'live')
            const randomInterval = Math.floor(Math.random() * 10000) + 15000;
            setInterval(
                function() {
                    if (!winnerAnnounced) {
                        updatePlayersLeaderboard();
                    }

                }, randomInterval);
        @endif

        // Prevent quiz overlay from closing when clicking inside
        $quizOverlay.addEventListener('click', function(e) {
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
            // const username = document.getElementById('registerUsername').value.trim();
            const email = document.getElementById('registerEmail').value.trim();
            const errorDiv = document.getElementById('registerError');
            errorDiv.style.display = 'none';
            errorDiv.innerHTML = '';


            if (!email) {
                errorDiv.textContent = 'Please add your email.';
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
                        // name: username,
                        email: email
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const username = data.user.user_name;
                        // console.log('username:', username);

                        var modal = bootstrap.Modal.getInstance(document.getElementById('registerModal'));
                        modal.hide();
                        // Optionally: update UI to reflect logged-in user
                        addOverlayMessage('@' + username, ' {{ __('de.chat.joined') }}');
                        replaceRegisterButtonWithUsername(username);

                        isLoggedIn = true;
                        userId = data.user.id;


                        enabledRegisterButton();

                        isEliminated = data.isEliminated == true ? true : false;


                        playerAsWinnerEventTrigger();
                        userBlockedFromLiveShowEventTrigger();
                        checkIfUserBlockedFromLiveShow();

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
                        class="px-0 py-2 nav-link d-flex flex-column align-items-center justify-content-center"
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
        let totalQuizQuestions = 0;
        let currentQuizQuestionIndex = 0;


        var channel = pusher.subscribe('live-show.{{ $liveShow->id }}');
        // System subscription event
        channel.bind('pusher:subscription_succeeded', function() {
            console.log('Quiz Subscribed successfully!');
        });
        // Your Laravel broadcast event (drop the dot)
        channel.bind('LiveShowQuizQuestionEvent', function(data) {
            console.log('Quiz Question:', data);


            timer = data.timer;

            let quizQuestion = data.quizQuestion;


            // add them to quizQuestion
            quizQuestion.index = data.quizQuestionIndex;


            showQuestionAndSetTimer(quizQuestion, timer);
            quizMode = false;
            toggleQuiz("show");

            //disable chatInput
            $chatInput.disabled = true;
            $sendBtnOverlay.disabled = true;


        });


        function showQuestionAndSetTimer(quiz, timer) {
            isCurrentAnswerCorrect = null; //reset current answer status
            $(".option-result-container").css("display", "none");

            console.log('Showing quiz question:', quiz, 'with timer:', timer);
            toggleQuiz("remove");
            appendQuizQuestion(quiz);
            startTimer(timer, evaluateAnswerWithTimeToSubmit);
            quizMode = false;
            setTimeout(() => {
                toggleQuiz("show");
            }, 100);
        }


        function startTimer(durationSeconds, onComplete) {
            document.querySelector('#quizTimer').style.display = "block";
            const circle = document.querySelector(".timer-progress");
            const text = document.querySelector(".timer-text");

            const radius = 60;
            const circumference = 2 * Math.PI * radius;
            circle.style.strokeDasharray = circumference;

            const durationMs = durationSeconds * 1000;
            countdownStartTime = Date.now();

            function updateTimer() {
                const now = Date.now();
                const elapsedMs = now - countdownStartTime;
                const remainingMs = Math.max(0, durationMs - elapsedMs);

                // Keep this global in ms if needed elsewhere
                currentCountdownMilliseconds = remainingMs;

                // If you still need seconds for UI/API:
                const remainingSeconds = Math.ceil(remainingMs / 1000);
                text.textContent = remainingSeconds;

                const progress = remainingMs / durationMs;
                const offset = -circumference + progress * circumference;
                circle.style.strokeDashoffset = offset;

                if (remainingMs <= 5000) {
                    circle.style.stroke = "#dc3545";
                    $videoContainer.style.display = "none";

                    //if not already playing, play the time-tick sound
                    if (!isTimeTickSoundPlaying) {
                        playSound('time-tick');
                        isTimeTickSoundPlaying = true;
                    }

                } else {
                    circle.style.stroke = "#007bff";
                    $videoContainer.style.display = "block";
                }

                // If you need elapsed seconds for submit:
                currentCountdownSeconds = elapsedMs / 1000;

                if (remainingMs <= 0) {
                    clearInterval(timerHandle);
                    isTimeTickSoundPlaying = false;
                    stopSound('time-tick');
                    setTimeout(() => {
                        if (onComplete) onComplete();
                    }, 1500);
                }
            }

            updateTimer();
            const timerHandle = setInterval(updateTimer, 50); // smoother updates
        }

        // Example: start a 10 second timer


        function evaluateAnswerWithTimeToSubmit() {
            document.querySelector('#quizTimer').style.display = "none";
            //disable all options
            document.querySelectorAll('input[name="option"]').forEach(option => {
                option.disabled = true;
            });

            // console.log('Evaluating elimination. isCurrentAnswerCorrect:', isCurrentAnswerCorrect);
            if (!isEliminated && isLoggedIn) {
                submitQuiz();
            }
            showVideoContainer();
        }


        // function evaluateElinimation() {
        //     document.querySelector('#quizTimer').style.display = "none";

        //     // console.log('Evaluating elimination. isCurrentAnswerCorrect:', isCurrentAnswerCorrect);
        //     updateUserPoints();

        //     if (!isEliminated && isLoggedIn) {
        //         if (isCurrentAnswerCorrect === true) {
        //             fireConfetti();
        //             appendQuestionResponseStatus('success');
        //         } else if (isCurrentAnswerCorrect === false) {
        //             appendQuestionResponseStatus('fail');

        //             isEliminated = true;
        //         } else {
        //             isEliminated = true;
        //             appendQuestionResponseStatus('warning');
        //         }
        //     } else {
        //         showVideoContainer();
        //     }

        //     // Reset for next question
        //     // isCurrentAnswerCorrect = null;
        //     //uncheckAndEnableOptions();
        // }

        function updateUserPoints() {
            // console.log('Updating user points...');
            fetch('{{ url('/live-show/get-my-points/' . $liveShow->id) }}')
                .then(response => response.json())
                .then(data => {
                    // console.log('User points response:', data);
                    document.getElementById('user-points').innerHTML = data.points;
                });
        }


        function appendQuestionResponseStatus(type, message = '') {
            const evaluationDiv = document.getElementById('evaluationStatus');
            let alertClass = 'alert-info';

            let messageText = message || ``;

            if (type === 'success') {
                alertClass = 'text-success';
                message = `<i class="fas fa-check-circle me-2"></i>Hurray!<br>{{ __('de.quiz.correct') }}`;
            } else if (type === 'fail') {
                alertClass = 'text-danger';
                message = `<i class="fas fa-times-circle me-2"></i>Oops!<br>{{ __('de.quiz.wrong') }}`;
                // updateEliminatedStatus();

            } else {
                alertClass = 'text-warning';
                messageText = `<i class="fas fa-exclamation-circle me-2"></i> ${messageText} `;
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

            }, 3000);
        }

        function showVideoContainer() {
            $videoContainer.style.display = "block";
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
                particleCount: 100,
                spread: 100,
                origin: {
                    y: 0.6
                },
                zIndex: 100000 // must be > 99999 for .main-container.quiz-mode
            });

        }

        function fireWorksConfetti() {
            var duration = 15 * 1000;
            var animationEnd = Date.now() + duration;
            var defaults = {
                startVelocity: 30,
                spread: 360,
                ticks: 60,
                zIndex: 100000
            };

            function randomInRange(min, max) {
                return Math.random() * (max - min) + min;
            }

            var fireworkStartTime = Date.now();
            var interval = setInterval(function() {
                var timeLeft = animationEnd - Date.now();

                // Stop the interval after 5 seconds regardless of animationEnd
                if (Date.now() - fireworkStartTime >= 10000) {
                    clearInterval(interval);
                    return;
                }

                if (timeLeft <= 0) {
                    clearInterval(interval);
                    return;
                }

                var particleCount = 50 * (timeLeft / duration);
                // since particles fall down, start a bit higher than random
                confetti({
                    ...defaults,
                    particleCount,
                    origin: {
                        x: randomInRange(0.1, 0.3),
                        y: Math.random() - 0.2
                    }
                });
                confetti({
                    ...defaults,
                    particleCount,
                    origin: {
                        x: randomInRange(0.7, 0.9),
                        y: Math.random() - 0.2
                    }
                });
            }, 250);

        }
    </script>


    <script>
        var channelRemoveQuestion = pusher.subscribe('live-show.{{ $liveShow->id }}');

        // System subscription event
        channelRemoveQuestion.bind('pusher:subscription_succeeded', function() {
            console.log('Quiz Removed successfully!');
        });

        // Your Laravel broadcast event (drop the dot)
        channelRemoveQuestion.bind('RemoveLiveShowQuizQuestionEvent', function(data) {
            console.log('Remove Quiz Question:', data);
            toggleQuiz("remove");
            $chatInput.disabled = false;
            $sendBtnOverlay.disabled = false;
        });



        function playerAsWinnerEventTrigger() {
            var channelShowWinner = pusher.subscribe(
                // 'live-show-winner-user.' + '{{ $liveShow->id }}' + '.' + userId
                'live-show-winner-user.{{ $liveShow->id }}.' + userId
            );
            // System subscription event
            channelShowWinner.bind('pusher:subscription_succeeded', function() {
                console.log('Winner Subscribed successfully!', channelShowWinner);
            });
            // Your Laravel broadcast event (drop the dot)
            channelShowWinner.bind('ShowPlayerAsWinnerEvent', function(data) {


                console.log('Winner Event:', data);

                // AJAX request to fetch prize money for this user
                toggleQuiz("remove");

                if (userId && isLoggedIn) {
                    fetch("{{ url('live-show/' . $liveShow->id . '/user-prize') }}?user_id=" + userId, {
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            }
                        })
                        .then(response => response.json())
                        .then(prizeData => {

                            console.log('Prize data:', prizeData);
                            if (prizeData.success && prizeData.prize !== undefined && prizeData.prize !=
                                'n/a' && prizeData.is_winner == true) {
                                fireWorksConfetti();
                                playSound('winner');
                                Swal.fire({
                                    title: "{{ __('de.winner.title') }}",
                                    html: `
                                        <i class="mb-3 fas fa-trophy fa-3x text-warning"></i>
                                        <h3 class="mb-2" style="color:#ff5f00;">{{ __('de.winner.title') }}</h3>
                                        <p class="mb-2" style="font-size:1.1rem;">{{ __('de.winner.selected') }}</p>
                                        <p class="mb-2" style="font-size:1.1rem;">{{ __('de.winner.prize') }}</p>
                                        <div class="text-center mb-3" style="font-size: 1.3rem; color:rgba(229, 84, 0, 1)">
                                            ${prizeData.prize}
                                        </div>
                                    `,
                                    icon: 'success',
                                    confirmButtonText: '<i class="fas fa-check me-2"></i>{{ __('de.profile.close') }}',
                                    width: 350,
                                    customClass: {
                                        popup: 'swal2-dialog-custom-winner',
                                        title: 'swal2-title-custom-winner'
                                    }
                                });
                                winnerAnnounced = 1;
                                showWinnersTabForParticipants();


                            }


                        })
                        .catch((err) => {
                            console.error('Error fetching prize money:', err);
                        });
                }

            });

        }

        function showWinnerDialogDiv() {
            // Show the winner dialog
            document.querySelector('#winnerDialog').style.display = 'block';
            //hide question
            toggleQuiz("remove");

        }

        function userBlockedFromLiveShowEventTrigger() {
            var channelUserBlockFromLiveShow = pusher.subscribe('user-block-from-live-show.{{ $liveShow->id }}');
            channelUserBlockFromLiveShow.bind('pusher:subscription_succeeded', function() {
                console.log('User block from live show channel subscribed successfully!');
            });
            channelUserBlockFromLiveShow.bind('UserBlockFromLiveShowEvent', function(data) {


                if (data.userId == userId) {
                    console.log('User block from live show event received:', data);
                    if (data.isBlocked) {
                        isUserBlockedFromChat = true;
                        alert('Du wurdest für die Chat Teilnahme blockiert.');
                        disableMessageInputAndSendButton();
                    } else {
                        isUserBlockedFromChat = false;
                        alert('Sie wurden aus der Live-Chat-Teilnahme entblockt.');
                        enableMessageInputAndSendButton();
                    }
                }
            });
        }

        playerAsWinnerEventTrigger()

        @if (Auth::guard('web')->check())
            userBlockedFromLiveShowEventTrigger()
        @endif
    </script>




    <script>
        document.getElementById("playButton").addEventListener("click", () => {
            console.log('Tap to play clicked');
            document.getElementById('playButtonOverlay').style.display = 'none';


            onLoadGameShow();

        });

        function onLoadGameShow() {
            if (isLoggedIn == false && window.location.search.indexOf('preview=true') === -1) {
                showRegisterModal();
            }

            // if (vid.src != '') {
            //     vid.play();
            //     vid.muted = false;
            // }



            if (zegoLiveRoot) {
                const video = zegoLiveRoot.querySelector('video');
                if (video) {
                    video.muted = false;
                    video.volume = 1.0;
                    video.play().catch(() => {});

                    console.log(' zegoLiveRoot video:', video);

                }
            }
        }
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






        function disableMessageInputAndSendButton() {
            isUserBlockedFromChat = true;
            updateChatComposerState();
        }

        function enableMessageInputAndSendButton() {
            isUserBlockedFromChat = false;
            updateChatComposerState();
        }

        function applyChatStatus(chatEnabled, showMessage = false) {
            isChatEnabled = !!chatEnabled;
            updateChatComposerState();
        }

        function updateChatComposerState() {
            const shouldDisable = (!isChatEnabled) || isUserBlockedFromChat;
            $chatInput.disabled = shouldDisable;
            $sendBtnOverlay.disabled = shouldDisable;
            document.querySelector('#chatInput').style.opacity = shouldDisable ? '0.5' : '1';
            document.querySelector('#send-btn-overlay').style.opacity = shouldDisable ? '0.5' : '1';
            document.querySelector('#chatInput').style.cursor = shouldDisable ? 'not-allowed' : 'pointer';
            document.querySelector('#send-btn-overlay').style.cursor = shouldDisable ? 'not-allowed' : 'pointer';
            document.querySelector('#chatInput').style.pointerEvents = shouldDisable ? 'none' : 'auto';
            document.querySelector('#send-btn-overlay').style.pointerEvents = shouldDisable ? 'none' : 'auto';
            document.getElementById('chatDisabledMsg').style.display = shouldDisable ? 'block' : 'none';
        }






        var channelResetChat = pusher.subscribe('live-show.{{ $liveShow->id }}');
        channelResetChat.bind('pusher:subscription_succeeded', function() {
            console.log('Reset chat channel subscribed successfully!');
        });
        channelResetChat.bind('ResetChatEvent', function(data) {
            console.log('Reset chat event received:', data);

            if ($overlayChat) {
                $overlayChat.innerHTML = '<p class="text-muted">No messages yet.</p>';
            }
        });



        /* Heart reactions (TikTok/Instagram style) */
        const _escapeDiv = document.createElement('div');

        function spawnHeartReaction(userName) {
            let colorsArray = [
                'heart-color-red',
                'heart-color-orange',
                'heart-color-yellow',
                'heart-color-green',
                'heart-color-blue',
                'heart-color-indigo',
                'heart-color-violet'
            ];

            var overlay = document.getElementById('heartReactionsOverlay');
            if (!overlay) return;
            var leftPct = 15 + Math.random() * 70;
            var el = document.createElement('div');
            el.className = 'heart-reaction-float';
            el.style.left = leftPct + '%';
            el.innerHTML = '<span class="heart-icon ' + colorsArray[Math.floor(Math.random() * colorsArray.length)] +
                '"><i class="fas fa-heart"></i></span><span class="heart-username">' +
                escapeHtml(userName || 'Someone') + '</span>';
            overlay.appendChild(el);
            // el.addEventListener('animationend', function() {
            //     if (el.parentNode) el.parentNode.removeChild(el);
            // });
        }

        function escapeHtml(text) {
            _escapeDiv.textContent = text;
            return _escapeDiv.innerHTML;
        }

        document.getElementById('heartReactionBtn')?.addEventListener('click', function() {
            if (!isLoggedIn) {

                return;
            }
            fetch('{{ url('live-show/' . $liveShow->id . '/heart-reaction') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                })
                .then(function(r) {
                    if (r.status === 401) {
                        showRegisterModal();
                        return;
                    }
                    return r.json();
                })
                .then(function(data) {
                    if (data && data.success) {
                        /* reaction sent; will appear via Pusher */
                    }
                })
                .catch(function() {});
        });
        var channelHearts = pusher.subscribe('live-show.{{ $liveShow->id }}');
        channelHearts.bind('pusher:subscription_succeeded', function() {
            // console.log('Heart reactions channel subscribed');
        });
        channelHearts.bind('HeartReactionEvent', function(data) {
            spawnHeartReaction(data.user_name);
        });

        /* Gallery image/video overlay */
    </script>

    <script>
        /* Gallery image/video overlay (Pusher + DB stream visibility via initial hydration) */
        const galleryStreamInitial = @json($galleryStreamInitial ?? ['showing' => false, 'state' => null]);

        function galleryPlaybackOffsetSeconds(playbackStartedAtIso, durationSeconds) {
            if (!playbackStartedAtIso) {
                return 0;
            }
            const startMs = new Date(playbackStartedAtIso).getTime();
            if (Number.isNaN(startMs)) {
                return 0;
            }
            let elapsed = (Date.now() - startMs) / 1000;
            if (elapsed < 0) {
                elapsed = 0;
            }
            if (durationSeconds != null && durationSeconds > 0) {
                elapsed = Math.min(elapsed, durationSeconds);
            }
            return elapsed;
        }

        /**
         * @param {object} opts
         * @param {string} opts.type — "image" | "video" (from Pusher) or use opts.media_type from API/state
         * @param {string} opts.src — media URL
         * @param {string|null} [opts.playback_started_at] — ISO8601, video sync (late joiners / restarts)
         * @param {number|null} [opts.video_duration_seconds] — clamp seek
         */
        function showGalleryOverlay(opts) {
            const type = opts.type || opts.media_type;
            const src = opts.src || opts.url;
            const playbackAt = opts.playback_started_at ?? null;
            const durationSec = opts.video_duration_seconds != null ? Number(opts.video_duration_seconds) : null;
            vid.style.display = "none";
            img.style.display = "none";



            if (type === "image") {

                img.src = src;
                img.style.display = "block";
                vid.pause();
                vid.src = "";
            } else if (type === "video") {

                const seekTo = galleryPlaybackOffsetSeconds(playbackAt, durationSec);
                vid.src = src;
                vid.style.display = "block";
                vid.poster = opts.thumbnail_url ?? "";

                img.src = "";

                const applySeekAndPlay = function() {
                    if (seekTo > 0 && vid.duration && !Number.isNaN(vid.duration)) {
                        vid.currentTime = Math.min(seekTo, Math.max(0, vid.duration - 0.05));
                    } else if (seekTo > 0) {
                        vid.currentTime = seekTo;
                    } else {
                        vid.currentTime = 0;
                        vid.play();
                    }
                    vid.muted = true;
                    vid.play().catch(function() {
                        console.error('Error playing video:', e);
                    });

                    setTimeout(() => {
                        vid.click();
                        vid.muted = false;
                        vid.play().catch(function(e) {
                            console.error('Error playing video:', e);
                        });
                    }, 300);

                };

                vid.addEventListener('loadedmetadata', applySeekAndPlay, {
                    once: true
                });
                vid.load();

                vid.addEventListener('error', function onErr() {
                    vid.removeEventListener('error', onErr);
                    console.warn('Gallery overlay video failed to load');
                });

                //mute = false , if not then show unmute icon via function
                vid.muted = false;

                if (vid.muted) {
                    appendUnmuteIcon(vid);
                }
            }
            overlay.style.display = "flex";
        }

        // If the gallery overlay video element is shown, check if it is paused and play it
        // This listener ensures that when the overlay is made visible, the video auto-plays if paused
        const galleryOverlay = document.getElementById('galleryOverlayModal');
        const galleryVideo = document.getElementById('galleryOverlayVideo');
        if (galleryOverlay && galleryVideo) {
            console.log('Gallery overlay and video found, setting up observer');
            // Listen for overlay being shown
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (
                        mutation.attributeName === "style" &&
                        galleryOverlay.style.display === "flex" &&
                        galleryVideo.style.display === "block"
                    ) {
                        // Video is visible in overlay, check/play if paused
                        setTimeout(() => {
                            if (galleryVideo.paused) {
                                galleryVideo.play().catch(() => {}); // Attempt play, ignore errors
                            }
                        }, 100); // Small delay to ensure rendering
                    }
                });
            });
            observer.observe(galleryOverlay, {
                attributes: true,
                attributeFilter: ['style']
            });
        }

        function appendUnmuteIcon(videoEl) {
            // Keep only one icon at a time
            let existing = videoEl.parentNode.querySelector('.unmute-center-icon');
            if (existing) existing.remove();

            const btn = document.createElement('div');
            btn.className = 'unmute-center-icon';
            btn.innerHTML = `
                <div style="
                    position: absolute;
                    left: 50%;
                    top: 50%;
                    transform: translate(-50%, -50%);
                    z-index: 10000;
                    background: rgba(0,0,0,0.65);
                    border-radius: 100px;
                    padding: 18px 24px;
                    cursor: pointer;
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    box-shadow: 0 2px 24px 0 rgba(0,0,0,0.38);
                ">
                    <i class="fas fa-volume-mute" style="font-size:2.7rem;color:white;"></i>
                    <div style="color:white;font-size:1rem;font-weight:500;margin-top:7px;">Unmute</div>
                </div>
            `;
            // Position must be relative/absolute container
            let relParent = videoEl.parentElement;
            if (getComputedStyle(relParent).position === 'static') {
                relParent.style.position = 'relative';
            }
            btn.style.position = 'absolute';
            btn.style.left = '0';
            btn.style.top = '0';
            btn.style.width = '100%';
            btn.style.height = '100%';
            btn.style.pointerEvents = 'auto';
            btn.style.display = 'flex';
            btn.style.alignItems = 'center';
            btn.style.justifyContent = 'center';

            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                videoEl.muted = false;
                // Optionally attempt to play in case browser requires "gesture"
                videoEl.play().catch(() => {});
                btn.remove();
            });

            btn.classList.add('unmute-center-icon');
            relParent.appendChild(btn);
        }

        function hideGalleryOverlay() {
            const overlay = document.getElementById('galleryOverlayModal');
            const img = document.getElementById('galleryOverlayImage');
            const vid = document.getElementById('galleryOverlayVideo');
            overlay.style.display = "none";
            img.src = "";
            vid.pause();
            vid.src = "";
        }

        function hydrateGalleryOverlayFromServer() {
            if (!galleryStreamInitial || !galleryStreamInitial.showing || !galleryStreamInitial.state) {
                hideGalleryOverlay();
                return;
            }
            const s = galleryStreamInitial.state;
            showGalleryOverlay({
                media_type: s.media_type,
                src: s.url,
                playback_started_at: s.playback_started_at,
                video_duration_seconds: s.video_duration_seconds
            });
        }


        //hide the gallery video when it is completed after 1 second of finish playing

        if (galleryVideo) {
            galleryVideo.addEventListener('ended', function() {
                setTimeout(() => {
                    hideGalleryOverlay();
                }, 1000);
            });
        }

        //if request has ?preview=true, then don't show playButtonOverlay
        if (window.location.search.includes('preview=true')) {
            document.getElementById('playButtonOverlay').style.display = 'none';
        }

        //Utility functions

        // Function to convert a number to ordinal with superscript (e.g. 1<sup>st</sup>, 2<sup>nd</sup>)
        function toOrdinalSup(num) {
            num = parseInt(num, 10);
            if (isNaN(num)) return '';
            let j = num % 10,
                k = num % 100;

            let suffix = "th";
            if (j == 1 && k != 11) {
                suffix = "st";
            } else if (j == 2 && k != 12) {
                suffix = "nd";
            } else if (j == 3 && k != 13) {
                suffix = "rd";
            }

            return num + '<sup>' + suffix + '</sup>';
        }
        // Function to convert number (0,1,2,3) to corresponding letter (A,B,C,D)
        function numberToLetter(index) {
            const letters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S',
                'T', 'U', 'V', 'W', 'X', 'Y', 'Z'
            ];
            return letters[index];
        }



        document.addEventListener('DOMContentLoaded', function() {
            if ("{{ $liveShow->id }}" == 1004 && isLoggedIn) {
                autoShowQuizQuestions();
            }
        });

        // ── Single-Tab Restriction (WhatsApp-style) ──
        (function() {
            var liveShowTabKey = 'live_show_active_tab_{{ $liveShow->id }}';
            var tabId = Date.now() + '-' + Math.random().toString(36).substr(2, 9);
            var tabChannel = (typeof BroadcastChannel !== 'undefined') ?
                new BroadcastChannel(liveShowTabKey) :
                null;

            var overlay = document.getElementById('inactiveTabOverlay');
            var useHereBtn = document.getElementById('useHereBtn');

            function claimActiveTab() {
                localStorage.setItem(liveShowTabKey, tabId);
                if (tabChannel) {
                    tabChannel.postMessage({
                        type: 'TAB_CLAIMED',
                        tabId: tabId
                    });
                }
                overlay.style.display = 'none';
                document.getElementById('inactiveTabContent').classList.add('d-none');
                if (typeof pusher !== 'undefined' && pusher.connection &&
                    pusher.connection.state !== 'connected') {
                    pusher.connect();
                }
            }

            function showInactiveOverlay() {
                overlay.style.display = 'flex';

                //after 1 second, show the inactive tab content
                setTimeout(function() {
                    document.getElementById('inactiveTabContent').classList.remove('d-none');
                }, 1000);
                if (typeof pusher !== 'undefined') {
                    pusher.disconnect();
                }
            }

            if (tabChannel) {
                tabChannel.onmessage = function(event) {
                    if (event.data.type === 'TAB_CLAIMED' && event.data.tabId !== tabId) {
                        showInactiveOverlay();
                    }
                    if (event.data.type === 'TAB_RELEASED') {
                        claimActiveTab();
                    }
                };
            }

            window.addEventListener('storage', function(e) {
                if (e.key === liveShowTabKey && e.newValue !== tabId) {
                    if (e.newValue === null) {
                        claimActiveTab();
                    } else {
                        showInactiveOverlay();
                    }
                }
            });

            useHereBtn.addEventListener('click', function() {
                claimActiveTab();
            });

            window.addEventListener('beforeunload', function() {
                if (localStorage.getItem(liveShowTabKey) === tabId) {
                    localStorage.removeItem(liveShowTabKey);
                    if (tabChannel) {
                        tabChannel.postMessage({
                            type: 'TAB_RELEASED',
                            tabId: tabId
                        });
                    }
                }
            });

            claimActiveTab();
        })();


        // Alert user if trying to close or reload the tab
        window.addEventListener('beforeunload', function(e) {

            if (window.location.search.includes('preview=true')) {
                return true;
            }
            if (isLoggedIn) {


                // var confirmationMessage =
                //     'Are you sure you want to leave or reload this live show? You may lose your progress or be disconnected.';
                // (e || window.event).returnValue = confirmationMessage; // For legacy browsers
                // return confirmationMessage; // For modern browsers
            }
        });

        function sanitizeMessageText(input) {
            if (input == null) return '';

            let text = String(input).trim();

            // Special handling: Remove <style>body { display: none!important; }</style> (ignore case, whitespace tolerant)
            text = text.replace(/<style[^>]*?>\s*body\s*\{\s*display\s*:\s*none\s*!important;\s*\}\s*<\/style>/gi, '');

            // strip_tags() — remove anything that looks like an HTML/PHP tag
            text = text.replace(/<\/?[^>]+(>|$)/g, '');

            // htmlspecialchars() — replace &, <, >, ", '
            text = text
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");

            return text;
        }
    </script>
    <script>
        var channelMediaPlayed = pusher.subscribe('live-show-media-played.' + {{ $liveShow->id }});
        //success 
        channelMediaPlayed.bind('pusher:subscription_succeeded', function() {
            console.log('LiveShowMediaPlayed channel subscribed successfully!');
        });
        channelMediaPlayed.bind('LiveShowMediaPlayed', function(data) {
            // document.getElementById('galleryOverlayModal').style.display = 'none';
            console.log('LiveShowMediaPlayed event received:', data);
            // document.getElementById('liveShowBottomFixed').style.display = 'none';
            toggleToBeHiddenOnMediaPlay('none');
        });

        var channelMediaHidden = pusher.subscribe('live-show-media-hidden.' + {{ $liveShow->id }});
        channelMediaHidden.bind('pusher:subscription_succeeded', function() {
            console.log('LiveShowMediaHidden channel subscribed successfully!');
        });
        channelMediaHidden.bind('LiveShowMediaHidden', function(data) {
            console.log('LiveShowMediaHidden event received:', data);
            //hide overlay
            document.getElementById('liveShowBottomFixed').style.display = 'block';
            toggleToBeHiddenOnMediaPlay('block');
        });
    </script>

    @if (request()->boolean('debug_bot'))
        <script src="{{ url('js/live-show-quiz-debug-bot.js?v=' . time()) }}"></script>
    @endif

    {{-- German web-push opt-in banner + subscription logic. --}}
    {{-- @include('partials.push-notification') --}}


</body>

<a style="display: none;" href="https://www.flaticon.com/free-icons/gold-cup" title="gold cup icons">Gold cup icons
    created by Md Tanvirul Haque - Flaticon</a>
<a style="display: none;" href="https://www.flaticon.com/free-icons/silver-cup" title="silver cup icons">Silver cup
    icons created by Md Tanvirul Haque - Flaticon</a>
<a style="display: none;" href="https://www.flaticon.com/free-icons/3rd-place" title="3rd place icons">3rd place
    icons created by Md Tanvirul Haque - Flaticon</a>

</html>
