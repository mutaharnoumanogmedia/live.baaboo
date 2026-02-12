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

        /* .zg_autoplay_mask {
            display: none !important;
        } */

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
