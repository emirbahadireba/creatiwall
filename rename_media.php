<?php
require 'inc/db.php';
require_once 'inc/functions.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$media_id = intval($_POST['id'] ?? 0);
$new_name_raw = trim($_POST['new_name'] ?? '');

if (!$media_id || $new_name_raw === '') {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

$stmt = $db->prepare("SELECT id FROM media WHERE id = ? AND user_id = ?");
$stmt->execute([$media_id, $user_id]);
if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Permission denied or media not found']);
    exit;
}

if (rename_media_file($db, $media_id, $new_name_raw)) {
    // Yeni dosya adını veritabanından al
    $stmt = $db->prepare("SELECT filename FROM media WHERE id = ?");
    $stmt->execute([$media_id]);
    $newFilename = $stmt->fetchColumn();
    echo json_encode(['success' => true, 'new_filename' => $newFilename]);
} else {
    echo json_encode(['success' => false, 'message' => 'Rename failed']);
}
