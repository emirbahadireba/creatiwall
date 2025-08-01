<?php
require_once 'inc/db.php';

function log_action(int $user_id, string $action): void {
    global $db;
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $stmt = $db->prepare("INSERT INTO user_logs (user_id, action, ip_address) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $action, $ip]);
}
