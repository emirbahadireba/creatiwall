<?php
require 'inc/db.php';
require_once 'inc/log.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $notify = isset($_POST['notify']) ? 1 : 0;

    // E-posta doğrulaması
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Geçerli bir e-posta giriniz.";
    } else {
        // E-posta güncelle
        $stmt = $db->prepare("UPDATE users SET email = ?, notify_on_new = ? WHERE id = ?");
        $stmt->execute([$email, $notify, $user_id]);

        // Şifre değiştirme (boş bırakılırsa değişmez)
        if ($password !== '') {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hash, $user_id]);
        }

        $success = "Profil güncellendi.";
        log_action($user_id, "Profile updated");
    }
}

// Kullanıcı bilgilerini çek
$stmt = $db->prepare("SELECT email, notify_on_new FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8" />
    <title>Profilim</title>
    <link rel="stylesheet" href="css/style.css" />
    <style>
        .profile-container {
            max-width: 400px;
            margin: 40px auto;
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 8px rgba(0,0,0,0.1);
        }
        label {
            display: block;
            margin-top: 15px;
        }
        input[type="email"], input[type="password"] {
            width: 100%;
            padding: 8px 10px;
            margin-top: 6px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }
        .checkbox-label {
            margin-top: 15px;
            display: flex;
            align-items: center;
        }
        .checkbox-label input {
            margin-right: 8px;
        }
        button {
            margin-top: 20px;
            padding: 10px 18px;
            background: #ff7e29;
            border: none;
            color: white;
            font-weight: bold;
            border-radius: 6px;
            cursor: pointer;
        }
        .success {
            color: green;
            margin-top: 15px;
        }
        .error {
            color: red;
            margin-top: 15px;
        }
    </style>
</head>
<body>
<?php include 'inc/header.php'; ?>

<div class="profile-container">
    <h1>Profilim</h1>
    <?php if ($success): ?>
        <div class="success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post">
        <label for="email">E-posta</label>
        <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>

        <label for="password">Yeni Şifre (değiştirmek istemiyorsanız boş bırakın)</label>
        <input type="password" id="password" name="password" placeholder="Yeni şifre">

        <label class="checkbox-label">
            <input type="checkbox" name="notify" <?= $user['notify_on_new'] ? 'checked' : '' ?>>
            Yeni bildirimlerde e-posta al
        </label>

        <button type="submit">Güncelle</button>
    </form>
</div>
</body>
</html>
