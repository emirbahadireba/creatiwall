<?php
require 'inc/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo "Unauthorized";
    exit;
}

$user_id = $_SESSION['user_id'];

if (!isset($_POST['notification_id'])) {
    http_response_code(400);
    echo "notification_id required";
    exit;
}

$notification_id = intval($_POST['notification_id']);

$stmt = $db->prepare("UPDATE notifications SET is_read = 1, read_at = NOW() WHERE id = ? AND user_id = ?");
$stmt->execute([$notification_id, $user_id]);

header("Location: notifications.php");
exit;
?>
