<?php
session_start();
require 'inc/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'king') {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_playlist'])) {
    $del_id = intval($_POST['delete_playlist']);
    $stmt = $db->prepare("DELETE FROM playlists WHERE id = ?");
    $stmt->execute([$del_id]);
    header('Location: admin_playlists.php');
    exit;
}

$stmt = $db->query("SELECT p.*, u.email AS owner_email FROM playlists p JOIN users u ON p.user_id = u.id ORDER BY p.id DESC");
$playlists = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8" />
    <title>Playlist Yönetimi</title>
    <link rel="stylesheet" href="css/style.css" />
    <style>
        table { width: 90%; margin: 30px auto; border-collapse: collapse; }
        th, td { padding: 12px 16px; border: 1px solid #ddd; text-align: center; }
        th { background: #212121; color: #fff; }
        button { padding: 6px 12px; background: #f44336; color: white; border: none; cursor: pointer; }
        button:hover { background: #d32f2f; }
    </style>
</head>
<body>
<?php include 'inc/header_king.php'; ?>

<div class="main-content">
    <h1>Playlistler</h1>
    <table>
        <thead>
            <tr>
                <th>ID</th><th>Ad</th><th>Owner (E-posta)</th><th>Kayıt Tarihi</th><th>İşlemler</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($playlists as $pl): ?>
            <tr>
                <td><?= $pl['id'] ?></td>
                <td><?= htmlspecialchars($pl['name']) ?></td>
                <td><?= htmlspecialchars($pl['owner_email']) ?></td>
                <td><?= $pl['created_at'] ?></td>
                <td>
                    <form method="post" onsubmit="return confirm('Playlisti silmek istediğinize emin misiniz?');" style="display:inline;">
                        <input type="hidden" name="delete_playlist" value="<?= $pl['id'] ?>">
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
