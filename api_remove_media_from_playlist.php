<?php
session_start();
require_once 'inc/db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);

$playlist_item_id = intval($data['playlist_item_id'] ?? 0);

if (!$playlist_item_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Playlist Item ID required']);
    exit;
}

// Check ownership of playlist_item via join
$stmt = $db->prepare("
    SELECT pi.id FROM playlist_items pi
    JOIN playlists p ON pi.playlist_id = p.id
    WHERE pi.id = ? AND p.user_id = ?
");
$stmt->execute([$playlist_item_id, $user_id]);

if (!$stmt->fetch()) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

$stmt = $db->prepare("DELETE FROM playlist_items WHERE id = ?");
$stmt->execute([$playlist_item_id]);

echo json_encode(['success' => true]);
