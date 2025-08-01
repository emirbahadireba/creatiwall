<?php
require 'inc/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];

if (!isset($_POST['notification_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'notification_id required']);
    exit;
}

$notification_id = intval($_POST['notification_id']);

try {
    $stmt = $db->prepare("UPDATE notifications SET read_at = NOW() WHERE id = ? AND user_id = ? AND read_at IS NULL");
    $stmt->execute([$notification_id, $user_id]);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
