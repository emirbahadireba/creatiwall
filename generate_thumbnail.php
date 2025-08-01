<?php
require 'inc/db.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit('Unauthorized');
}

if (!isset($_GET['media_id'])) {
    http_response_code(400);
    exit('media_id required');
}

$media_id = intval($_GET['media_id']);
$user_id = $_SESSION['user_id'];

// Medya bilgisini al
$stmt = $db->prepare("SELECT filename FROM media WHERE id = ? AND user_id = ?");
$stmt->execute([$media_id, $user_id]);
$media = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$media) {
    http_response_code(404);
    exit('Media not found');
}

$video_path = "uploads/" . $media['filename'];
$thumbnail_name = uniqid() . ".jpg";
$thumbnail_path = "uploads/thumbnails/" . $thumbnail_name;

$cmd = "ffmpeg -i " . escapeshellarg($video_path) . " -ss 00:00:01 -vframes 1 " . escapeshellarg($thumbnail_path);
exec($cmd, $output, $return_var);

if ($return_var === 0 && file_exists($thumbnail_path)) {
    // Veritabanını güncelle
    $stmt = $db->prepare("UPDATE media SET thumbnail = ? WHERE id = ? AND user_id = ?");
    $stmt->execute([$thumbnail_name, $media_id, $user_id]);
    echo "Thumbnail created: $thumbnail_name";
} else {
    http_response_code(500);
    echo "Thumbnail creation failed";
}
