<html>

<head>
    <style>
        #root {
            width: 100vw;
            height: 100vh;
        }
    </style>
</head>


<body>
    <div id="root"></div>
</body>
<script src="https://resource.ZEGOCLOUD.com/prebuilt/crypto-js.js"></script>
<script src="https://resource.ZEGOCLOUD.com/prebuilt/prebuiltToken.js"></script>
<script src="https://unpkg.com/@ZEGOCLOUD/zego-uikit-prebuilt/zego-uikit-prebuilt.js"></script>


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
                    showMirror: true,
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
                    showMirror: true,
                    fillMode: "cover",
                };
            }

            const zp = ZegoUIKitPrebuilt.create(TOKEN);
            zp.joinRoom({
                container: document.querySelector("#root"),
               
                scenario: {
                    mode: ZegoUIKitPrebuilt.LiveStreaming,
                    config: {
                        role
                    },
                },
                // --- ADD THIS SECTION BELOW ---
                onInRoomStateChanged: (state) => {
                    console.log("Connection state:", state);
                    if (state === 'DISCONNECTED') {
                        console.warn("Network timeout (1002099). Reconnecting...");
                        // Short delay to allow network to stabilize, then refresh
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    }
                },
                // ------------------------------
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
    }
</script>


</html>
