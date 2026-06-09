<!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Badabing Gewinnbestätigung</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    html, body { width: 100%; height: 100%; overflow: hidden; background: #fff; }
    iframe { width: 100%; height: 100vh; border: 0; display: block; }
    #error {
      display: none;
      font-family: Arial, sans-serif;
      padding: 32px;
      max-width: 640px;
      margin: 0 auto;
      color: #c81e4a;
    }
  </style>
</head>
<body>
  <div id="error">
    <h2>Ungültiger Link</h2>
    <p>Bitte verwende den vollständigen Link aus der E-Mail.</p>
  </div>

  <script>
    const SCRIPT_URL =
      'https://script.google.com/macros/s/AKfycbxjD7WfIAb0l92JVE148D-8HYmjSv1CQG9tWQogHDQG8AtyJkV5umevsoz7_H2-iVCm/exec';

    const params = new URLSearchParams(window.location.search);
    const token = params.get('t');

    if (token && /^[a-f0-9]{40,}$/i.test(token)) {
      const frame = document.createElement('iframe');
      frame.src = SCRIPT_URL + '?t=' + encodeURIComponent(token);
      frame.allow = 'clipboard-write';
      document.body.appendChild(frame);
    } else {
      document.getElementById('error').style.display = 'block';
    }
  </script>
</body>
</html>