<?php
session_start();
require 'inc/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'king') {
    header('Location: login.php');
    exit;
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = trim($_POST['message']);
    $target_user = $_POST['target_user']; // "all" veya kullanıcı ID

    if ($message === '') {
        $error = "Mesaj boş olamaz.";
    } else {
        if ($target_user === 'all') {
            // Tüm kullanıcılara gönder
            $stmt = $db->query("SELECT id FROM users");
            $users = $stmt->fetchAll(PDO::FETCH_COLUMN);
        } else {
            $users = [$target_user];
        }
        $stmt = $db->prepare("INSERT INTO notifications (user_id, message, is_read, created_at) VALUES (?, ?, 0, NOW())");
        foreach ($users as $uid) {
            $stmt->execute([$uid, $message]);
        }
        $success = "Bildirim başarıyla gönderildi.";
    }
}

// Kullanıcıları çek (kendisi hariç)
$stmt = $db->prepare("SELECT id, email FROM users WHERE id != ?");
$stmt->execute([$_SESSION['user_id']]);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Bildirim Gönder</title>
    <link rel="stylesheet" href="css/style.css" />
    <style>
        .container {
            max-width: 600px;
            margin: 40px auto;
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 8px rgba(0,0,0,0.1);
        }
        textarea {
            width: 100%;
            height: 120px;
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 16px;
            resize: vertical;
        }
        select, button {
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 16px;
        }
        button {
            background-color: #ff7e29;
            border: none;
            color: white;
            font-weight: bold;
            cursor: pointer;
        }
        button:hover {
            background-color: #e36b15;
        }
        .success {
            color: green;
            margin-bottom: 15px;
        }
        .error {
            color: red;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
<?php include 'inc/header_king.php'; ?>

<div class="container">
    <h1>Bildirim Gönder</h1>

    <?php if ($success): ?>
        <div class="success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post">
        <label for="target_user">Hedef Kullanıcı</label><br>
        <select id="target_user" name="target_user" required>
            <option value="all">Tüm Kullanıcılar</option>
            <?php foreach ($users as $user): ?>
                <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['email']) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="message" style="margin-top:15px; display:block;">Mesaj</label>
        <textarea id="message" name="message" required></textarea>

        <button type="submit">Gönder</button>
    </form>
</div>
</body>
</html>
