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

$playlist_id = intval($data['playlist_id'] ?? 0);
$media_id = intval($data['media_id'] ?? 0);

if (!$playlist_id || !$media_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Playlist ID and Media ID required']);
    exit;
}

// Check playlist ownership
$stmt = $db->prepare("SELECT id FROM playlists WHERE id = ? AND user_id = ?");
$stmt->execute([$playlist_id, $user_id]);
if (!$stmt->fetch()) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

// Insert item
$stmt = $db->prepare("SELECT MAX(order_no) as max_order FROM playlist_items WHERE playlist_id = ?");
$stmt->execute([$playlist_id]);
$row = $stmt->fetch();
$new_order = ($row['max_order'] ?? 0) + 1;

$stmt = $db->prepare("INSERT INTO playlist_items (playlist_id, media_id, order_no) VALUES (?, ?, ?)");
$stmt->execute([$playlist_id, $media_id, $new_order]);

echo json_encode(['success' => true]);
