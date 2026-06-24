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
            object-fit: contain !important;
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw !important;
            height: 100vh !important;
             
        }




        .zg_autoplay_mask {
            background: rgba(0, 0, 0, 0.7) !important;
            font-family: -apple-system, system-ui, sans-serif !important;
            font-size: 18px !important;
        }

        .dIzgYQV4CBbzZxzJbwbS,
        #ZegoRoomFooter,
        #ZegoRoomHeader {
            display: none !important;
        }

        .QAHxuJxRZWb3P_cbR8QA {
            display: block !important;
        }


        .zg_autoplay_mask {
            display: none !important;
        }

        .IoC1lj0UQIKqG1pNh5vE,
        #zego_left_notify_wrapper,
        #zego_right_notify_wrapper,
        #ZegoRoomMobileLeaveButton,
        #ZegoRoomHeader,
        #ZegoRoomCssMobileMore,
        #ZegoRoomFooter {
            display: none !important;
        }

        .zg_autoplay_wrapper {
            position: absolute !important;
            top: 50px !important;

        }


        div:has(> .unmuteVideo) {
            display: none !important;
        }

        /* audience-single-host: co-host tiles hidden via data attribute + JS */
        #root [data-audience-hidden-cohost="1"] {
            display: none !important;
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

        // Main host is the only broadcaster shown to the audience; co-hosts are hidden.
        const MAIN_HOST_ZEGO_USER_ID = @json($mainHostZegoUserId);
        const MAIN_HOST_EMAIL = @json($mainHostEmail);
        const hiddenBroadcasterIds = [];

        function shouldHideBroadcaster(user) {
            if (!user || !user.userID) {
                return false;
            }
            if (MAIN_HOST_ZEGO_USER_ID && user.userID === MAIN_HOST_ZEGO_USER_ID) {
                return false;
            }
            if (user.userName && user.userName.indexOf('cohost-') === 0) {
                return true;
            }
            // Any other admin publisher (not the designated main host).
            if (MAIN_HOST_ZEGO_USER_ID && user.userID.indexOf('admin-') === 0) {
                return true;
            }
            return false;
        }

        function registerHiddenBroadcaster(user) {
            if (!shouldHideBroadcaster(user) || hiddenBroadcasterIds.includes(user.userID)) {
                return;
            }
            hiddenBroadcasterIds.push(user.userID);
            syncHiddenBroadcasters();
        }

        function syncHiddenBroadcasters() {
            if (window.__zegoAudienceInstance && ZegoUIKitPrebuilt.core && ZegoUIKitPrebuilt.core._config) {
                ZegoUIKitPrebuilt.core._config.hideUsersById = hiddenBroadcasterIds.slice();
            }
            applyAudienceSingleHostLayout();
        }

        function applyAudienceSingleHostLayout() {
            const root = document.getElementById('root');
            if (!root) {
                return;
            }

            root.querySelectorAll('#ZegoVideoPlayerName').forEach(function(nameEl) {
                const label = (nameEl.textContent || '').trim();
                if (MAIN_HOST_EMAIL && label === MAIN_HOST_EMAIL) {
                    return;
                }
                if (label.indexOf('cohost-') !== 0) {
                    return;
                }

                let node = nameEl;
                for (let depth = 0; depth < 10 && node && node !== root; depth++) {
                    if (node.querySelector && node.querySelector('video')) {
                        node.setAttribute('data-audience-hidden-cohost', '1');
                        break;
                    }
                    node = node.parentElement;
                }
            });
        }

        let audienceLayoutObserver = null;
        function watchAudienceVideoLayout() {
            const root = document.getElementById('root');
            if (!root || audienceLayoutObserver) {
                return;
            }
            audienceLayoutObserver = new MutationObserver(function() {
                syncHiddenBroadcasters();
            });
            audienceLayoutObserver.observe(root, {
                childList: true,
                subtree: true,
            });
        }

        // You can assign different roles based on url parameters.
        let role = ZegoUIKitPrebuilt.Audience;

        config = {
            showTextChat: false,
            showUserList: false,
            turnOnCameraWhenJoining: false,
            showMyCameraToggleButton: false,
            showAudioVideoSettingsButton: false,
            showScreenSharingButton: false,
            showPreJoinView: false,
            showMemberJoinNotice: false,
            showMessageNotification: false,
            showUserJoinAndLeave: false,
            showMyCameraToggleButton: false,
            showMyMicrophoneToggleButton: false,
            showAudioVideoSettingsButton: false,
            showScreenSharingButton: false,
            showTextChat: false, // Disables the chat/message input
            showInRoomMessageButton: false, // Hides the chat icon
            showUserList: false, // Hides the viewer count/list
            language: 'German',
            showMirror: false,
            fillMode: "contain",
        }

        const zp = ZegoUIKitPrebuilt.create(TOKEN);
        window.__zegoAudienceInstance = zp;
        zp.joinRoom({
            container: document.querySelector("#root"),
            videoResolutionList: [ZegoUIKitPrebuilt.VideoResolution_360P],
            videoResolutionDefault: ZegoUIKitPrebuilt.VideoResolution_360P,
            hideUsersById: hiddenBroadcasterIds,
            showNonVideoUser: false,
            scenario: {
                mode: ZegoUIKitPrebuilt.LiveStreaming,
                config: {
                    role: ZegoUIKitPrebuilt.Audience,
                },
            },
            onJoinRoom: function() {
                watchAudienceVideoLayout();
                syncHiddenBroadcasters();
            },
            onUserJoin: function(users) {
                (users || []).forEach(registerHiddenBroadcaster);
            },
            onLiveStart: function(user) {
                if (user) {
                    registerHiddenBroadcaster(user);
                }
                syncHiddenBroadcasters();
            },
            onStreamUpdate: function() {
                syncHiddenBroadcasters();
            },
            sharedLinks: [{
                name: 'Join as an audience',
                url: window.location.origin +
                    window.location.pathname +
                    '?roomID=' +
                    roomID +
                    '&role=Audience',
            }],
            // --- Disable All Notifications ---
            showMemberJoinNotice: false, // Hides "User joined" alerts
            showUserJoinAndLeave: false, // Hides join/leave messages in chat
            showMessageNotification: false, // Hides floating message popups

            // --- Disable All Controls & UI ---
            showTextChat: false, // Disables the chat system entirely
            showInRoomMessageButton: false, // Removes the chat icon/button
            showUserList: false, // Hides the viewer count/list
            showPreJoinView: false, // Skips the "Enter Name" preview screen

            // --- Device Controls (Audience shouldn't have these anyway) ---
            turnOnCameraWhenJoining: false,
            turnOnMicrophoneWhenJoining: false,
            showMyCameraToggleButton: false,
            showMyMicrophoneToggleButton: false,
            showAudioVideoSettingsButton: false,
            showScreenSharingButton: false,
            showLeaveRoomConfirmDialog: false, // Exit immediately without popup
            translateLanguage: 'German',
            // language: 'German',

            innerText: {
                roomLiveNotStarted: "The broadcast hasn't started yet.", // Fixes your specific issue
                roomEmpty: "The host has left the room.",
                userJoin: "{userName} joined the stream",
                userLeave: "{userName} left",
                send: "Send",
                inputMessagePlaceholder: "Type a message...",
                networkNotGood: "Poor network connection",
                networkDisconnected: "Disconnected. Reconnecting...",
                // Add more keys as needed from the list below
            },


        });
    }


    function onZgAutoplayMaskAppeared(callback) {
        let executed = false;
        const observer = new MutationObserver((mutationsList) => {
            if (!executed && document.querySelector('.zg_autoplay_mask')) {
                executed = true;
                callback();
                observer.disconnect();
            }
        });

        // In case it's already on the page
        if (document.querySelector('.zg_autoplay_mask')) {
            executed = true;
            callback();
        } else {
            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
        }
    }

    // Usage: log once when .zg_autoplay_mask appears
    onZgAutoplayMaskAppeared(() => {
        console.log('zg_autoplay_mask appeared');
        document.querySelector('.zg_autoplay_mask').style = 'display: flex !important';
        document.querySelector('.zg_autoplay_mask').querySelectorAll('.zg_autoplay_content')[0].innerHTML =
            '<div style="text-align:center;padding:20px;">' +
            '<div style="font-size:50px;margin-bottom:15px;">🔊</div>' +
            '<div style="font-size:18px;font-weight:500;">Tap or click resume enable audio</div>' +
            '</div>';
    });




    var channel2 = pusher.subscribe('set-broadcast-room-id.{{ $liveShow->id }}');

    // System subscription event
    channel2.bind('pusher:subscription_succeeded', function() {
        console.log('Subscribed message event successfully!');
    });

    // Your Laravel broadcast event (drop the dot)
    channel2.bind('SetBroadcastRoomIdEvent', function(data) {
        window.location.reload();
    });
</script>

</html>
