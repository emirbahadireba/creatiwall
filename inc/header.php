<?php
// inc/header.php
// NOT: session_start() sayfada önceden çağrılmış olmalı

$current_page = basename($_SERVER['PHP_SELF']);
$user_role = $_SESSION['role'] ?? 'viewer';
$user_email = $_SESSION['email'] ?? 'Anonim';

// Bildirim sayısını çekmek için fonksiyon
function get_unread_notification_count() {
    global $db;
    if (!isset($_SESSION['user_id'])) return 0;
    $stmt = $db->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->execute([$_SESSION['user_id']]);
    return (int)$stmt->fetchColumn();
}

$unread_count = get_unread_notification_count();
?>
<style>
.sidebar {
    background-color: #ffc000; /* arka plan rengi */
    height: 100vh;
    position: fixed;
    width: 220px;
    padding-top: 20px;
    box-sizing: border-box;
}
.logo {
    text-align: center;
    margin-bottom: 30px;
}
.logo img {
    max-width: 180px;
    height: auto;
}
.sidebar ul {
    list-style: none;
    padding-left: 0;
    margin: 0;
}
.sidebar ul li {
    margin: 12px 0;
}
.sidebar ul li a {
    color: #212121;
    text-decoration: none;
    padding: 10px 20px;
    display: block;
    font-weight: 600;
    position: relative;
    border-radius: 4px;
    transition: background-color 0.2s ease;
}
.sidebar ul li a:hover,
.sidebar ul li a.active {
    background-color: #ff7e29;
    color: white;
}
.notification-count {
    background: #c0392b;
    color: white;
    font-size: 12px;
    padding: 2px 6px;
    border-radius: 12px;
    margin-left: 6px;
    vertical-align: middle;
}
.user-info {
    position: fixed;
    top: 10px;
    right: 10px;
    background: #ff7e29;
    color: white;
    padding: 8px 14px;
    border-radius: 4px;
    font-weight: bold;
    font-family: Arial, sans-serif;
    z-index: 9999;
    text-align: right;
    line-height: 1.3;
}
.user-email {
    font-size: 14px;
    margin-bottom: 2px;
    opacity: 0.9;
}
.user-role {
    font-size: 16px;
}
</style>

<div class="sidebar">
    <div class="logo">
        <img src="uploads/creatiwall dolgulu beyaz.png" alt="CreatiWall Logo">
    </div>
    <ul>
        <li><a href="dashboard.php" class="<?= $current_page === 'dashboard.php' ? 'active' : '' ?>">Dashboard</a></li>
        <li><a href="media.php" class="<?= $current_page === 'media.php' ? 'active' : '' ?>">Media</a></li>
        <li><a href="screens.php" class="<?= $current_page === 'screens.php' ? 'active' : '' ?>">Screens</a></li>
        <li><a href="playlists.php" class="<?= $current_page === 'playlists.php' ? 'active' : '' ?>">Playlists</a></li>
        <li>
            <a href="notifications.php" class="<?= $current_page === 'notifications.php' ? 'active' : '' ?>">
                Bildirimler
                <?php if ($unread_count > 0): ?>
                    <span class="notification-count"><?= $unread_count ?></span>
                <?php endif; ?>
            </a>
        </li>
        <li><a href="profile.php" class="<?= $current_page === 'profile.php' ? 'active' : '' ?>">Profilim</a></li>
        <?php if ($user_role === 'king'): ?>
            <li><a href="admin.php" class="<?= $current_page === 'admin.php' ? 'active' : '' ?>">Admin Paneli</a></li>
        <?php endif; ?>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</div>

<div class="user-info">
    <div class="user-email"><?= htmlspecialchars($user_email) ?></div>
    <div class="user-role">Rol: <?= htmlspecialchars(ucfirst($user_role)) ?></div>
</div>
