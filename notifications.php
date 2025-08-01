<?php
require 'inc/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

require_once 'inc/notifications.php';

// Bildirim silme işlemi
$delete_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_notification_id'])) {
    $notif_id = intval($_POST['delete_notification_id']);
    // Güvenlik kontrolü: bildirim bu kullanıcıya mı ait?
    $stmt = $db->prepare("SELECT id FROM notifications WHERE id = ? AND user_id = ?");
    $stmt->execute([$notif_id, $user_id]);
    if ($stmt->fetch()) {
        $del_stmt = $db->prepare("DELETE FROM notifications WHERE id = ?");
        if ($del_stmt->execute([$notif_id])) {
            $delete_msg = "Bildirim başarıyla silindi.";
        } else {
            $delete_msg = "Bildirim silinirken hata oluştu.";
        }
    } else {
        $delete_msg = "Bildirim bulunamadı veya silme yetkiniz yok.";
    }
}

$notifications = get_notifications($user_id);

?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8" />
    <title>Bildirimler</title>
    <link rel="stylesheet" href="css/style.css" />
    <script>
        function confirmDelete() {
            return confirm('Bu bildirimi silmek istediğinize emin misiniz?');
        }
    </script>
</head>
<body>
<?php include 'inc/header.php'; ?>

<div class="main-content">
    <h1>Bildirimler</h1>

    <?php if ($delete_msg): ?>
        <p style="color: green; font-weight: bold;"><?= htmlspecialchars($delete_msg) ?></p>
    <?php endif; ?>

    <?php if (empty($notifications)): ?>
        <p>Bildirim yok.</p>
    <?php else: ?>
        <ul style="list-style:none; padding:0;">
            <?php foreach ($notifications as $n): ?>
                <li style="background: <?= $n['is_read'] ? '#f0f0f0' : '#fff9c4' ?>; padding: 10px; margin-bottom: 8px; display: flex; justify-content: space-between; align-items: center;">
                    <form action="mark_read.php" method="post" style="flex-grow: 1; margin-right: 10px;">
                        <input type="hidden" name="notification_id" value="<?= $n['id'] ?>">
                        <button type="submit" style="background:none; border:none; padding:0; font-size:16px; cursor:pointer; text-align:left; width:100%;">
                            <?= htmlspecialchars($n['message']) ?>
                            <br>
                            <small><?= $n['created_at'] ?></small>
                        </button>
                    </form>
                    <form action="" method="post" onsubmit="return confirmDelete();" style="margin:0;">
                        <input type="hidden" name="delete_notification_id" value="<?= $n['id'] ?>">
                        <button type="submit" style="background:#c0392b; color:#fff; border:none; border-radius:4px; padding:5px 10px; cursor:pointer;">Sil</button>
                    </form>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>
</body>
</html>
