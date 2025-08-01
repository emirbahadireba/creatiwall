<?php
require 'inc/db.php';
require_once 'inc/log.php';
require_once 'inc/notifications.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$user_id = $_SESSION['user_id'];

// Log: Dashboard açıldı
log_action($user_id, "Dashboard accessed");

// İstatistikleri çek
$stmt = $db->prepare("SELECT COUNT(*) FROM screens WHERE user_id = ?");
$stmt->execute([$user_id]);
$total_screens = $stmt->fetchColumn();

$stmt = $db->prepare("SELECT COUNT(*) FROM media WHERE user_id = ?");
$stmt->execute([$user_id]);
$total_media = $stmt->fetchColumn();

$stmt = $db->prepare("SELECT COUNT(*) FROM playlists WHERE user_id = ?");
$stmt->execute([$user_id]);
$total_playlists = $stmt->fetchColumn();

$stmt = $db->prepare("SELECT COUNT(DISTINCT sp.screen_id) FROM screen_playlists sp JOIN screens s ON sp.screen_id = s.id WHERE s.user_id = ?");
$stmt->execute([$user_id]);
$total_assigned_playlists = $stmt->fetchColumn();

$stmt = $db->prepare("SELECT COUNT(*) FROM screens WHERE user_id = ? AND last_active IS NOT NULL AND UNIX_TIMESTAMP() - UNIX_TIMESTAMP(last_active) < 120");
$stmt->execute([$user_id]);
$total_online_screens = $stmt->fetchColumn();

$total_unread = get_unread_notifications_count($user_id);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - İstatistikler</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .stats-container {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            margin-top: 40px;
        }
        .stat-box {
            background: #fff;
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
            color: #ff7e29;
        }
        .stat-box p {
            font-size: 18px;
            margin: 0;
        }
        .notification-wrapper {
            position: fixed;
            top: 12px;
            right: 20px;
            z-index: 9999;
            font-family: Arial, sans-serif;
        }
        .notification-icon {
            position: relative;
            cursor: pointer;
            font-size: 24px;
            color: #ff7e29;
        }
        .notification-badge {
            position: absolute;
            top: -6px;
            right: -10px;
            background: red;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 12px;
            font-weight: bold;
            display: <?= $total_unread > 0 ? 'inline-block' : 'none' ?>;
        }
        .notification-list {
            display: none;
            position: absolute;
            right: 0;
            margin-top: 6px;
            width: 300px;
            background: white;
            border: 1px solid #ddd;
            border-radius: 6px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            max-height: 300px;
            overflow-y: auto;
            font-size: 14px;
            color: #333;
            z-index: 10000;
        }
        .notification-list.show {
            display: block;
        }
        .notification-list-item {
            padding: 10px 15px;
            border-bottom: 1px solid #eee;
        }
        .notification-list-item:last-child {
            border-bottom: none;
        }
        .notification-list-item.unread {
            background-color: #fffae6;
            font-weight: bold;
        }
    </style>
</head>
<body>
<?php include 'inc/header.php'; ?>

<div class="notification-wrapper">
  <div class="notification-icon" id="notifIcon">&#128276;
    <span class="notification-badge" id="notifCount"><?= $total_unread ?></span>
  </div>
  <div class="notification-list" id="notifList"></div>
</div>

<div class="main-content">
    <h1>Dashboard</h1>
    <p>Hoşgeldiniz!</p>

    <div class="stats-container">
        <div class="stat-box" onclick="location.href='screens.php'">
            <h2><?= htmlspecialchars($total_screens) ?></h2>
            <p>Kayıtlı Ekran</p>
        </div>
        <div class="stat-box" onclick="location.href='screens.php?filter=online'">
            <h2><?= htmlspecialchars($total_online_screens) ?></h2>
            <p>Online Ekran</p>
        </div>
        <div class="stat-box" onclick="location.href='media.php'">
            <h2><?= htmlspecialchars($total_media) ?></h2>
            <p>Medya Dosyası</p>
        </div>
        <div class="stat-box" onclick="location.href='playlists.php'">
            <h2><?= htmlspecialchars($total_playlists) ?></h2>
            <p>Playlist</p>
        </div>
        <div class="stat-box" onclick="location.href='playlists.php?assigned=1'">
            <h2><?= htmlspecialchars($total_assigned_playlists) ?></h2>
            <p>Atanmış Playlist</p>
        </div>
    </div>
</div>

<script>
const notifIcon = document.getElementById('notifIcon');
const notifList = document.getElementById('notifList');
const notifCount = document.getElementById('notifCount');

notifIcon.addEventListener('click', () => {
  if(notifList.classList.contains('show')) {
    notifList.classList.remove('show');
  } else {
    fetch('fetch_notifications.php')
      .then(response => response.json())
      .then(data => {
        if(data.notifications.length === 0) {
          notifList.innerHTML = '<div class="notification-list-item">Yeni bildirim yok.</div>';
        } else {
          notifList.innerHTML = data.notifications.map(n => 
            `<div class="notification-list-item ${n.is_read == 0 ? 'unread' : ''}">${n.message}<br><small>${n.created_at}</small></div>`
          ).join('');
        }
        notifList.classList.add('show');
        notifCount.style.display = 'none';
      });
  }
});

// Sayfa açıkken belirli aralıklarla yeni bildirim kontrolü
setInterval(() => {
  fetch('fetch_unread_count.php')
    .then(response => response.json())
    .then(data => {
      if(data.count > 0) {
        notifCount.textContent = data.count;
        notifCount.style.display = 'inline-block';
      } else {
        notifCount.style.display = 'none';
      }
    });
}, 30000);
</script>

</body>
</html>
