// admin.php
<?php
session_start();
require 'inc/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'king') {
    header('Location: login.php');
    exit;
}

$total_users = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_screens = $db->query("SELECT COUNT(*) FROM screens")->fetchColumn();
$total_media = $db->query("SELECT COUNT(*) FROM media")->fetchColumn();
$total_playlists = $db->query("SELECT COUNT(*) FROM playlists")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8" />
    <title>King Dashboard</title>
    <link rel="stylesheet" href="css/style.css" />
    <style>
        .main-content {
            margin-left: 220px;
            padding: 40px 30px;
            background: #f8fafc;
            min-height: 100vh;
            box-sizing: border-box;
        }
        .stats-container {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            margin-top: 40px;
        }
        .stat-box {
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 8px rgba(0,0,0,0.1);
            flex: 1 1 200px;
            padding: 30px 20px;
            text-align: center;
            color: #333;
            cursor: pointer;
            transition: transform 0.2s ease-in-out;
        }
        .stat-box:hover {
            transform: scale(1.05);
            box-shadow: 0 0 12px rgba(0,0,0,0.2);
        }
        .stat-box h2 {
            font-size: 40px;
            margin: 0 0 10px;
            color: #e67e22;
        }
        .stat-box p {
            font-size: 18px;
            margin: 0;
        }
    </style>
</head>
<body>
<?php include 'inc/header_king.php'; ?>

<div class="main-content">
    <h1>King Dashboard</h1>
    <div class="stats-container">
        <div class="stat-box" onclick="location.href='admin_users.php'">
            <h2><?= $total_users ?></h2>
            <p>Kullanıcılar</p>
        </div>
        <div class="stat-box" onclick="location.href='admin_screens.php'">
            <h2><?= $total_screens ?></h2>
            <p>Ekranlar</p>
        </div>
        <div class="stat-box" onclick="location.href='admin_media.php'">
            <h2><?= $total_media ?></h2>
            <p>Medya Dosyaları</p>
        </div>
        <div class="stat-box" onclick="location.href='admin_playlists.php'">
            <h2><?= $total_playlists ?></h2>
            <p>Playlistler</p>
        </div>
    </div>
</div>
</body>
</html>
