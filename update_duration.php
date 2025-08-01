<?php
session_start();
require_once 'inc/db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['id'], $data['duration'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

$playlist_item_id = intval($data['id']);
$duration = intval($data['duration']);
$user_id = $_SESSION['user_id'];

// Güvenlik kontrolü: Playlist öğesi gerçekten kullanıcıya ait mi?
$stmt = $db->prepare("SELECT pi.id FROM playlist_items pi JOIN playlists p ON pi.playlist_id = p.id WHERE pi.id = ? AND p.user_id = ?");
$stmt->execute([$playlist_item_id, $user_id]);
if (!$stmt->fetch()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Forbidden']);
    exit;
}

// Güncelle
$stmt = $db->prepare("UPDATE playlist_items SET display_duration = ? WHERE id = ?");
if ($stmt->execute([$duration, $playlist_item_id])) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'DB error']);
}
