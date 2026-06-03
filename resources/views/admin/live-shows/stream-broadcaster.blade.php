<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <style>
        /* body {
            margin: 0 auto;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #f0f2f5;
        }

        #mobile-frame {
            width: 100%;
            max-width: 390px;

            height: 844px;

            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            background-color: #ffffff;
            border-radius: 24px;
            border: 4px solid #000000;
        } */

        #root,
        #root>div,
        #root video {
            width: 100vw !important;
            height: 100vh !important;
            max-width: 100%;
        }

        #root video {
            object-fit: contain !important;
            /* or contain, depending on what you want */
        }


        div:has(> #root video) {}

        #root [id^="zg-rtc-player"],
        [id*="zg-rtc-player"] {
            transform: scaleX(-1) !important;


        }


        #zego_left_notify_wrapper,
        #zego_right_notify_wrapper {
            display: none !important;
        }

        #page-qr-wrapper {
            width: 120px;
            height: auto;
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 99999;
            background: rgba(255, 255, 255, 0.97);
            padding: 8px 16px 12px 16px;
            border-radius: 10px;
        }

        #page-qr-code {
            width: 110px;
            height: 110px;
        }

        #page-qr-code canvas {
            width: 100%;
            height: 100%;
        }

        #page-qr-code img {
            width: 100%;
            height: 100%;
        }

        @media (max-width: 768px) {
            #page-qr-wrapper {
                display: none !important;
            }
        }

        .zg_autoplay_wrapper {
            position: absolute !important;
            top: 50px !important;

        }

        #overlay-controls {
            position: fixed;
            bottom: 10%;
            right: 20px;
            z-index: 99999;
            background: rgba(20, 20, 20, 0.92);
            color: #fff;
            padding: 14px 14px 12px 14px;
            border-radius: 10px;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", system-ui, sans-serif;
            width: 320px;
            box-shadow: 0 8px 28px rgba(0, 0, 0, 0.45);
            font-size: 13px;
        }

        #overlay-controls h4 {
            margin: 0 0 10px 0;
            font-size: 14px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        #overlay-controls h4::before {
            content: "";
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #ef4444;
        }

        #overlay-controls input[type="text"],
        #overlay-controls select {
            width: 100%;
            padding: 7px 9px;
            border-radius: 6px;
            border: 1px solid rgba(255, 255, 255, 0.15);
            background: rgba(255, 255, 255, 0.08);
            color: #fff;
            box-sizing: border-box;
            font-size: 12px;
            outline: none;
        }

        #overlay-controls input[type="text"]::placeholder {
            color: rgba(255, 255, 255, 0.45);
        }

        #overlay-controls .row {
            display: flex;
            gap: 6px;
            margin-top: 6px;
        }

        #overlay-controls button {
            flex: 1;
            padding: 8px 10px;
            border-radius: 6px;
            border: none;
            color: #fff;
            cursor: pointer;
            font-weight: 600;
            font-size: 12px;
            transition: filter 0.15s ease;
        }

        #overlay-controls button:hover {
            filter: brightness(1.1);
        }

        #overlay-controls button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        #overlay-play {
            background: #16a34a;
        }

        #overlay-stop {
            background: #dc2626;
        }

        #overlay-status {
            margin-top: 8px;
            font-size: 11px;
            color: rgba(255, 255, 255, 0.65);
            min-height: 14px;
        }

        #overlay-toggle {
            position: fixed;
            bottom: 20%;
            right: 20px;
            z-index: 99998;
            background: rgba(20, 20, 20, 1);
            color: #fff;
            border: none;
            padding: 8px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-family: system-ui, sans-serif;
            font-size: 12px;
            display: none;
        }

        #bgm-toggle {
            position: fixed;
            bottom: 28%;
            right: 20px;
            z-index: 99998;
            background: rgba(20, 20, 20, 0.95);
            color: #fff;
            border: 1px solid rgba(255, 255, 255, 0.12);
            padding: 8px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-family: system-ui, sans-serif;
            font-size: 12px;
            font-weight: 600;
            min-width: 108px;
            transition: background 0.15s ease, border-color 0.15s ease;
        }

        #bgm-toggle:hover {
            filter: brightness(1.08);
        }

        #bgm-toggle:disabled {
            opacity: 0.55;
            cursor: not-allowed;
        }

        #bgm-toggle[aria-pressed="true"] {
            background: rgba(22, 163, 74, 0.92);
            border-color: rgba(134, 239, 172, 0.35);
        }

        #bgm-toggle[aria-pressed="false"] {
            background: rgba(20, 20, 20, 0.95);
            border-color: rgba(255, 255, 255, 0.12);
        }

        @media (max-width: 768px) {
            #overlay-controls {
                width: calc(100vw - 40px);
                right: 20px;
                left: 20px;
            }
        }

        #overlay-media-list {
            margin-top: 6px;
            max-height: 240px;
            overflow-y: auto;
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 6px;
            background: rgba(0, 0, 0, 0.25);
        }

        #overlay-media-list::-webkit-scrollbar {
            width: 8px;
        }

        #overlay-media-list::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.18);
            border-radius: 4px;
        }

        .overlay-media-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 6px 8px;
            cursor: pointer;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            transition: background 0.15s ease;
        }

        .overlay-media-item:last-child {
            border-bottom: none;
        }

        .overlay-media-item:hover {
            background: rgba(255, 255, 255, 0.06);
        }

        .overlay-media-item.selected {
            background: rgba(34, 197, 94, 0.18);
            border-left: 3px solid #22c55e;
            padding-left: 5px;
        }

        .overlay-media-thumb {
            width: 56px;
            height: 36px;
            object-fit: cover;
            border-radius: 4px;
            background: #111;
            flex: 0 0 auto;
        }

        .overlay-media-thumb-fallback {
            width: 56px;
            height: 36px;
            border-radius: 4px;
            background: linear-gradient(135deg, #1f2937, #374151);
            display: flex;
            align-items: center;
            justify-content: center;
            color: rgba(255, 255, 255, 0.5);
            font-size: 16px;
            flex: 0 0 auto;
        }

        .overlay-media-meta {
            flex: 1;
            min-width: 0;
        }

        .overlay-media-meta .title {
            font-size: 12px;
            font-weight: 500;
            color: #fff;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .overlay-media-meta .sub {
            font-size: 10px;
            color: rgba(255, 255, 255, 0.55);
            margin-top: 2px;
        }

        .overlay-media-check {
            width: 18px;
            height: 18px;
            border-radius: 4px;
            border: 1.5px solid rgba(255, 255, 255, 0.35);
            flex: 0 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            color: #fff;
            transition: all 0.15s ease;
        }

        .overlay-media-item.selected .overlay-media-check {
            background: #22c55e;
            border-color: #22c55e;
        }

        .overlay-media-item.selected .overlay-media-check::after {
            content: "\2713";
        }

        #overlay-media-empty,
        #overlay-media-loading {
            padding: 16px 12px;
            text-align: center;
            font-size: 12px;
            color: rgba(255, 255, 255, 0.6);
        }

        #overlay-media-refresh {
            background: rgba(255, 255, 255, 0.08);
            color: #fff;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 11px;
            cursor: pointer;
            margin-left: auto;
        }

        #overlay-media-refresh:hover {
            background: rgba(255, 255, 255, 0.15);
        }

        .overlay-section-label {
            display: flex;
            align-items: center;
            font-size: 11px;
            color: rgba(255, 255, 255, 0.55);
            margin: 8px 0 2px 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        @media (max-width: 768px) {
            #root video {
                width: 100vw !important;
                height: 100vh !important;
            }

            #overlay-toggle {
                bottom: 80px;
                right: 10px;
            }

            #bgm-toggle {
                bottom: 130px;
                right: 10px;
            }

            #overlay-controls {
                bottom: 10px;
                max-height: 70vh;
                overflow-y: auto;
            }
        }

        /* ─── "Broadcaster opened elsewhere" overlay ─────────────────
           Shown when this tab has been superseded by a newer broadcaster
           tab (same browser, another browser, or another device). It
           covers the entire page so the host cannot accidentally keep
           streaming from the old session. */
        #broadcasterInactiveOverlay {
            position: fixed;
            inset: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(10, 10, 10, 0.96);
            color: #fff;
            z-index: 100000;
            display: none;
            align-items: center;
            justify-content: center;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", system-ui, sans-serif;
            padding: 20px;
        }

        #broadcasterInactiveOverlay .card {
            max-width: 460px;
            text-align: center;
            background: rgba(30, 30, 30, 0.95);
            border-radius: 14px;
            padding: 32px 26px;
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.6);
        }

        #broadcasterInactiveOverlay .icon {
            font-size: 44px;
            color: #f87171;
            margin-bottom: 14px;
        }

        #broadcasterInactiveOverlay h3 {
            font-size: 20px;
            margin: 0 0 10px 0;
            font-weight: 600;
        }

        #broadcasterInactiveOverlay p {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.78);
            line-height: 1.5;
            margin: 0 0 22px 0;
        }

        #broadcasterInactiveOverlay button {
            background: linear-gradient(90deg, #4ade80 0%, #3b82f6 100%);
            color: #fff;
            border: none;
            padding: 12px 22px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: filter 0.15s ease;
        }

        #broadcasterInactiveOverlay button:hover {
            filter: brightness(1.1);
        }

        /* Shown on a newly opened tab when another tab already owns the
           broadcaster. The user must confirm before we claim and start Zego. */
        #broadcasterTakeoverPrompt {
            position: fixed;
            inset: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(10, 10, 10, 0.96);
            color: #fff;
            z-index: 100001;
            display: none;
            align-items: center;
            justify-content: center;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", system-ui, sans-serif;
            padding: 20px;
        }

        #broadcasterTakeoverPrompt .card {
            max-width: 480px;
            text-align: center;
            background: rgba(30, 30, 30, 0.95);
            border-radius: 14px;
            padding: 32px 26px;
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.6);
        }

        #broadcasterTakeoverPrompt .icon {
            font-size: 44px;
            color: #fbbf24;
            margin-bottom: 14px;
        }

        #broadcasterTakeoverPrompt h3 {
            font-size: 20px;
            margin: 0 0 10px 0;
            font-weight: 600;
        }

        #broadcasterTakeoverPrompt p {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.78);
            line-height: 1.5;
            margin: 0 0 22px 0;
        }

        #broadcasterTakeoverPrompt .actions {
            display: flex;
            gap: 10px;
            justify-content: center;
            flex-wrap: wrap;
        }

        #broadcasterTakeoverPrompt button {
            border: none;
            padding: 12px 22px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: filter 0.15s ease;
        }

        #broadcasterTakeoverPrompt button:hover {
            filter: brightness(1.1);
        }

        #broadcasterTakeoverContinueBtn {
            background: linear-gradient(90deg, #4ade80 0%, #3b82f6 100%);
            color: #fff;
        }

        #broadcasterTakeoverCancelBtn {
            background: rgba(255, 255, 255, 0.12);
            color: #fff;
        }
    </style>
