<?php
require_once 'inc/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $device_id = $_POST['device_id'] ?? '';
    if ($device_id) {
        $stmt = $db->prepare("UPDATE screens SET last_active = NOW() WHERE device_id = ?");
        $stmt->execute([$device_id]);
        echo json_encode(["status" => "ok"]);
    } else {
        echo json_encode(["status" => "error", "reason" => "device_id boş"]);
    }
} else {
    echo json_encode(["status" => "error", "reason" => "POST bekleniyor"]);
}
