<?php
// admin_logs.php
session_start();
require 'inc/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'king') {
    header('Location: login.php');
    exit;
}

// Filtreleme parametreleri al
$user_filter = isset($_GET['user']) ? trim($_GET['user']) : '';
$action_filter = isset($_GET['action']) ? trim($_GET['action']) : '';
$date_from = isset($_GET['date_from']) ? trim($_GET['date_from']) : '';
$date_to = isset($_GET['date_to']) ? trim($_GET['date_to']) : '';

// Sorgu için where koşulları ve parametreler
$where_clauses = [];
$params = [];

if ($user_filter !== '') {
    $where_clauses[] = "u.email LIKE ?";
    $params[] = "%$user_filter%";
}
if ($action_filter !== '') {
    $where_clauses[] = "ul.action LIKE ?";
    $params[] = "%$action_filter%";
}
if ($date_from !== '') {
    $where_clauses[] = "ul.created_at >= ?";
    $params[] = $date_from . ' 00:00:00';
}
if ($date_to !== '') {
    $where_clauses[] = "ul.created_at <= ?";
    $params[] = $date_to . ' 23:59:59';
}

$where_sql = '';
if (count($where_clauses) > 0) {
    $where_sql = 'WHERE ' . implode(' AND ', $where_clauses);
}

// Logları çek (son 100 kayıt, filtreli)
$stmt = $db->prepare("
    SELECT ul.*, u.email 
    FROM user_logs ul 
    JOIN users u ON ul.user_id = u.id
    $where_sql
    ORDER BY ul.created_at DESC
    LIMIT 100
");
$stmt->execute($params);
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8" />
    <title>Kullanıcı Logları - Filtreli</title>
    <link rel="stylesheet" href="css/style.css" />
    <style>
        .main-content {
            margin-left: 220px;
            padding: 40px 30px;
            background: #f8fafc;
            min-height: 100vh;
            box-sizing: border-box;
        }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background-color: #212121; color: white; }
        tbody tr:nth-child(odd) { background: #f9f9f9; }
        form.filter-form {
            margin-bottom: 20px;
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 0 6px rgba(0,0,0,0.1);
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: center;
        }
        form.filter-form input, form.filter-form button {
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #ccc;
            font-size: 14px;
        }
        form.filter-form button {
            background-color: #e67e22;
            color: white;
            border: none;
            cursor: pointer;
        }
        form.filter-form button:hover {
            background-color: #cf6e18;
        }
        label {
            font-weight: bold;
            font-size: 14px;
        }
    </style>
</head>
<body>
<?php include 'inc/header_king.php'; ?>

<div class="main-content">
    <h1>Kullanıcı Logları - Filtreleme</h1>
    <form class="filter-form" method="get" action="admin_logs.php">
        <label for="user">Kullanıcı E-posta:</label>
        <input type="text" id="user" name="user" value="<?= htmlspecialchars($user_filter) ?>" placeholder="E-posta ara...">

        <label for="action">İşlem:</label>
        <input type="text" id="action" name="action" value="<?= htmlspecialchars($action_filter) ?>" placeholder="İşlem ara...">

        <label for="date_from">Başlangıç Tarihi:</label>
        <input type="date" id="date_from" name="date_from" value="<?= htmlspecialchars($date_from) ?>">

        <label for="date_to">Bitiş Tarihi:</label>
        <input type="date" id="date_to" name="date_to" value="<?= htmlspecialchars($date_to) ?>">

        <button type="submit">Filtrele</button>
        <button type="button" onclick="window.location='admin_logs.php'">Temizle</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Kullanıcı E-posta</th>
                <th>İşlem</th>
                <th>IP Adresi</th>
                <th>Tarih</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($logs) === 0): ?>
                <tr><td colspan="5" style="text-align:center;">Kayıt bulunamadı.</td></tr>
            <?php else: ?>
                <?php foreach($logs as $log): ?>
                <tr>
                    <td><?= htmlspecialchars($log['id']) ?></td>
                    <td><?= htmlspecialchars($log['email']) ?></td>
                    <td><?= htmlspecialchars($log['action']) ?></td>
                    <td><?= htmlspecialchars($log['ip_address']) ?></td>
                    <td><?= htmlspecialchars($log['created_at']) ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>