</head>


<body id="mobile-frame">
    <div id="page-qr-wrapper">
        <div id="page-qr-code"></div>
        <div id="page-qr-label">Scan to open the same broadcasting panel in your phone</div>
    </div>

    <button id="bgm-toggle" type="button" title="Pause background music" aria-pressed="false"
        data-playing="false" disabled>
        &#9835; Music Off
    </button>

    <button id="overlay-toggle" class="btn btn-primary btn-lg" type="button" title="Show video overlay controls">Media
        Management</button>

    <div id="overlay-controls" style="display: none;">
        <h4>
            <span style="flex:1;">Broadcast Video Overlay</span>
            <button type="button" id="overlay-collapse" title="Hide"
                style="background:transparent;color:#fff;border:none;cursor:pointer;padding:0;font-size:16px;line-height:1;flex:0 0 auto;">&times;</button>
        </h4>
        <div class="overlay-section-label">
            <span style="flex:1;">Attached Media</span>
            <button id="overlay-media-refresh" type="button" title="Reload list">Refresh</button>
        </div>
        <div id="overlay-media-list">
            <div id="overlay-media-loading">Loading attached media&hellip;</div>
        </div>
        <input id="overlay-url" type="hidden" value="" />
        <div class="row">
            <select id="overlay-position" title="Overlay position">
                <option value="fullscreen" selected>Fullscreen</option>

                <option value="top-right">Top Right</option>
                <option value="top-left">Top Left</option>
                <option value="bottom-right">Bottom Right</option>
                <option value="bottom-left">Bottom Left</option>
                <option value="center">Center</option>
            </select>
            <select id="overlay-size" title="Overlay size" style="flex:0 0 90px;">
                <option value="0.25">25%</option>
                <option value="0.35" selected>35%</option>
                <option value="0.5">50%</option>
                <option value="0.7">70%</option>
            </select>
        </div>
        <div class="row">
            <label style="flex:0 0 auto;font-size:12px;color:rgba(255,255,255,0.75);">Music volume</label>
            <input id="bgm-volume" type="range" min="0" max="100" value="20" title="Background music volume"
                style="flex:1;" />
        </div>
        <div class="row">
            <label style="flex:1;display:flex;align-items:center;gap:6px;font-size:12px;cursor:pointer;">
                <input id="overlay-loop" type="checkbox" /> Loop
            </label>
            <label style="flex:1;display:flex;align-items:center;gap:6px;font-size:12px;cursor:pointer;">
                <input id="overlay-muted" type="checkbox" /> Mute audio
            </label>
        </div>
        <div class="row">
            <button id="overlay-play" type="button">Play</button>
            <button id="overlay-stop" type="button">Stop</button>
        </div>
        <div id="overlay-status">Pipeline: initializing&hellip;</div>
    </div>

    <div id="root"></div>


    {{-- <div id="fullscreen-go-live-overlay" style="
    position: fixed;
    inset: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(16,16,16,0.92);
    z-index: 99999;
    display: flex;
    align-items: center;
    justify-content: center;
">
    <button id="go-live-main-btn" style="
        font-size: 2.7rem;
        padding: 1.5em 3em;
        border-radius: 2em;
        border: none;
        background: linear-gradient(90deg, #4ade80 0%, #3b82f6 100%);
        color: #fff;
        font-weight: bold;
        cursor: pointer;
        box-shadow: 0 4px 32px 0 #0006;
        transition: transform 0.1s;
    ">
        Go live
    </button> --}}
    </div>
    {{-- <script>
    document.addEventListener('DOMContentLoaded', function() {
        const overlay = document.getElementById('fullscreen-go-live-overlay');
        const btn = document.getElementById('go-live-main-btn');
        btn.addEventListener('click', function() {
            // Hide the overlay
            overlay.style.display = 'none';
            // Find and click #ZegoLiveButton
            const zegoBtn = document.querySelector('#ZegoLiveButton');
            if (zegoBtn) {
                zegoBtn.click();
            } else {
                // fallback: show overlay again & alert user
                overlay.style.display = '';
                alert('Could not find the Go Live button in the UI (#ZegoLiveButton).');
            }
        });
    });
</script> --}}

    {{-- <div id="broadcasterTakeoverPrompt" role="dialog" aria-live="polite" aria-hidden="true">
        <div class="card">
            <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
            <h3>Broadcaster already open</h3>
            <p>
                This live show broadcaster is already open in another tab or on another device.
                Do you want to continue here? The other session will be stopped.
            </p>
            <div class="actions">
                <button id="broadcasterTakeoverContinueBtn" type="button">Continue here</button>
                <button id="broadcasterTakeoverCancelBtn" type="button">Cancel</button>
            </div>
        </div>
    </div> --}}

    {{-- <div id="broadcasterInactiveOverlay" role="dialog" aria-live="assertive" aria-hidden="true">
        <div class="card">
            <div class="icon"><i class="fas fa-tv"></i></div>
            <h3>Broadcaster opened elsewhere</h3>
            <p>
                This live show broadcaster has been opened in another tab or on another device.
                Only one broadcaster tab can stream at a time.
            </p>
            <button id="broadcasterUseHereBtn" type="button">
                Use this tab instead
            </button>
            <button id="broadcasterCloseBtn" type="button">Close</button>
        </div>
    </div> --}}
</body>
<script>
    // ─────────────────────────────────────────────────────────────
    //  Broadcast Video Overlay – getUserMedia interception
    //
    //  Wraps navigator.mediaDevices.getUserMedia BEFORE the Zego
    //  prebuilt UI loads so that when prebuilt asks for the host's
    //  camera/mic, we substitute a canvas-mixed stream that can
    //  optionally render an external <video> on top of the camera
    //  feed. The audience receives the merged stream as part of
    //  the normal broadcast – no audience-side changes required.
    // ─────────────────────────────────────────────────────────────
    (function() {
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) return;

        // Set canvas dimensions responsively depending on device type
        function isMobile() {
            return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
        }
        const CANVAS_WIDTH = isMobile() ? 360 : 1280;
        const CANVAS_HEIGHT = isMobile() ? 640 : 720;

        const TARGET_FPS = 50;

        const origGetUserMedia = navigator.mediaDevices.getUserMedia.bind(navigator.mediaDevices);

        let pipeline = null;
        let pipelinePromise = null;
        let overlayVideoEl = null;
        let overlayImageEl = null;

        const liveShowMediaHiddenUrl = '{{ route('admin.live-shows.media-hidden', $liveShow->id) }}';
        const liveShowMediaPlayedUrl = '{{ route('admin.live-shows.media-played', $liveShow->id) }}';
        const csrfToken = '{{ csrf_token() }}';
        const BGM_URL = '{{ asset('badabing-audio/bg-think-fast.mp3') }}';

        const BG_VOLUME = 0.1;

        const hostAudio = new Audio(BGM_URL);
        hostAudio.loop = true;
        hostAudio.preload = 'auto';

        hostAudio.crossOrigin = 'anonymous';
        hostAudio.volume = BG_VOLUME;

        // Overlay state, mutable from the UI controls.
        const overlayState = {
            visible: false,
            mediaType: null, // 'video' | 'image' | null
            position: 'center',
            size: 0.35,
        };

        async function buildPipeline(constraints) {
            // Always grab BOTH camera + mic so we have everything to mix.
            const sourceStream = await origGetUserMedia({
                video: constraints && constraints.video !== false ? (constraints.video === true ? true :
                    constraints.video || true) : true,
                audio: constraints && constraints.audio !== false ? (constraints.audio === true ? true :
                    constraints.audio || true) : true,
            });

            // Hidden host container kept in the DOM so video decoders don't get
            // background-tab throttled the way fully off-DOM <video> elements do.
            let hiddenHost = document.getElementById('broadcast-hidden-media');
            if (!hiddenHost) {
                hiddenHost = document.createElement('div');
                hiddenHost.id = 'broadcast-hidden-media';
                hiddenHost.style.cssText = [
                    'position:fixed',
                    'left:-9999px',
                    'top:-9999px',
                    'width:2px',
                    'height:2px',
                    'opacity:0.01',
                    'pointer-events:none',
                    'overflow:hidden',
                ].join(';');
                document.body.appendChild(hiddenHost);
            }

            // Camera preview <video> (in-DOM but off-screen, used as canvas drawImage source).
            const cameraVideoEl = document.createElement('video');
            cameraVideoEl.srcObject = sourceStream;
            cameraVideoEl.muted = true;
            cameraVideoEl.autoplay = true;
            cameraVideoEl.playsInline = true;
            cameraVideoEl.style.cssText = 'width:2px;height:2px;';
            hiddenHost.appendChild(cameraVideoEl);
            try {
                await cameraVideoEl.play();
            } catch (_) {
                /* autoplay can defer */
            }

            // The overlay <video> for whatever URL the host plays.
            overlayVideoEl = document.createElement('video');
            overlayVideoEl.crossOrigin = 'anonymous';
            overlayVideoEl.playsInline = true;
            overlayVideoEl.preload = 'auto';
            overlayVideoEl.muted = false;
            overlayVideoEl.style.cssText = 'width:2px;height:2px;';
            hiddenHost.appendChild(overlayVideoEl);

            // Overlay <img> for gallery images (composited like video).
            overlayImageEl = document.createElement('img');
            overlayImageEl.crossOrigin = 'anonymous';
            overlayImageEl.style.cssText = 'width:2px;height:2px;';
            hiddenHost.appendChild(overlayImageEl);

            // Canvas where camera + overlay get composited every frame.
            const canvas = document.createElement('canvas');
            canvas.width = CANVAS_WIDTH;
            canvas.height = CANVAS_HEIGHT;
            const ctx = canvas.getContext('2d');

            function drawCompositedOverlay(naturalWidth, naturalHeight, sourceEl) {
                const aspect = naturalWidth / naturalHeight;
                let ow, oh, ox, oy;

                if (overlayState.position === 'fullscreen') {
                    const sc = Math.min(canvas.width / naturalWidth, canvas.height / naturalHeight);
                    ow = naturalWidth * sc;
                    oh = naturalHeight * sc;
                    ox = (canvas.width - ow) / 2;
                    oy = (canvas.height - oh) / 2;
                    ctx.fillStyle = '#000';
                    ctx.fillRect(0, 0, canvas.width, canvas.height);
                } else {
                    ow = canvas.width * (overlayState.size || 0.35);
                    oh = ow / aspect;
                    const margin = 24;
                    switch (overlayState.position) {
                        case 'top-left':
                            ox = margin;
                            oy = margin;
                            break;
                        case 'bottom-left':
                            ox = margin;
                            oy = canvas.height - oh - margin;
                            break;
                        case 'bottom-right':
                            ox = canvas.width - ow - margin;
                            oy = canvas.height - oh - margin;
                            break;
                        case 'center':
                            ox = (canvas.width - ow) / 2;
                            oy = (canvas.height - oh) / 2;
                            break;
                        case 'top-right':
                        default:
                            ox = canvas.width - ow - margin;
                            oy = margin;
                            break;
                    }
                    ctx.save();
                    ctx.shadowColor = 'rgba(0,0,0,0.55)';
                    ctx.shadowBlur = 18;
                    ctx.shadowOffsetY = 4;
                    ctx.fillStyle = '#000';
                    ctx.fillRect(ox - 2, oy - 2, ow + 4, oh + 4);
                    ctx.restore();
                }

                ctx.drawImage(sourceEl, ox, oy, ow, oh);
            }

            function drawFrame() {
                if (cameraVideoEl.readyState >= 2 && cameraVideoEl.videoWidth > 0) {
                    const cw = cameraVideoEl.videoWidth;
                    const ch = cameraVideoEl.videoHeight;
                    const scale = Math.max(canvas.width / cw, canvas.height / ch);
                    const dw = cw * scale;
                    const dh = ch * scale;
                    const dx = (canvas.width - dw) / 2;
                    const dy = (canvas.height - dh) / 2;
                    ctx.drawImage(cameraVideoEl, dx, dy, dw, dh);
                } else {
                    ctx.fillStyle = '#000';
                    ctx.fillRect(0, 0, canvas.width, canvas.height);
                }

                if (overlayState.visible && overlayState.mediaType === 'video' &&
                    !overlayVideoEl.paused &&
                    overlayVideoEl.readyState >= 2 &&
                    overlayVideoEl.videoWidth > 0) {
                    drawCompositedOverlay(
                        overlayVideoEl.videoWidth,
                        overlayVideoEl.videoHeight,
                        overlayVideoEl
                    );
                } else if (overlayState.visible && overlayState.mediaType === 'image' &&
                    overlayImageEl.complete &&
                    overlayImageEl.naturalWidth > 0) {
                    drawCompositedOverlay(
                        overlayImageEl.naturalWidth,
                        overlayImageEl.naturalHeight,
                        overlayImageEl
                    );
                }
            }

            // ── Background-resilient frame ticker ──────────────────
            // requestAnimationFrame is throttled (or paused) in hidden tabs,
            // which freezes the canvas-captureStream feed for the audience.
            // A Web Worker setInterval keeps ticking regardless of tab focus.
            const tickerSrc = `
                let id = null;
                onmessage = (e) => {
                    if (e && e.data && e.data.type === 'start') {
                        clearInterval(id);
                        id = setInterval(() => postMessage('tick'), 1000 / e.data.fps);
                    } else if (e && e.data === 'stop') {
                        clearInterval(id);
                        id = null;
                    }
                };
            `;
            let frameTicker = null;
            try {
                const blob = new Blob([tickerSrc], {
                    type: 'application/javascript'
                });
                frameTicker = new Worker(URL.createObjectURL(blob));
                frameTicker.onmessage = () => {
                    try {
                        drawFrame();
                    } catch (e) {
                        /* swallow per-frame errors */
                    }
                };
                frameTicker.postMessage({
                    type: 'start',
                    fps: TARGET_FPS
                });
            } catch (e) {
                console.warn('[Pipeline] Worker ticker unavailable, falling back to rAF:', e);
                const rafLoop = () => {
                    drawFrame();
                    requestAnimationFrame(rafLoop);
                };
                requestAnimationFrame(rafLoop);
            }

            // Belt-and-braces: when the tab becomes visible again, force a few
            // immediate redraws and try to resume any media that paused.
            document.addEventListener('visibilitychange', () => {
                if (document.visibilityState !== 'visible') return;
                try {
                    cameraVideoEl.play().catch(() => {});
                } catch (_) {}
                if (overlayState.visible && overlayState.mediaType === 'video') {
                    try {
                        overlayVideoEl.play().catch(() => {});
                    } catch (_) {}
                }
                if (!bgmEl.paused) {
                    bgmEl.play().catch(() => {});
                }
                for (let i = 0; i < 3; i++) drawFrame();
            });

            // ── Video track ────────────────────────────────────────
            const canvasStream = canvas.captureStream(TARGET_FPS);
            const mixedVideoTrack = canvasStream.getVideoTracks()[0];

            // ── Audio mixing ───────────────────────────────────────
            const AudioCtx = window.AudioContext || window.webkitAudioContext;
            const audioContext = new AudioCtx();
            const audioDestination = audioContext.createMediaStreamDestination();

            // Mic → destination
            let micGain = null;
            if (sourceStream.getAudioTracks().length > 0) {
                const micOnly = new MediaStream([sourceStream.getAudioTracks()[0]]);
                const micSource = audioContext.createMediaStreamSource(micOnly);
                micGain = audioContext.createGain();
                micGain.gain.value = 1.0;
                micSource.connect(micGain).connect(audioDestination);
            }

            // Overlay video audio is wired lazily on first play (createMediaElementSource
            // can only be called once per element).
            let overlayAudioConnected = false;
            const overlayGain = audioContext.createGain();
            overlayGain.gain.value = 1.0;
            overlayGain.connect(audioDestination);
            // Also let the host monitor it locally (so they can hear what's playing).
            overlayGain.connect(audioContext.destination);

            function ensureOverlayAudioWired() {
                if (overlayAudioConnected) return;
                try {
                    const src = audioContext.createMediaElementSource(overlayVideoEl);
                    src.connect(overlayGain);
                    overlayAudioConnected = true;
                } catch (e) {
                    console.warn('Overlay audio wiring failed:', e);
                }
            }

            // Background music → mixed stream (audience hears this)
            const bgmEl = document.createElement('audio');
            bgmEl.src = BGM_URL;
            bgmEl.loop = true;
            bgmEl.preload = 'auto';
            bgmEl.crossOrigin = 'anonymous';
            hiddenHost.appendChild(bgmEl);

            const bgmGain = audioContext.createGain();
            bgmGain.gain.value = BG_VOLUME;
            try {
                const bgmSource = audioContext.createMediaElementSource(bgmEl);
                bgmSource.connect(bgmGain).connect(audioDestination);
                // Host local monitoring (same mix the audience receives).
                bgmGain.connect(audioContext.destination);
            } catch (e) {
                console.warn('BGM audio wiring failed:', e);
            }

            const mixedAudioTrack = audioDestination.stream.getAudioTracks()[0];

            // overlayVideoEl.addEventListener('playing', () => {
            //     //api call to dispatch LiveShowMediaPlayed event
            //     fetch(liveShowMediaPlayedUrl, {
            //         method: 'POST',
            //         headers: {
            //             'X-CSRF-TOKEN': csrfToken,
            //             'Accept': 'application/json',
            //             'Content-Type': 'application/json'
            //         }
            //     });
            //     console.log('LiveShowMediaPlayed event dispatched successfully!');
            // });


            overlayVideoEl.addEventListener('ended', () => {
                console.log('Streamed video finished!');

                //api call to dispatch LiveShowMediaHidden event
                fetch(liveShowMediaHiddenUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                });
                console.log('LiveShowMediaHidden event dispatched successfully!');
            });

            return {
                cameraVideoEl,
                overlayVideoEl,
                overlayImageEl,
                canvas,
                audioContext,
                overlayGain,
                micGain,
                bgmEl,
                bgmGain,
                mixedVideoTrack,
                mixedAudioTrack,
                sourceStream,
                ensureOverlayAudioWired,
            };



        }



        function getOrInitPipeline(constraints) {
            if (pipeline) return Promise.resolve(pipeline);
            if (pipelinePromise) return pipelinePromise;

            pipelinePromise = buildPipeline(constraints).then(p => {
                pipeline = p;
                pipelinePromise = null;
                window.dispatchEvent(new CustomEvent('broadcast-pipeline-ready'));
                return p;
            }).catch(err => {
                pipelinePromise = null;
                throw err;
            });
            return pipelinePromise;
        }

        navigator.mediaDevices.getUserMedia = async function(constraints) {
            // Skip interception for non-AV requests (e.g. some prebuilt utility calls).
            const wantsVideo = !!(constraints && constraints.video);
            const wantsAudio = !!(constraints && constraints.audio);
            if (!wantsVideo && !wantsAudio) return origGetUserMedia(constraints);

            const p = await getOrInitPipeline(constraints);
            const tracks = [];
            if (wantsVideo) tracks.push(p.mixedVideoTrack);
            if (wantsAudio) tracks.push(p.mixedAudioTrack);
            return new MediaStream(tracks);
        };

        // ── Public overlay control API ────────────────────────────
        window.BroadcastOverlay = {
            isReady() {
                return !!pipeline;
            },

            _clearImageOverlay() {
                if (!pipeline || !pipeline.overlayImageEl) return;
                pipeline.overlayImageEl.onload = null;
                pipeline.overlayImageEl.onerror = null;
                try {
                    pipeline.overlayImageEl.removeAttribute('src');
                } catch (_) {}
            },

            _clearVideoOverlay() {
                if (!pipeline || !pipeline.overlayVideoEl) return;
                pipeline.overlayVideoEl.pause();
                try {
                    pipeline.overlayVideoEl.removeAttribute('src');
                    pipeline.overlayVideoEl.load();
                } catch (_) {}
            },

            play(url, options) {
                if (!pipeline) {
                    console.warn('[Overlay] Pipeline not ready yet, retrying when available.');
                    window.addEventListener('broadcast-pipeline-ready',
                        () => window.BroadcastOverlay.play(url, options), {
                            once: true
                        });
                    return;
                }
                const opts = options || {};
                if (opts.type === 'image') {
                    return this.playImage(url, opts);
                }
                return this.playVideo(url, opts);
            },

            playImage(url, options) {
                if (!pipeline) return;
                const opts = options || {};
                const img = pipeline.overlayImageEl;

                this._clearVideoOverlay();
                overlayState.mediaType = 'image';
                overlayState.visible = false;
                if (opts.position) overlayState.position = opts.position;
                if (typeof opts.size === 'number') overlayState.size = opts.size;

                img.onload = () => {
                    overlayState.visible = true;
                };
                img.onerror = () => {
                    overlayState.visible = false;
                    const err = new Error('Image failed to load (check URL / CORS).');
                    console.error('[Overlay] image load failed:', url, err);
                    window.dispatchEvent(new CustomEvent('broadcast-overlay-error', {
                        detail: err
                    }));
                };
                img.crossOrigin = 'anonymous';
                img.src = url;
            },

            playVideo(url, options) {
                if (!pipeline) return;
                const opts = options || {};
                const v = pipeline.overlayVideoEl;

                this._clearImageOverlay();
                overlayState.mediaType = 'video';
                overlayState.visible = false;

                v.loop = !!opts.loop;
                v.muted = !!opts.muted;

                if (pipeline.audioContext.state === 'suspended') {
                    pipeline.audioContext.resume().catch(() => {});
                }

                if (!v.muted) pipeline.ensureOverlayAudioWired();

                if (opts.position) overlayState.position = opts.position;
                if (typeof opts.size === 'number') overlayState.size = opts.size;

                v.src = url;
                v.load();
                v.play().then(() => {
                    overlayState.visible = true;
                }).catch(err => {
                    console.error('[Overlay] play failed:', err);
                    window.dispatchEvent(new CustomEvent('broadcast-overlay-error', {
                        detail: err
                    }));
                });
            },

            stop() {
                if (!pipeline) return;
                this._clearVideoOverlay();
                this._clearImageOverlay();
                overlayState.visible = false;
                overlayState.mediaType = null;
            },

            setPosition(position) {
                if (position) overlayState.position = position;
            },

            setSize(size) {
                if (typeof size === 'number') overlayState.size = size;
            },

            setOverlayVolume(v) {
                if (pipeline && pipeline.overlayGain) pipeline.overlayGain.gain.value = Math.max(0, Math.min(2,
                    v));
            },

            _emitBgmState(playing) {
                window.dispatchEvent(new CustomEvent('broadcast-bgm-state-changed', {
                    detail: {
                        playing: !!playing
                    }
                }));
            },

            async startBgm() {
                if (!pipeline || !pipeline.bgmEl) return false;
                try {
                    if (pipeline.audioContext.state === 'suspended') {
                        await pipeline.audioContext.resume();
                    }
                    await pipeline.bgmEl.play();
                    this._emitBgmState(true);

                    console.log('Playing host audio');
                    await hostAudio.play();
                    return true;
                } catch (e) {
                    console.warn('[BGM] play failed:', e);
                    this._emitBgmState(false);
                    return false;
                }
            },

            pauseBgm() {
                if (!pipeline || !pipeline.bgmEl) return;
                pipeline.bgmEl.pause();
                this._emitBgmState(false);
                console.log('Pausing host audio');
                hostAudio.pause();
            },

            stopBgm() {
                if (!pipeline || !pipeline.bgmEl) return;
                pipeline.bgmEl.pause();
                pipeline.bgmEl.currentTime = 0;
                this._emitBgmState(false);
            },

            async toggleBgm() {
                if (this.isBgmPlaying()) {
                    this.pauseBgm();
                    return false;
                }
                return this.startBgm();
            },

            setBgmVolume(v) {
                if (pipeline && pipeline.bgmGain) {
                    pipeline.bgmGain.gain.value = Math.max(0, Math.min(1, v));
                }
            },

            isBgmPlaying() {
                return !!(pipeline && pipeline.bgmEl && !pipeline.bgmEl.paused);
            },

            getState() {
                return Object.assign({}, overlayState, {
                    ready: !!pipeline
                });
            },
        };
    })();
