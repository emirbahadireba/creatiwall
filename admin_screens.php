<?php
session_start();
require 'inc/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'king') {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_screen'])) {
    $del_id = intval($_POST['delete_screen']);
    $stmt = $db->prepare("DELETE FROM screens WHERE id = ?");
    $stmt->execute([$del_id]);
    header('Location: admin_screens.php');
    exit;
}

$stmt = $db->query("SELECT s.*, u.email AS owner_email FROM screens s JOIN users u ON s.user_id = u.id ORDER BY s.id DESC");
$screens = $stmt->fetchAll(PDO::FETCH_ASSOC);

function is_online($last_active) {
    if (!$last_active) return false;
    $dt_last = strtotime($last_active);
    return (time() - $dt_last) < 120;
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8" />
    <title>Ekranlar Yönetimi</title>
    <link rel="stylesheet" href="css/style.css" />
    <style>
        table { width: 90%; margin: 30px auto; border-collapse: collapse; }
        th, td { padding: 12px 16px; border: 1px solid #ddd; text-align: center; }
        th { background: #212121; color: #fff; }
        .status-dot { font-size: 18px; }
        .online { color: #00c853; }
        .offline { color: #bdbdbd; }
        button { padding: 6px 12px; background: #f44336; color: white; border: none; cursor: pointer; }
        button:hover { background: #d32f2f; }
    </style>
</head>
<body>
<?php include 'inc/header_king.php'; ?>

<div class="main-content">
    <h1>Ekranlar</h1>
    <table>
        <thead>
            <tr>
                <th>ID</th><th>Ad</th><th>Owner (E-posta)</th><th>Cihaz ID</th><th>Son Aktif</th><th>Durum</th><th>İşlemler</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($screens as $screen): ?>
            <tr>
                <td><?= $screen['id'] ?></td>
                <td><?= htmlspecialchars($screen['name']) ?></td>
                <td><?= htmlspecialchars($screen['owner_email']) ?></td>
                <td><?= htmlspecialchars($screen['device_id']) ?></td>
                <td><?= $screen['last_active'] ?? '<em>Yok</em>' ?></td>
                <td>
                    <?php if (is_online($screen['last_active'])): ?>
                    <span class="status-dot online">●</span> Online
                    <?php else: ?>
                    <span class="status-dot offline">●</span> Offline
                    <?php endif; ?>
                </td>
                <td>
                    <form method="post" onsubmit="return confirm('Ekranı silmek istediğinize emin misiniz?');" style="display:inline;">
                        <input type="hidden" name="delete_screen" value="<?= $screen['id'] ?>">
                        <button type="submit">Sil</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
