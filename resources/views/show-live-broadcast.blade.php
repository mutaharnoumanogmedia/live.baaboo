<html>

<head>
    <style>
        body {
            margin: 0;
            padding: 0;
            overflow: hidden;
        }

        #root {
            width: 100vw;
            height: 100vh;
        }

        #root video {
            object-fit: cover;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }

        .zg_autoplay_mask {
            display: none !important;
        }

        .dIzgYQV4CBbzZxzJbwbS,
        #ZegoRoomFooter,
        #ZegoRoomHeader {
            display: none !important;
        }

        .QAHxuJxRZWb3P_cbR8QA {
            display: block !important;
        }
    </style>
</head>


<body>
    <div id="root"></div>
</body>
<script src="https://resource.ZEGOCLOUD.com/prebuilt/crypto-js.js"></script>
<script src="https://resource.ZEGOCLOUD.com/prebuilt/prebuiltToken.js"></script>
<script src="https://unpkg.com/@ZEGOCLOUD/zego-uikit-prebuilt/zego-uikit-prebuilt.js"></script>

<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script>
    Pusher.logToConsole = true;
    var pusher = new Pusher('{{ env('PUSHER_APP_KEY', '2a66d003a7ded9fe567a') }}', {
        cluster: '{{ env('PUSHER_APP_CLUSTER', 'eu') }}',
    });

    window.onload = function() {

        function getUrlParams(url) {
            let urlStr = url.split('?')[1];
            const urlSearchParams = new URLSearchParams(urlStr);
            const result = Object.fromEntries(urlSearchParams.entries());
            return result;
        }



        // Generate a Token by calling a method.
        // @param 1: appID
        // @param 2: serverSecret
        // @param 3: Room ID
        // @param 4: User ID
        // @param 5: Username
        const roomID = "{{ $liveShow->stream_id }}";
        const userID = "{{ session()->getId() }}";
        const userName = "player-{{ session()->getId() }}";
        const appID = {{ env('ZEGO_APP_ID', 1251897065) }};
        const serverSecret = "{{ env('ZEGO_SERVER_SECRET', 'ac4b30ceb3e43b0280c7fa40be34d2ef') }}";
        const TOKEN = generatePrebuiltToken(appID, serverSecret, roomID, userID, userName);


        // You can assign different roles based on url parameters.
        let role = 'Audience';
        role = role === 'Host' ? ZegoUIKitPrebuilt.Host : ZegoUIKitPrebuilt.Audience;
        let config = {}


        if (role === 'Audience') {
            config = {
                showTextChat: false,
                showUserList: false,
                turnOnCameraWhenJoining: false,
                showMyCameraToggleButton: false,
                showAudioVideoSettingsButton: false,
                showScreenSharingButton: false,
                showPreJoinView: false

            }
        }
        const zp = ZegoUIKitPrebuilt.create(TOKEN);
        zp.joinRoom({
            container: document.querySelector("#root"),
            scenario: {
                mode: ZegoUIKitPrebuilt.LiveStreaming,
                config: {
                    role,
                },
            },
            sharedLinks: [{
                name: 'Join as an audience',
                url: window.location.origin +
                    window.location.pathname +
                    '?roomID=' +
                    roomID +
                    '&role=Audience',
            }],
            ...config
        });


        // ============================================
        // SAFARI AUDIO UNMUTE - ROBUST SOLUTION
        // ============================================
        (function() {
            var isSafari = /^((?!chrome|android|crios|fxios).)*safari/i.test(navigator.userAgent) ||
                /iPhone|iPad|iPod/.test(navigator.userAgent) ||
                (navigator.vendor && navigator.vendor.indexOf('Apple') > -1);

            if (!isSafari) return;

            var hasUnmuted = false;
            var maxRetries = 10;
            var retryCount = 0;

            // Core unmute function - tries multiple approaches
            function forceUnmute() {
                var videos = document.querySelectorAll('#root video');
                var unmutedAny = false;

                videos.forEach(function(video) {
                    // Approach 1: Set muted to false
                    if (video.muted) {
                        video.muted = false;
                        unmutedAny = true;
                    }

                    // Approach 2: Set volume to max
                    if (video.volume < 1) {
                        video.volume = 1;
                    }

                    // Approach 3: If paused, try to play
                    if (video.paused) {
                        video.play().catch(function() {});
                    }

                    // Approach 4: Remove muted attribute if present
                    if (video.hasAttribute('muted')) {
                        video.removeAttribute('muted');
                        unmutedAny = true;
                    }
                });

                return unmutedAny || videos.length > 0;
            }

            // Check if audio is actually working
            function isAudioEnabled() {
                var videos = document.querySelectorAll('#root video');
                for (var i = 0; i < videos.length; i++) {
                    if (!videos[i].muted && videos[i].volume > 0) {
                        return true;
                    }
                }
                return false;
            }

            // Retry unmute with exponential backoff
            function retryUnmute() {
                if (hasUnmuted && isAudioEnabled()) return;
                if (retryCount >= maxRetries) return;

                retryCount++;
                forceUnmute();

                if (isAudioEnabled()) {
                    hasUnmuted = true;
                    hideOverlay();
                } else {
                    // Retry with increasing delay: 500ms, 1s, 1.5s, 2s...
                    setTimeout(retryUnmute, Math.min(500 * retryCount, 3000));
                }
            }

            // Watch for new video elements being added
            var observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    mutation.addedNodes.forEach(function(node) {
                        if (node.nodeName === 'VIDEO' || (node.querySelectorAll && node
                                .querySelectorAll('video').length)) {
                            // New video detected, try to unmute after short delay
                            setTimeout(function() {
                                if (!hasUnmuted) {
                                    retryUnmute();
                                }
                            }, 300);
                        }
                    });
                });
            });
            observer.observe(document.querySelector('#root') || document.body, {
                childList: true,
                subtree: true
            });

            // ============================================
            // TOUCH TO UNMUTE OVERLAY (FALLBACK FOR iOS)
            // ============================================
            var style = document.createElement('style');
            style.textContent = [
                '.safari-unmute-overlay{',
                '  position:fixed;inset:0;z-index:9999;',
                '  display:flex;flex-direction:column;align-items:center;justify-content:center;',
                '  background:rgba(0,0,0,0.6);color:#fff;',
                '  font-family:-apple-system,BlinkMacSystemFont,system-ui,sans-serif;',
                '  cursor:pointer;-webkit-tap-highlight-color:transparent;',
                '}',
                '.safari-unmute-overlay .unmute-icon{',
                '  width:80px;height:80px;margin-bottom:20px;',
                '  border:3px solid #fff;border-radius:50%;',
                '  display:flex;align-items:center;justify-content:center;',
                '}',
                '.safari-unmute-overlay .unmute-icon svg{',
                '  width:40px;height:40px;fill:#fff;',
                '}',
                '.safari-unmute-overlay .unmute-text{',
                '  font-size:18px;font-weight:500;',
                '}',
                '.safari-unmute-overlay.hidden{display:none !important;}'
            ].join('');
            document.head.appendChild(style);

            var overlay = document.createElement('div');
            overlay.className = 'safari-unmute-overlay';
            overlay.innerHTML = [
                '<div class="unmute-icon">',
                '  <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">',
                '    <path d="M3 9v6h4l5 5V4L7 9H3zm13.5 3c0-1.77-1.02-3.29-2.5-4.03v8.05c1.48-.73 2.5-2.25 2.5-4.02zM14 3.23v2.06c2.89.86 5 3.54 5 6.71s-2.11 5.85-5 6.71v2.06c4.01-.91 7-4.49 7-8.77s-2.99-7.86-7-8.77z"/>',
                '  </svg>',
                '</div>',
                '<div class="unmute-text">Tap to enable sound</div>'
            ].join('');
            document.body.appendChild(overlay);

            function hideOverlay() {
                overlay.classList.add('hidden');
            }

            function onUserGesture(e) {
                if (e) e.preventDefault();

                // Force unmute on user gesture (required by iOS)
                forceUnmute();
                hasUnmuted = true;

                // Double-check after short delay
                setTimeout(function() {
                    forceUnmute();
                }, 100);

                // Triple-check after longer delay
                setTimeout(function() {
                    forceUnmute();
                }, 500);

                hideOverlay();
                overlay.removeEventListener('click', onUserGesture);
                overlay.removeEventListener('touchend', onUserGesture);
            }

            overlay.addEventListener('click', onUserGesture);
            overlay.addEventListener('touchend', onUserGesture, {
                passive: false
            });

            // ============================================
            // INITIAL UNMUTE ATTEMPTS (may work on desktop Safari)
            // ============================================
            // Try immediately
            setTimeout(forceUnmute, 500);
            setTimeout(forceUnmute, 1000);
            setTimeout(forceUnmute, 2000);

            // Start retry loop
            setTimeout(retryUnmute, 1500);

            // Auto-hide overlay if audio works without user gesture (desktop Safari)
            setTimeout(function() {
                if (isAudioEnabled()) {
                    hasUnmuted = true;
                    hideOverlay();
                }
            }, 3000);

            // Final check - hide overlay if audio is working
            setInterval(function() {
                if (isAudioEnabled() && !overlay.classList.contains('hidden')) {
                    hideOverlay();
                }
            }, 2000);
        })();


    }
    var channel2 = pusher.subscribe('set-broadcast-room-id.{{ $liveShow->id }}');

    // System subscription event
    channel2.bind('pusher:subscription_succeeded', function() {
        console.log('Subscribed message event successfully!');
    });

    // Your Laravel broadcast event (drop the dot)
    channel2.bind('SetBroadcastRoomIdEvent', function(data) {
        window.location.reload();
    });

    // Safari: show "Touch to unmute" overlay (when page is opened directly)

    // (function() {
    //     // var isSafari = /^((?!chrome|android|crios|fxios).)*safari/i.test(navigator.userAgent) ||
    //     //     /iPhone|iPad|iPod/.test(navigator.userAgent) ||
    //     //     (navigator.vendor && navigator.vendor.indexOf('Apple') > -1);
    //     // if (!isSafari) return;

    //     var style = document.createElement('style');
    //     style.textContent = [
    //         '.safari-unmute-overlay{',
    //         '  position:fixed;inset:0;z-index:9999;',
    //         '  display:flex;align-items:center;justify-content:center;',
    //         '  background:rgba(0,0,0,0.5);color:#fff;',
    //         '  font-family:system-ui,sans-serif;font-size:1rem;cursor:pointer;',
    //         '}',
    //         '.safari-unmute-overlay.hidden{display:none !important;}'
    //     ].join('');
    //     document.head.appendChild(style);

    //     var overlay = document.createElement('div');
    //     overlay.className = 'safari-unmute-overlay';
    //     overlay.setAttribute('aria-label', 'Touch to unmute');
    //     overlay.textContent = 'Touch to unmute';
    //     document.body.appendChild(overlay);

    //     function unmuteAndHide() {
    //         document.querySelectorAll('#root video').forEach(function(v) {
    //             if (v.muted) v.muted = false;
    //         });
    //         overlay.classList.add('hidden');
    //         overlay.removeEventListener('click', unmuteAndHide);
    //         overlay.removeEventListener('touchend', unmuteAndHide);
    //     }

    //     overlay.addEventListener('click', unmuteAndHide);
    //     overlay.addEventListener('touchend', function(e) {
    //         e.preventDefault();
    //         unmuteAndHide();
    //     }, { passive: false });
    // })();
</script>

</html>