</script>

<script src="https://resource.ZEGOCLOUD.com/prebuilt/crypto-js.js"></script>
<script src="https://resource.ZEGOCLOUD.com/prebuilt/prebuiltToken.js"></script>
<script src="https://unpkg.com/@ZEGOCLOUD/zego-uikit-prebuilt/zego-uikit-prebuilt.js"></script>



<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>

{{-- Zego must not start until the broadcaster lock allows this tab. --}}
{{-- <script>
    window.__broadcasterLockReady = new Promise(function(resolve, reject) {
        window.__resolveBroadcasterLock = resolve;
        window.__rejectBroadcasterLock = reject;
    });
</script> --}}

<script>
    window.onload = async function() {
        // Wait until this tab owns the broadcaster (first tab auto-claims;
        // additional tabs must confirm via the takeover prompt).
        // try {
        //     await window.__broadcasterLockReady;
        // } catch (e) {
        //     console.log('[Broadcaster lock] Streaming not started in this tab.');
        //     return;
        // }

        function getUrlParams(url) {
            let urlStr = url.split('?')[1] || "";
            const urlSearchParams = new URLSearchParams(urlStr);
            return Object.fromEntries(urlSearchParams.entries());
        }

        function saveRoomID(roomID) {
            return fetch(
                '{{ route('admin.live-shows.stream-management.save-room-id', ['id' => $liveShow->id]) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        'room_id': roomID
                    })
                });
        }

        // 1) get or create roomID in outer scope
        const roomID = "{{ 'RoomID_' . $liveShow->id . rand(1000, 9999) }}";

        try {
            // 2) save it first
            const response = await saveRoomID(roomID);
            const data = await response.json();
            console.log('Room ID saved successfully:', data);

            // 3) only after save succeeds, use it
            const userID = Math.floor(Math.random() * 10000) + "";
            const userName = "userName" + userID;
            const appID = {{ env('ZEGO_APP_ID', 1251897065) }};
            const serverSecret = "{{ env('ZEGO_SERVER_SECRET', 'ac4b30ceb3e43b0280c7fa40be34d2ef') }}";
            const TOKEN = generatePrebuiltToken(appID, serverSecret, roomID, userID, userName);

            
            let roleParam = 'Host';
            let role = roleParam === 'Host' ?
                ZegoUIKitPrebuilt.Host :
                ZegoUIKitPrebuilt.Audience;

            let config = {};
            if (roleParam === 'Host') {
                config = {
                    turnOnCameraWhenJoining: true,
                    showMyCameraToggleButton: true,
                    showAudioVideoSettingsButton: true,
                    showScreenSharingButton: true,
                    showTextChat: false,
                    showUserList: false,
                    showPreJoinView: false,
                    showUserJoinAndLeave: false,
                    showMirror: false,
                    fillMode: "cover",

                    onLeaveRoom: () => {
                        if (window.BroadcastOverlay) window.BroadcastOverlay.stopBgm();
                    },
                };
            } else {
                config = {
                    turnOnCameraWhenJoining: false,
                    showMyCameraToggleButton: false,
                    showAudioVideoSettingsButton: false,
                    showScreenSharingButton: false,
                    showTextChat: false,
                    showUserList: false,
                    showPreJoinView: false,
                    showUserJoinAndLeave: false,
                    showMirror: false,
                    fillMode: "cover",
                };
            }



            const zp = ZegoUIKitPrebuilt.create(TOKEN);
            // Expose the Zego instance globally so the single-broadcaster
            // lock script can tear it down when this tab gets superseded.
            window.__zegoInstance = zp;
            zp.joinRoom({
                container: document.querySelector("#root"),
                videoResolutionList: [ZegoUIKitPrebuilt.VideoResolution_720P],
                videoResolutionDefault: ZegoUIKitPrebuilt.VideoResolution_720P,
                captureWidth: 1080,
                captureHeight: 1920,
                encodeWidth: 1080,
                encodeHeight: 1920,
                fps: 30, // Match your Camo Studio frame rate
                bitrate: 3000,


                scenario: {
                    mode: ZegoUIKitPrebuilt.LiveStreaming,
                    config: {
                        role
                    },
                },

                onLiveStart: (user) => {
                    console.log("Success! The stream has officially started.");
                    console.log("Broadcasting user details:", user);
                    const bgmBtn = document.getElementById('bgm-toggle');
                    const shouldPlay = !bgmBtn || bgmBtn.getAttribute('data-playing') !== 'false';
                    if (window.BroadcastOverlay && shouldPlay) {
                        window.BroadcastOverlay.startBgm();
                    }
                },
                // --- Connection state handling ---------------------------------
                // Background-tab throttling can cause brief DISCONNECTED blips.
                // Let Zego try to reconnect on its own first; only force a full
                // page reload if we really stay disconnected for a long time.
                onInRoomStateChanged: (state) => {

                    console.log("Connection state:", state);
                    if (state === 'CONNECTED') {
                        window.__lastZegoConnectedAt = Date.now();
                        if (window.__zegoReloadTimer) {
                            clearTimeout(window.__zegoReloadTimer);
                            window.__zegoReloadTimer = null;
                        }
                        return;
                    }
                    if (state === 'DISCONNECTED' || state === 'RECONNECTING') {
                        if (window.__zegoReloadTimer) return;
                        console.warn("Zego disconnected; waiting for reconnect before reloading.");
                        window.__zegoReloadTimer = setTimeout(() => {
                            const lastOk = window.__lastZegoConnectedAt || 0;
                            if (Date.now() - lastOk > 30000) {
                                console.warn(
                                    "Zego still disconnected after grace period, reloading."
                                );
                                window.location.reload();
                            } else {
                                window.__zegoReloadTimer = null;
                            }
                        }, 30000);
                    }
                },
                // ---------------------------------------------------------------
                sharedLinks: [{
                    name: 'Join as an audience',
                    url: window.location.origin +
                        window.location.pathname +
                        '?roomID=' + roomID +
                        '&role=Audience',
                }],
                ...config
            });




        } catch (error) {
            console.error('Error saving Room ID:', error);
            // Optionally decide whether to still join room or not
        }

        generateQRCode(window.location.href);
    }


    function generateQRCode(link) {
        const qrcodeContainer = document.getElementById('page-qr-code');
        qrcodeContainer.innerHTML = '';
        new QRCode(qrcodeContainer, {
            text: link,
            width: 110,
            height: 110
        });
    }
