<?php
session_start();
require 'inc/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'king') {
    header('Location: login.php');
    exit;
}

// Toplam medya dosya boyutu
$stmt = $db->query("SELECT SUM(LENGTH(filename)) FROM media");
$total_media_size = $stmt->fetchColumn();

// Kullanıcı bazında medya dosya boyutları
$stmt = $db->query("SELECT u.email, SUM(LENGTH(m.filename)) as total_size FROM media m JOIN users u ON m.user_id = u.id GROUP BY u.id ORDER BY total_size DESC");
$user_usage = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8" />
    <title>Veri Kullanımı</title>
    <link rel="stylesheet" href="css/style.css" />
    <style>
        table { width: 90%; margin: 30px auto; border-collapse: collapse; }
        th, td { padding: 12px 16px; border: 1px solid #ddd; text-align: center; }
        th { background: #212121; color: #fff; }
    </style>
</head>
<body>
<?php include 'inc/header_king.php'; ?>

<div class="main-content">
    <h1>Veri Kullanımı</h1>
    <p>Toplam Medya Dosya Boyutu: <?= $total_media_size ? number_format($total_media_size/1024, 2) : '0' ?> KB</p>
    <h2>Kullanıcı Bazında Kullanım</h2>
    <table>
        <thead>
            <tr><th>E-posta</th><th>Toplam Dosya Boyutu (KB)</th></tr>
        </thead>
        <tbody>
            <?php foreach ($user_usage as $usage): ?>
            <tr>
                <td><?= htmlspecialchars($usage['email']) ?></td>
                <td><?= $usage['total_size'] ? number_format($usage['total_size']/1024, 2) : '0' ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
