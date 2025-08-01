// inc/header_king.php
<?php
// session_start() burada olmamalı, her sayfada ayrı çağrılacak

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'king') {
    header("Location: login.php");
    exit;
}

$user_email = $_SESSION['email'] ?? 'Anonim';
$user_role = $_SESSION['role'] ?? '';
?>

<style>
    body { margin: 0; font-family: Arial, sans-serif; background: #f8fafc; }
    .sidebar {
        width: 220px;
        background-color: #2c3e50;
        height: 100vh;
        position: fixed;
        top: 0; left: 0;
        padding: 20px 0;
        color: white;
        box-sizing: border-box;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .sidebar .logo {
        font-weight: bold;
        font-size: 1.8em;
        text-align: center;
        color: #e67e22;
        margin-bottom: 30px;
    }
    .sidebar ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .sidebar ul li {
        margin-bottom: 10px;
    }
    .sidebar ul li a {
        text-decoration: none;
        color: #bdc3c7;
        padding: 12px 20px;
        display: block;
        border-left: 4px solid transparent;
        transition: background 0.3s, border-color 0.3s;
    }
    .sidebar ul li a.active, .sidebar ul li a:hover {
        background-color: #34495e;
        color: #ecf0f1;
        border-left-color: #e67e22;
    }
    .user-info {
        padding: 0 20px 20px 20px;
        border-top: 1px solid #34495e;
        font-size: 14px;
        color: #ecf0f1;
    }
    .user-info strong {
        display: block;
        margin-bottom: 4px;
    }
</style>

<div class="sidebar">
    <div>
        <div class="logo">🟧 CreatiWall King</div>
        <ul>
            <li><a href="admin.php" class="<?= basename($_SERVER['PHP_SELF'])==='admin.php' ? 'active' : '' ?>">Dashboard</a></li>
            <li><a href="admin_users.php" class="<?= basename($_SERVER['PHP_SELF'])==='admin_users.php' ? 'active' : '' ?>">Kullanıcılar</a></li>
            <li><a href="admin_screens.php" class="<?= basename($_SERVER['PHP_SELF'])==='admin_screens.php' ? 'active' : '' ?>">Ekranlar</a></li>
            <li><a href="admin_media.php" class="<?= basename($_SERVER['PHP_SELF'])==='admin_media.php' ? 'active' : '' ?>">Medya</a></li>
            <li><a href="admin_playlists.php" class="<?= basename($_SERVER['PHP_SELF'])==='admin_playlists.php' ? 'active' : '' ?>">Playlistler</a></li>
            <li><a href="admin_usage.php" class="<?= basename($_SERVER['PHP_SELF'])==='admin_usage.php' ? 'active' : '' ?>">Veri Kullanımı</a></li>
            <li><a href="admin_notify.php" class="<?= $current_page === 'admin_notify.php' ? 'active' : '' ?>">Bildirim Gönder</a></li>
            <li><a href="profile.php" class="<?= $current_page === 'profile.php' ? 'active' : '' ?>">Profilim</a></li>
            <li><a href="admin_logs.php" class="<?= $current_page === 'admin_logs.php' ? 'active' : '' ?>">Loglar</a></li>
            <li><a href="logout.php">Çıkış Yap</a></li>
        </ul>
    </div>
    <div class="user-info">
        <strong><?= htmlspecialchars($user_email) ?></strong>
        <span><?= htmlspecialchars(strtoupper($user_role)) ?></span>
    </div>
</div>
