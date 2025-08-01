<?php
session_start();
require_once 'inc/db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['playlist_id'], $data['order']) || !is_array($data['order'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

$playlist_id = intval($data['playlist_id']);
$order = $data['order'];
$user_id = $_SESSION['user_id'];

// Playlist'in kullanıcıya ait olup olmadığını kontrol et
$stmt = $db->prepare("SELECT id FROM playlists WHERE id = ? AND user_id = ?");
$stmt->execute([$playlist_id, $user_id]);
if (!$stmt->fetch()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Forbidden']);
    exit;
}

// Her item için order_no güncelle
try {
    $db->beginTransaction();
    $updateStmt = $db->prepare("UPDATE playlist_items SET order_no = ? WHERE id = ? AND playlist_id = ?");
    foreach ($order as $position => $item_id) {
        $pos = $position + 1;
        $item_id = intval($item_id);
        $updateStmt->execute([$pos, $item_id, $playlist_id]);
    }
    $db->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $db->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'DB error: ' . $e->getMessage()]);
}
