<?php
require 'inc/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];

if (isset($_GET['mark_read'])) {
    // Bildirim okundu olarak işaretle
    $notif_id = intval($_GET['mark_read']);
    $stmt = $db->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
    $stmt->execute([$notif_id, $user_id]);
    echo json_encode(['success' => true]);
    exit;
}

// Bildirimleri çek
$stmt = $db->prepare("SELECT id, message, created_at, is_read FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 20");
$stmt->execute([$user_id]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['notifications' => $notifications]);
