<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
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
    </style>
</head>


<body>
    <div id="page-qr-wrapper">
        <div id="page-qr-code"></div>
        <div id="page-qr-label">Scan to open the same broadcasting panel in your phone</div>
    </div>

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


<div id="fullscreen-go-live-overlay" style="
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
    </button>
</div>
<script>
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
</script>

{{--
    Full-screen "this broadcaster tab is no longer active" overlay.
    Hidden by default. Shown when single-broadcaster lock JS detects that
    a newer tab has claimed this live show (locally via BroadcastChannel
    or globally via Pusher / polling). See the script block further down
    that wires this up.
--}}
<div id="broadcasterInactiveOverlay" role="dialog" aria-live="assertive" aria-hidden="true">
    <div class="card">
        <div class="icon"><i class="fas fa-tv"></i></div>
        <h3>Broadcaster opened elsewhere</h3>
        <p>
            This live show broadcaster has been opened in another tab or on another device.
            Only the most recently opened broadcaster can stream at a time.
        </p>
        <button id="broadcasterUseHereBtn" type="button">
            Use this tab instead
        </button>
    </div>
</div>
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

        const liveShowMediaHiddenUrl = '{{ route('admin.live-shows.media-hidden', $liveShow->id) }}';
        const liveShowMediaPlayedUrl = '{{ route('admin.live-shows.media-played', $liveShow->id) }}';
        const csrfToken = '{{ csrf_token() }}';

        // Overlay state, mutable from the UI controls.
        const overlayState = {
            visible: false,
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

            // Canvas where camera + overlay get composited every frame.
            const canvas = document.createElement('canvas');
            canvas.width = CANVAS_WIDTH;
            canvas.height = CANVAS_HEIGHT;
            const ctx = canvas.getContext('2d');

            function drawFrame() {
                if (cameraVideoEl.readyState >= 2 && cameraVideoEl.videoWidth > 0) {
                    // Draw camera using "cover" semantics so it always fills.
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

                if (overlayState.visible &&
                    !overlayVideoEl.paused &&
                    overlayVideoEl.readyState >= 2 &&
                    overlayVideoEl.videoWidth > 0) {

                    const aspect = overlayVideoEl.videoWidth / overlayVideoEl.videoHeight;

                    let ow, oh, ox, oy;
                    if (overlayState.position === 'fullscreen') {
                        // Fit (letterbox) the URL video inside canvas
                        const sc = Math.min(canvas.width / overlayVideoEl.videoWidth,
                            canvas.height / overlayVideoEl.videoHeight);
                        ow = overlayVideoEl.videoWidth * sc;
                        oh = overlayVideoEl.videoHeight * sc;
                        ox = (canvas.width - ow) / 2;
                        oy = (canvas.height - oh) / 2;
                        // Black bars
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
                        // Soft drop shadow for legibility
                        ctx.save();
                        ctx.shadowColor = 'rgba(0,0,0,0.55)';
                        ctx.shadowBlur = 18;
                        ctx.shadowOffsetY = 4;
                        ctx.fillStyle = '#000';
                        ctx.fillRect(ox - 2, oy - 2, ow + 4, oh + 4);
                        ctx.restore();
                    }

                    ctx.drawImage(overlayVideoEl, ox, oy, ow, oh);
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
                if (overlayState.visible) {
                    try {
                        overlayVideoEl.play().catch(() => {});
                    } catch (_) {}
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
                canvas,
                audioContext,
                overlayGain,
                micGain,
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
                const v = pipeline.overlayVideoEl;

                v.loop = !!opts.loop;
                v.muted = !!opts.muted;

                // Resume audio context (browsers require a user gesture).
                if (pipeline.audioContext.state === 'suspended') {
                    pipeline.audioContext.resume().catch(() => {});
                }

                if (!v.muted) pipeline.ensureOverlayAudioWired();

                v.src = url;
                v.load();
                v.play().then(() => {
                    overlayState.visible = true;
                    if (opts.position) overlayState.position = opts.position;
                    if (typeof opts.size === 'number') overlayState.size = opts.size;
                }).catch(err => {
                    console.error('[Overlay] play failed:', err);
                    window.dispatchEvent(new CustomEvent('broadcast-overlay-error', {
                        detail: err
                    }));
                });
            },

            stop() {
                if (!pipeline) return;
                pipeline.overlayVideoEl.pause();
                try {
                    pipeline.overlayVideoEl.removeAttribute('src');
                    pipeline.overlayVideoEl.load();
                } catch (_) {}
                overlayState.visible = false;
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

<script>
    window.onload = async function() {

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
        const roomID = "{{ time() . rand(1000, 9999) }}";

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
                videoResolutionList: [ZegoUIKitPrebuilt.VideoResolution_540P],
                videoResolutionDefault: ZegoUIKitPrebuilt.VideoResolution_540P,


                scenario: {
                    mode: ZegoUIKitPrebuilt.LiveStreaming,
                    config: {
                        role
                    },
                },

                onLiveStart: (user) => {
                    console.log("Success! The stream has officially started.");
                    console.log("Broadcasting user details:", user);
                    // alert('cx`ontinue');
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
    const playBtn = $('overlay-play');
    const stopBtn = $('overlay-stop');
    const status = $('overlay-status');
    const mediaList = $('overlay-media-list');
    const mediaRefresh = $('overlay-media-refresh');

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
            const res = await fetch(ATTACHED_MEDIA_URL + '?type=video', {
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
                    '<div id="overlay-media-empty">No video media attached to this live show.</div>';
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

    function refreshReadyState() {
        const ready = window.BroadcastOverlay && window.BroadcastOverlay.isReady();
        playBtn.disabled = !ready;
        stopBtn.disabled = !ready;
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
        setStatus('Loading ' + label + '…');
        window.BroadcastOverlay.play(url, {
            position: posSel.value,
            size: parseFloat(sizeSel.value),
            loop: loopChk.checked,
            muted: muteChk.checked,
        });
        setTimeout(() => setStatus('Playing: ' + label, '#86efac'), 600);

    });

    stopBtn.addEventListener('click', () => {
        if (window.BroadcastOverlay) window.BroadcastOverlay.stop();
        setStatus('Overlay stopped.');
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
            window.BroadcastOverlay.play(data.url, {
                position: 'fullscreen',
                size: 1,
                loop: false,
                muted: false,
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
    ─────────────────────────────────────────────────────────────────────
    Single-broadcaster lock
    ─────────────────────────────────────────────────────────────────────
    Only ONE browser tab (across any browser, any device) is allowed to act
    as the broadcaster for a given live show at any time. The newest tab
    always wins; older tabs are kicked into a blocking overlay and their
    streaming pipeline is torn down.

    How it works:
      1.  This tab generates a unique `myTabId` at page load.
      2.  It POSTs that id to `…/claim-tab`, which writes it into
          `live_shows.host_browser_tab` on the server (latest wins) and
          fires a Pusher `BroadcasterTabClaimedEvent` on the existing
          `live-show.{id}` channel.
      3.  Every open broadcaster tab listens for that event. If the event's
          `tab_id` does not match the local `myTabId`, that tab has been
          superseded → tear down Zego + media overlay + Pusher, and show
          the full-screen "Broadcaster opened elsewhere" overlay.
      4.  A local same-browser `BroadcastChannel` mirror gives instant
          feedback between tabs of the same browser without waiting for a
          Pusher round-trip.
      5.  A 15-second polling fallback hits `…/active-tab` so we still kick
          out throttled / background tabs that may have missed the Pusher
          message.
      6.  Clicking "Use this tab instead" simply reclaims and reloads, so
          a fresh Zego session starts cleanly in this tab.
--}}
<script>
    (function () {
        // ── Configuration coming from blade ────────────────────────────
        const LIVE_SHOW_ID   = {{ $liveShow->id }};
        const CLAIM_URL      = '{{ route('admin.live-shows.stream-management.broadcaster.claim-tab', ['id' => $liveShow->id]) }}';
        const ACTIVE_TAB_URL = '{{ route('admin.live-shows.stream-management.broadcaster.active-tab', ['id' => $liveShow->id]) }}';
        const CSRF_TOKEN     = '{{ csrf_token() }}';
        const POLL_INTERVAL_MS = 15000;

        // Unique id for this browser tab. Same scheme used elsewhere in
        // the app (see resources/views/live-show.blade.php).
        const myTabId = Date.now() + '-' + Math.random().toString(36).substr(2, 9);
        window.__broadcasterTabId = myTabId;

        // Same-origin / same-browser side channel for instant supersede
        // notifications. Falls back to no-op if the browser is old.
        const localChannelKey = 'broadcaster_active_tab_' + LIVE_SHOW_ID;
        const localChannel = (typeof BroadcastChannel !== 'undefined')
            ? new BroadcastChannel(localChannelKey)
            : null;

        const inactiveOverlay = document.getElementById('broadcasterInactiveOverlay');
        const useHereBtn      = document.getElementById('broadcasterUseHereBtn');

        let superseded   = false; // true once another tab has taken over
        let pollTimerId  = null;

        // ── Helpers ────────────────────────────────────────────────────

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

        // Best-effort tear down of everything that produces audio/video so
        // that an obsolete tab cannot keep broadcasting in the background.
        function teardownBroadcastingPipeline() {
            // 1) Stop the canvas / overlay video pipeline that mixes the
            //    extra media into the host's stream.
            try {
                if (window.BroadcastOverlay && typeof window.BroadcastOverlay.stop === 'function') {
                    window.BroadcastOverlay.stop();
                }
            } catch (e) { console.warn('Overlay stop failed:', e); }

            // 2) Ask Zego to leave / destroy. The prebuilt SDK exposes a
            //    `destroy()` method on the instance we created in window.onload.
            try {
                const zp = window.__zegoInstance;
                if (zp) {
                    if (typeof zp.destroy === 'function') zp.destroy();
                    else if (typeof zp.leaveRoom === 'function') zp.leaveRoom();
                }
            } catch (e) { console.warn('Zego teardown failed:', e); }

            // 3) Forcefully stop every active getUserMedia track so the
            //    camera / mic indicators turn off.
            try {
                document.querySelectorAll('video, audio').forEach(function (el) {
                    const stream = el.srcObject;
                    if (stream && typeof stream.getTracks === 'function') {
                        stream.getTracks().forEach(function (t) { try { t.stop(); } catch (_) {} });
                    }
                    try { el.pause(); } catch (_) {}
                    el.srcObject = null;
                });
            } catch (e) { console.warn('Media element cleanup failed:', e); }

            // 4) Disconnect the Pusher socket from this tab.
            try {
                if (typeof pusher !== 'undefined' && pusher && pusher.disconnect) {
                    pusher.disconnect();
                }
            } catch (e) { console.warn('Pusher disconnect failed:', e); }
        }

        // Mark this tab as inactive and visually block further use.
        function markAsSuperseded(reason) {
            if (superseded) return; // already handled
            superseded = true;
            console.warn('[Broadcaster lock] Tab superseded:', reason || '(no reason)');
            teardownBroadcastingPipeline();
            showInactiveOverlay();

            // Stop polling – we're no longer the owner and there's nothing
            // useful to learn until the user clicks "Use this tab".
            if (pollTimerId) { clearInterval(pollTimerId); pollTimerId = null; }

            // If this tab was opened via window.open() from another window,
            // browsers allow window.close(). If not, this call is a no-op
            // (we still have the blocking overlay so the user can't keep
            // broadcasting).
            try { window.close(); } catch (_) {}
        }

        // POST our tab id to the server, becoming the new owner.
        function claimBroadcasterTab() {
            return fetch(CLAIM_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ tab_id: myTabId }),
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                console.log('[Broadcaster lock] Claimed:', data);
                // Local mirror so any other tab in the same browser
                // immediately knows it is no longer the active one.
                if (localChannel) {
                    localChannel.postMessage({ type: 'TAB_CLAIMED', tabId: myTabId });
                }
                return data;
            })
            .catch(function (err) {
                console.error('[Broadcaster lock] Claim failed:', err);
            });
        }

        // Polling fallback in case the Pusher event is missed (background
        // throttling, disconnected socket, etc.).
        function checkActiveTab() {
            if (superseded) return;
            fetch(ACTIVE_TAB_URL, { headers: { 'Accept': 'application/json' } })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (!data || !data.active_tab_id) return;
                    if (data.active_tab_id !== myTabId) {
                        markAsSuperseded('polling found different active tab: ' + data.active_tab_id);
                    }
                })
                .catch(function (err) {
                    // Network blips shouldn't kick us out; just log.
                    console.warn('[Broadcaster lock] Poll failed:', err);
                });
        }

        // ── Listen for the Pusher event from any other tab/device ─────
        // The Pusher `channel` and `pusher` globals are created earlier in
        // this view (in the previous <script> block).
        if (typeof channel !== 'undefined' && channel && typeof channel.bind === 'function') {
            channel.bind('BroadcasterTabClaimedEvent', function (data) {
                if (!data || !data.tab_id) return;
                if (data.tab_id !== myTabId) {
                    markAsSuperseded('pusher event from tab ' + data.tab_id);
                }
            });
        }

        // ── Same-browser instant supersede via BroadcastChannel ───────
        if (localChannel) {
            localChannel.onmessage = function (event) {
                const msg = event && event.data;
                if (!msg || msg.type !== 'TAB_CLAIMED') return;
                if (msg.tabId && msg.tabId !== myTabId) {
                    markAsSuperseded('local BroadcastChannel from tab ' + msg.tabId);
                }
            };
        }

        // ── "Use this tab instead" button ─────────────────────────────
        if (useHereBtn) {
            useHereBtn.addEventListener('click', function () {
                // Reclaim, then hard reload so a fresh Zego session starts
                // in this tab cleanly (the previous one was torn down).
                claimBroadcasterTab().finally(function () {
                    window.location.reload();
                });
            });
        }

        // ── Kick everything off ───────────────────────────────────────
        document.addEventListener('DOMContentLoaded', function () {
            hideInactiveOverlay();
            claimBroadcasterTab();
            // Start polling shortly after claim so old tabs still using
            // this URL get evicted even without Pusher.
            pollTimerId = setInterval(checkActiveTab, POLL_INTERVAL_MS);
        });
    })();
</script>

<script src="https://cdn.jsdelivr.net/npm/nosleep.js@0.12.0/dist/NoSleep.min.js"></script>
<script>
    const noSleep = new NoSleep();
    document.addEventListener('click', () => noSleep.enable(), {
        once: true
    });
</script>


</html>