</script>

<script>
    // ── Wire the overlay control panel ─────────────────────────────

    const $ = (id) => document.getElementById(id);

    const panel = $('overlay-controls');
    const toggle = $('overlay-toggle');
    const collapse = $('overlay-collapse');
    const urlInput = $('overlay-url');
    const posSel = $('overlay-position');
    const sizeSel = $('overlay-size');
    const loopChk = $('overlay-loop');
    const muteChk = $('overlay-muted');
    const bgmToggleBtn = $('bgm-toggle');
    const bgmVolumeSlider = $('bgm-volume');
    const playBtn = $('overlay-play');
    const stopBtn = $('overlay-stop');
    const status = $('overlay-status');
    const mediaList = $('overlay-media-list');
    const mediaRefresh = $('overlay-media-refresh');
    const galleryShowOnStreamUrl =
        '{{ route('admin.live-shows.stream-management.show-gallery-image', ['id' => $liveShow->id]) }}';
    const galleryCsrf = '{{ csrf_token() }}';
    const galleryHideOnStreamUrl =
        '{{ route('admin.live-shows.stream-management.hide-gallery-image', ['id' => $liveShow->id]) }}';

    const ATTACHED_MEDIA_URL =
        '{{ route('admin.live-shows.stream-management.attached-media', ['id' => $liveShow->id]) }}';

    let selectedMedia = null;

    hidePanel();

    function setStatus(text, color) {
        if (!status) return;
        status.textContent = text;
        status.style.color = color || 'rgba(255,255,255,0.65)';
    }

    function escapeHtml(s) {
        return String(s == null ? '' : s)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function fmtDuration(secs) {
        if (!secs || secs < 0) return '';
        const m = Math.floor(secs / 60);
        const s = Math.floor(secs % 60).toString().padStart(2, '0');
        return m + ':' + s;
    }

    function renderMediaItem(m) {
        const title = m.title || m.original_name || ('Media #' + m.id);
        const dur = fmtDuration(m.total_seconds);
        const sub = [m.type, dur].filter(Boolean).join(' • ');
        const thumb = m.type === 'video' ?
            `<img class="overlay-media-thumb" src="${escapeHtml(m.thumbnail)}" alt="" />` :
            `<img class="overlay-media-thumb" src="${escapeHtml(m.path)}" alt="" />`;

        const item = document.createElement('div');
        item.className = 'overlay-media-item';
        item.dataset.id = m.id;
        item.dataset.url = m.url;
        item.innerHTML = `
                ${thumb}
                <div class="overlay-media-meta">
                    <div class="title">${escapeHtml(title)}</div>
                    <div class="sub">${escapeHtml(sub)}</div>
                </div>
                <div class="overlay-media-check"></div>
            `;
        item.addEventListener('click', () => selectMedia(m, item));
        return item;
    }

    function selectMedia(m, itemEl) {
        mediaList.querySelectorAll('.overlay-media-item.selected')
            .forEach(el => el.classList.remove('selected'));
        if (itemEl) itemEl.classList.add('selected');
        selectedMedia = m;
        urlInput.value = m.url;
        setStatus('Selected: ' + (m.title || m.original_name || ('Media #' + m.id)));
    }

    async function loadAttachedMedia() {
        mediaList.innerHTML = '<div id="overlay-media-loading">Loading attached media…</div>';
        try {
            const res = await fetch(ATTACHED_MEDIA_URL, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin',
            });
            if (!res.ok) throw new Error('HTTP ' + res.status);
            const data = await res.json();

            mediaList.innerHTML = '';

            if (!data.media || data.media.length === 0) {
                mediaList.innerHTML =
                    '<div id="overlay-media-empty">No image or video media attached to this live show.</div>';
                return;
            }

            const previouslySelectedId = selectedMedia ? selectedMedia.id : null;
            let restored = null;

            data.media.forEach(m => {
                const itemEl = renderMediaItem(m);
                mediaList.appendChild(itemEl);
                if (previouslySelectedId && m.id === previouslySelectedId) {
                    restored = {
                        m,
                        itemEl
                    };
                }
            });

            if (restored) {
                selectMedia(restored.m, restored.itemEl);
            } else {
                selectedMedia = null;
                urlInput.value = '';
            }
        } catch (err) {
            console.error('[Overlay] Failed to load attached media:', err);
            mediaList.innerHTML =
                '<div id="overlay-media-empty" style="color:#fca5a5;">Failed to load media. Click Refresh to retry.</div>';
        }
    }

    if (mediaRefresh) mediaRefresh.addEventListener('click', loadAttachedMedia);
    loadAttachedMedia();

    function showPanel() {
        panel.style.display = 'block';
        toggle.style.display = 'none';
    }

    function hidePanel() {
        panel.style.display = 'none';
        toggle.style.display = 'inline-block';
    }

    if (collapse) collapse.addEventListener('click', hidePanel);
    if (toggle) toggle.addEventListener('click', showPanel);

    function updateBgmToggleUI(playing) {
        if (!bgmToggleBtn) return;
        bgmToggleBtn.setAttribute('aria-pressed', playing ? 'true' : 'false');
        bgmToggleBtn.setAttribute('data-playing', playing ? 'true' : 'false');
        bgmToggleBtn.title = playing ? 'Pause background music' : 'Play background music';
        bgmToggleBtn.innerHTML = playing ? '&#9835; Music On' : '&#9654; Music Off';
    }

    if (bgmToggleBtn) {
        bgmToggleBtn.addEventListener('click', async () => {
            if (!window.BroadcastOverlay || !window.BroadcastOverlay.isReady()) return;
            await window.BroadcastOverlay.toggleBgm();
        });
        window.addEventListener('broadcast-bgm-state-changed', (ev) => {
            updateBgmToggleUI(!!(ev.detail && ev.detail.playing));
        });
        window.addEventListener('broadcast-pipeline-ready', () => {
            if (bgmToggleBtn) bgmToggleBtn.disabled = false;
            if (bgmVolumeSlider && window.BroadcastOverlay) {
                window.BroadcastOverlay.setBgmVolume(parseInt(bgmVolumeSlider.value, 10) / 100);
            }
        });
    }

    function refreshReadyState() {
        const ready = window.BroadcastOverlay && window.BroadcastOverlay.isReady();
        playBtn.disabled = !ready;
        stopBtn.disabled = !ready;
        if (bgmToggleBtn) bgmToggleBtn.disabled = !ready;
        setStatus(ready ? 'Pipeline: ready' : 'Pipeline: initializing…',
            ready ? '#86efac' : 'rgba(255,255,255,0.65)');
    }

    refreshReadyState();
    window.addEventListener('broadcast-pipeline-ready', refreshReadyState);

    // Live-update position/size while a video is already playing
    posSel.addEventListener('change', () => {
        if (window.BroadcastOverlay) window.BroadcastOverlay.setPosition(posSel.value);
    });
    sizeSel.addEventListener('change', () => {
        if (window.BroadcastOverlay) window.BroadcastOverlay.setSize(parseFloat(sizeSel.value));
    });

    if (bgmVolumeSlider) {
        bgmVolumeSlider.addEventListener('input', () => {
            const v = parseInt(bgmVolumeSlider.value, 10) / 100;
            if (window.BroadcastOverlay) window.BroadcastOverlay.setBgmVolume(v);
        });
    }

    playBtn.addEventListener('click', () => {
        const url = (urlInput.value || '').trim();
        if (!url) {
            setStatus('Select a media item first.', '#fca5a5');
            return;
        }
        if (!window.BroadcastOverlay || !window.BroadcastOverlay.isReady()) {
            setStatus('Stream pipeline not ready yet.', '#fca5a5');
            return;
        }
        const label = selectedMedia ?
            (selectedMedia.title || selectedMedia.original_name || ('Media #' + selectedMedia.id)) :
            'overlay';
        const mediaType = selectedMedia && selectedMedia.type === 'image' ? 'image' : 'video';
        setStatus('Loading ' + label + '…');
        window.BroadcastOverlay.play(url, {
            type: mediaType,
            position: posSel.value,
            size: parseFloat(sizeSel.value),
            loop: loopChk.checked,
            muted: mediaType === 'image' ? true : muteChk.checked,
        });
        setTimeout(() => setStatus((mediaType === 'image' ? 'Showing' : 'Playing') + ': ' + label, '#86efac'),
            600);

        //event of show gallery via ajax call
        fetch(galleryShowOnStreamUrl, {
            method: 'POST',

            body: JSON.stringify({
                gallery_media_id: parseInt(selectedMedia.id, 10)
            }),
            headers: {
                'X-CSRF-TOKEN': galleryCsrf,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });

    });

    stopBtn.addEventListener('click', () => {
        if (window.BroadcastOverlay) window.BroadcastOverlay.stop();
        setStatus('Overlay stopped.');

        //event of hide gallery via ajax call
        fetch(galleryHideOnStreamUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': galleryCsrf,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });
    });

    window.addEventListener('broadcast-overlay-error', (ev) => {
        const err = ev.detail || {};
        console.log('Broadcast overlay error:', err);
        // const msg = (err && err.name === 'NotSupportedError') ?
        //     'Cannot play this URL (codec / CORS / format).' :
        //     'Playback failed: ' + (err.message || 'unknown error');
        if (err.name === 'NotAllowedError') {
            // Retry muted; show a "tap for sound" overlay

        }
        const msg = (err && err.message) ? err.message : 'Overlay playback failed.';
        setStatus(msg, '#fca5a5');
    });
</script>


<script>
    // Listen for ShowGalleryImageEvent via Pusher/Echo or Pusher.js (frontend)
    // Assumes Pusher/Echo is already loaded and configured globally as `window.Echo` or `window.Pusher`


    // Helper: get live show ID from DOM or global (adjust if coming from blade/php)
    let liveShowId = {{ $liveShow->id }};
    Pusher.logToConsole = true;
    var pusher = new Pusher('{{ env('PUSHER_APP_KEY', '2a66d003a7ded9fe567a') }}', {
        cluster: '{{ env('PUSHER_APP_CLUSTER', 'eu') }}',
        forceTls: true,
    });

    var channel = pusher.subscribe('live-show.' + liveShowId);

    channel.bind('pusher:subscription_succeeded', function() {
        console.log('ShowGalleryImageEvent Subscribed successfully!');
    });
    channel.bind('ShowGalleryImageEvent', function(data) {
        console.log("[Pusher] ShowGalleryImageEvent received:", data);
        if (window.BroadcastOverlay && data.url) {
            const mediaType = data.type === 'image' ? 'image' : 'video';
            window.BroadcastOverlay.play(data.url, {
                type: mediaType,
                position: 'fullscreen',
                size: 1,
                loop: false,
                muted: mediaType !== 'video',
            });

        } else {
            console.error("[Pusher] No URL received in ShowGalleryImageEvent");
        }
    });

    channel.bind('HideGalleryImageEvent', function() {
        console.log("[Pusher] HideGalleryImageEvent received");
        if (window.BroadcastOverlay) window.BroadcastOverlay.stop();
        setStatus('Overlay stopped.');
    });
    // Listen for Pusher channel events to observe state and failures

    channel.bind('state_change', function(states) {
        console.log('[Pusher] Connection state changed:', states);
        setStatus('[Pusher] State: ' + (states && states.current ? states.current : JSON.stringify(states)));
    });

    channel.bind('error', function(err) {
        console.error('[Pusher] Channel error:', err);
        setStatus('[Pusher] Error: ' + (err && err.message ? err.message : JSON.stringify(err)));
    });

    channel.bind('unavailable', function() {
        console.warn('[Pusher] Channel unavailable');
        setStatus('[Pusher] Channel unavailable');
    });

    // 'subscription_error' is not a channel-level event in Pusher JS, but is a connection event.
    // Listen for it on the Pusher instance if possible.
    pusher.connection.bind('error', function(err) {
        console.error('[Pusher] Connection error:', err);
        setStatus('[Pusher] Connection error: ' + (err && err.error && err.error.data ? JSON.stringify(err.error
            .data) : JSON.stringify(err)));
    });

    pusher.connection.bind('state_change', function(states) {
        console.log('[Pusher] Connection state changed:', states);
        setStatus('[Pusher] Conn State: ' + (states && states.current ? states.current : JSON.stringify(
            states)));
    });
</script>

{{--
    Single-broadcaster lock
    ─────────────────────────
    • First tab (no host_browser_tab yet): auto-claims and starts Zego.
    • New tab while another is active: shows "Continue here?" and waits;
      Zego only starts after the user confirms.
    • Old tab after someone else claims: superseded overlay + stream teardown.
--}}
{{-- <script>
    (function() {
        const LIVE_SHOW_ID = {{ $liveShow->id }};
        const CLAIM_URL =
            '{{ route('admin.live-shows.stream-management.broadcaster.claim-tab', ['id' => $liveShow->id]) }}';
        const ACTIVE_TAB_URL =
            '{{ route('admin.live-shows.stream-management.broadcaster.active-tab', ['id' => $liveShow->id]) }}';
        const CSRF_TOKEN = '{{ csrf_token() }}';
        const POLL_INTERVAL_MS = 15000;

        const myTabId = Date.now() + '-' + Math.random().toString(36).substr(2, 9);
        window.__broadcasterTabId = myTabId;

        const localChannelKey = 'broadcaster_active_tab_' + LIVE_SHOW_ID;
        const localChannel = (typeof BroadcastChannel !== 'undefined') ?
            new BroadcastChannel(localChannelKey) :
            null;

        const takeoverPrompt = document.getElementById('broadcasterTakeoverPrompt');
        const takeoverContinue = document.getElementById('broadcasterTakeoverContinueBtn');
        const takeoverCancel = document.getElementById('broadcasterTakeoverCancelBtn');
        const inactiveOverlay = document.getElementById('broadcasterInactiveOverlay');
        const useHereBtn = document.getElementById('broadcasterUseHereBtn');
        const closeBtn = document.getElementById('broadcasterCloseBtn');

        let superseded = false;
        let isActiveOwner = false;
        let pollTimerId = null;

        function showTakeoverPrompt() {
            if (!takeoverPrompt) return;
            takeoverPrompt.style.display = 'flex';
            takeoverPrompt.setAttribute('aria-hidden', 'false');
        }

        function hideTakeoverPrompt() {
            if (!takeoverPrompt) return;
            takeoverPrompt.style.display = 'none';
            takeoverPrompt.setAttribute('aria-hidden', 'true');
        }

        function showInactiveOverlay() {
            if (!inactiveOverlay) return;
            inactiveOverlay.style.display = 'flex';
            inactiveOverlay.setAttribute('aria-hidden', 'false');
        }

        function hideInactiveOverlay() {
            if (!inactiveOverlay) return;
            inactiveOverlay.style.display = 'none';
            inactiveOverlay.setAttribute('aria-hidden', 'true');
        }


        function teardownBroadcastingPipeline() {
            try {
                if (window.BroadcastOverlay && typeof window.BroadcastOverlay.stop === 'function') {
                    window.BroadcastOverlay.stop();
                }
            } catch (e) {
                console.warn('Overlay stop failed:', e);
            }

            try {
                const zp = window.__zegoInstance;
                if (zp) {
                    if (typeof zp.destroy === 'function') zp.destroy();
                    else if (typeof zp.leaveRoom === 'function') zp.leaveRoom();
                }
            } catch (e) {
                console.warn('Zego teardown failed:', e);
            }

            try {
                document.querySelectorAll('video, audio').forEach(function(el) {
                    const stream = el.srcObject;
                    if (stream && typeof stream.getTracks === 'function') {
                        stream.getTracks().forEach(function(t) {
                            try {
                                t.stop();
                            } catch (_) {}
                        });
                    }
                    try {
                        el.pause();
                    } catch (_) {}
                    el.srcObject = null;
                });
            } catch (e) {
                console.warn('Media element cleanup failed:', e);
            }

            try {
                if (typeof pusher !== 'undefined' && pusher && pusher.disconnect) {
                    pusher.disconnect();
                }
            } catch (e) {
                console.warn('Pusher disconnect failed:', e);
            }
        }

        function markAsSuperseded(reason) {
            if (superseded) return;
            superseded = true;
            isActiveOwner = false;
            console.warn('[Broadcaster lock] Tab superseded:', reason || '(no reason)');
            hideTakeoverPrompt();
            teardownBroadcastingPipeline();
            showInactiveOverlay();
            if (pollTimerId) {
                clearInterval(pollTimerId);
                pollTimerId = null;
            }
        }

        function claimBroadcasterTab() {
            return fetch(CLAIM_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        tab_id: myTabId
                    }),
                })
                .then(function(r) {
                    return r.json();
                })
                .then(function(data) {
                    console.log('[Broadcaster lock] Claimed:', data);
                    if (localChannel) {
                        localChannel.postMessage({
                            type: 'TAB_CLAIMED',
                            tabId: myTabId
                        });
                    }
                    return data;
                })
                .catch(function(err) {
                    console.error('[Broadcaster lock] Claim failed:', err);
                    throw err;
                });
        }

        function closeBroadcasterTab() {
            window.location.href = "about:blank"
        }

        function fetchActiveTabId() {
            return fetch(ACTIVE_TAB_URL, {
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                .then(function(r) {
                    return r.json();
                })
                .then(function(data) {
                    return (data && data.active_tab_id) ? data.active_tab_id : null;
                });
        }

        function activateAsOwner() {
            isActiveOwner = true;
            superseded = false;
            hideTakeoverPrompt();
            hideInactiveOverlay();
            if (!pollTimerId) {
                pollTimerId = setInterval(checkActiveTab, POLL_INTERVAL_MS);
            }
            if (typeof window.__resolveBroadcasterLock === 'function') {
                window.__resolveBroadcasterLock();
            }
        }

        function checkActiveTab() {
            if (!isActiveOwner || superseded) return;
            fetchActiveTabId()
                .then(function(activeId) {
                    if (activeId && activeId !== myTabId) {
                        markAsSuperseded('polling found different active tab: ' + activeId);
                    }
                })
                .catch(function(err) {
                    console.warn('[Broadcaster lock] Poll failed:', err);
                });
        }

        // User confirmed they want this tab to take over from another session.
        function confirmTakeoverHere() {
            claimBroadcasterTab()
                .then(function() {
                    activateAsOwner();
                })
                .catch(function() {
                    alert('Could not take over the broadcaster. Please try again.');
                });
        }

        // On load: claim only if nobody else owns the broadcaster yet.
        function initBroadcasterLock() {
            hideInactiveOverlay();
            hideTakeoverPrompt();

            fetchActiveTabId()
                .then(function(activeId) {
                    if (!activeId) {
                        return claimBroadcasterTab().then(function() {
                            activateAsOwner();
                        });
                    }
                    if (activeId === myTabId) {
                        activateAsOwner();
                        return;
                    }
                    // Another tab/device already owns this live show — ask first.
                    showTakeoverPrompt();
                })
                .catch(function(err) {
                    console.error('[Broadcaster lock] Init failed:', err);
                    if (typeof window.__rejectBroadcasterLock === 'function') {
                        window.__rejectBroadcasterLock(err);
                    }
                });
        }

        if (typeof channel !== 'undefined' && channel && typeof channel.bind === 'function') {
            channel.bind('BroadcasterTabClaimedEvent', function(data) {
                console.log('[Pusher] BroadcasterTabClaimedEvent received:', data, 'myTabId:', myTabId);
                if (data.tab_id === myTabId) return;
                if (data.tab_id !== myTabId) {
                    window.location.href = "about:blank"
                }
                if (isActiveOwner) {
                    markAsSuperseded('pusher event from tab ' + data.tab_id);
                }
            });
        }

        if (localChannel) {
            localChannel.onmessage = function(event) {
                const msg = event && event.data;
                if (!msg || msg.type !== 'TAB_CLAIMED') return;
                if (msg.tabId && msg.tabId !== myTabId && isActiveOwner) {
                    markAsSuperseded('local BroadcastChannel from tab ' + msg.tabId);
                }
            };
        }

        if (takeoverContinue) {
            takeoverContinue.addEventListener('click', confirmTakeoverHere);
        }

        if (takeoverCancel) {
            takeoverCancel.addEventListener('click', function() {
                console.log('[Broadcaster lock] takeoverCancel clicked');
                window.location.href = "about:blank"

                hideTakeoverPrompt();
                if (typeof window.__rejectBroadcasterLock === 'function') {
                    window.__rejectBroadcasterLock('user_cancelled');
                }
            });
        }

        if (useHereBtn) {
            useHereBtn.addEventListener('click', function() {
                claimBroadcasterTab().finally(function() {
                    window.location.reload();
                });
            });
        }

        document.addEventListener('DOMContentLoaded', initBroadcasterLock);
    })();

   
</script> --}}

<script src="https://cdn.jsdelivr.net/npm/nosleep.js@0.12.0/dist/NoSleep.min.js"></script>
<script>
    const noSleep = new NoSleep();
    document.addEventListener('click', () => noSleep.enable(), {
        once: true
    });
</script>


</html>
