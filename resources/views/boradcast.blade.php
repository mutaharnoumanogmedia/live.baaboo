<!DOCTYPE html>
<html>

<head>
    <title>Broadcast</title>
</head>

<body>
    <h2>Broadcaster</h2>
    <video id="localVideo" autoplay muted playsinline style="width: 480px; background:#000;"></video>
    <button id="startBtn">Start Broadcast</button>

    <script src="https://cdn.livekit.io/livekit-client/2.2.0/livekit-client.min.js"></script>

    <script>
        document.getElementById('startBtn').onclick = async () => {
            const roomId = new URLSearchParams(location.search).get('id') || 'room1';
            const identity = 'broadcaster-' + roomId;

            const res = await fetch(`/api/livekit-token?room=${roomId}&identity=${identity}&role=broadcaster`);
            const {
                token,
                url
            } = await res.json();

            const room = await LiveKit.connect(url, token);

            const stream = await navigator.mediaDevices.getUserMedia({
                video: true,
                audio: true
            });
            document.getElementById('localVideo').srcObject = stream;

            await room.localParticipant.publishTrack(new LiveKit.LocalVideoTrack(stream.getVideoTracks()[0]));
            await room.localParticipant.publishTrack(new LiveKit.LocalAudioTrack(stream.getAudioTracks()[0]));

            console.log("Broadcasting to room:", roomId);
        };
    </script>
</body>

</html>
