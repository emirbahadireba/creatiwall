<?php
require 'inc/db.php';

$screen_id = isset($_GET['screen_id']) ? intval($_GET['screen_id']) : 0;
if (!$screen_id) {
    die("Screen ID is required!");
}

$stmt = $db->prepare("SELECT playlist_id FROM screen_playlists WHERE screen_id = ?");
$stmt->execute([$screen_id]);
$playlist_id = $stmt->fetchColumn();

if (!$playlist_id) {
    die("Playlist ID is required!");
}

$stmt = $db->prepare("
    SELECT pi.display_duration, m.filename, m.filetype 
    FROM playlist_items pi
    JOIN media m ON pi.media_id = m.id
    WHERE pi.playlist_id = ?
    ORDER BY pi.order_no ASC
");
$stmt->execute([$playlist_id]);
$media = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$media) {
    die("No media found in playlist.");
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8" />
    <title>Player</title>
    <style>
        html, body {
            height: 100%;
            margin: 0;
            background: #000;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        #container {
            width: 100vw;
            height: 100vh;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            background: #000;
        }
        img, video {
            width: 100%;
            height: 100%;
            object-fit: cover; /* Kırparak tam kaplama */
            position: absolute;
            top: 0;
            left: 0;
        }
    </style>
</head>
<body>
<div id="container"></div>
<script>
const mediaList = <?php echo json_encode($media); ?>;
let idx = 0;
const container = document.getElementById('container');

function showMedia(i) {
    if (!mediaList[i]) { idx = 0; i = 0; } // döngüye al
    const m = mediaList[i];
    container.innerHTML = '';

    if (m.filetype.startsWith('image')) {
        const img = document.createElement('img');
        img.src = 'uploads/' + m.filename;
        container.appendChild(img);
        // display_duration varsa kullan, yoksa 5 saniye
        let duration = parseInt(m.display_duration);
        if (isNaN(duration) || duration < 1) duration = 5;
        setTimeout(() => { idx++; showMedia(idx); }, duration * 1000);
    } else if (m.filetype.startsWith('video')) {
        const vid = document.createElement('video');
        vid.src = 'uploads/' + m.filename;
        vid.autoplay = true;
        vid.controls = false;
        vid.muted = true;
        vid.loop = false;
        vid.style.background = "#000";
        vid.onended = () => { idx++; showMedia(idx); };
        container.appendChild(vid);
        vid.play();
    } else {
        setTimeout(() => { idx++; showMedia(idx); }, 3000);
    }
}

document.addEventListener('keydown', e => {
    if (e.key === 'f') {
        if (document.fullscreenElement) document.exitFullscreen();
        else document.documentElement.requestFullscreen();
    }
});

showMedia(0);
</script>
</body>
</html>
