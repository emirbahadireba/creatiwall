<?php
session_start();
require 'inc/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'king') {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_media'])) {
    $del_id = intval($_POST['delete_media']);
    $stmt = $db->prepare("DELETE FROM media WHERE id = ?");
    $stmt->execute([$del_id]);
    header('Location: admin_media.php');
    exit;
}

$stmt = $db->query("SELECT m.*, u.email AS owner_email FROM media m JOIN users u ON m.user_id = u.id ORDER BY m.id DESC");
$media = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8" />
    <title>Medya Yönetimi</title>
    <link rel="stylesheet" href="css/style.css" />
    <style>
        table { width: 90%; margin: 30px auto; border-collapse: collapse; }
        th, td { padding: 12px 16px; border: 1px solid #ddd; text-align: center; }
        th { background: #212121; color: #fff; }
        button { padding: 6px 12px; background: #f44336; color: white; border: none; cursor: pointer; }
        button:hover { background: #d32f2f; }
        img { height: 40px; }
    </style>
</head>
<body>
<?php include 'inc/header_king.php'; ?>

<div class="main-content">
    <h1>Medya Dosyaları</h1>
    <table>
        <thead>
            <tr>
                <th>ID</th><th>Dosya</th><th>Tip</th><th>Owner (E-posta)</th><th>İşlemler</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($media as $m): ?>
            <tr>
                <td><?= $m['id'] ?></td>
                <td>
                    <?php
                    if (strpos($m['filetype'], 'image') !== false) {
                        echo "<img src='uploads/" . htmlspecialchars($m['filename']) . "' alt=''>";
                    } else {
                        echo htmlspecialchars($m['filename']);
                    }
                    ?>
                </td>
                <td><?= htmlspecialchars($m['filetype']) ?></td>
                <td><?= htmlspecialchars($m['owner_email']) ?></td>
                <td>
                    <form method="post" onsubmit="return confirm('Medya dosyasını silmek istediğinize emin misiniz?');" style="display:inline;">
                        <input type="hidden" name="delete_media" value="<?= $m['id'] ?>">
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
