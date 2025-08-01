<?php
require '../inc/db.php';
header('Content-Type: application/json');

$playlist_id = isset($_GET['playlist_id']) ? intval($_GET['playlist_id']) : 0;
$device_id = isset($_GET['device_id']) ? $_GET['device_id'] : '';

if ($playlist_id === 0 && empty($device_id)) {
    echo json_encode(['error' => 'playlist_id veya device_id gerekli']);
    exit;
}

try {
    if ($playlist_id) {
        // playlist_id ile medya çek
        $stmt = $db->prepare("
            SELECT m.filename, m.filetype
            FROM playlists p
            JOIN playlist_items pi ON pi.playlist_id = p.id
            JOIN media m ON pi.media_id = m.id
            WHERE p.id = ?
            ORDER BY pi.order_no ASC
        ");
        $stmt->execute([$playlist_id]);
    } else {
        // device_id ile ekran ve playlist bul, sonra medya çek
        $stmt = $db->prepare("
            SELECT sp.playlist_id
            FROM screens s
            JOIN screen_playlists sp ON sp.screen_id = s.id
            WHERE s.device_id = ?
            LIMIT 1
        ");
        $stmt->execute([$device_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            echo json_encode(['error' => 'Ekran veya playlist bulunamadı']);
            exit;
        }
        $pid = $row['playlist_id'];
        $stmt = $db->prepare("
            SELECT m.filename, m.filetype
            FROM playlists p
            JOIN playlist_items pi ON pi.playlist_id = p.id
            JOIN media m ON pi.media_id = m.id
            WHERE p.id = ?
            ORDER BY pi.order_no ASC
        ");
        $stmt->execute([$pid]);
    }

    $media = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['playlist' => $media]);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
