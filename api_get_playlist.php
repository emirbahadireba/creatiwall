<?php
session_start();
require_once 'inc/db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode([]);
    exit;
}

$user_id = $_SESSION['user_id'];
$playlist_id = isset($_GET['playlist_id']) ? intval($_GET['playlist_id']) : 0;

if (!$playlist_id) {
    echo json_encode([]);
    exit;
}

// Check playlist ownership
$stmt = $db->prepare("SELECT id FROM playlists WHERE id = ? AND user_id = ?");
$stmt->execute([$playlist_id, $user_id]);
if (!$stmt->fetch()) {
    echo json_encode([]);
    exit;
}

// Get playlist items
$stmt = $db->prepare("SELECT pi.id, m.filename, m.filetype FROM playlist_items pi JOIN media m ON pi.media_id = m.id WHERE pi.playlist_id = ? ORDER BY pi.order_no ASC");
$stmt->execute([$playlist_id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($items);
