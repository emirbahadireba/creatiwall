<?php
require 'inc/db.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['count' => 0]);
    exit;
}

$user_id = $_SESSION['user_id'];

require_once 'inc/notifications.php';

$count = get_unread_notifications_count($user_id);
echo json_encode(['count' => $count]);
