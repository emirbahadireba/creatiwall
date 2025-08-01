<?php
require 'inc/db.php'; // Veritabanı bağlantısı dahil edilir

$hata = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $hata = "Geçerli bir e-posta giriniz.";
    } elseif (strlen($password) < 6) {
        $hata = "Şifre en az 6 karakter olmalı.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $hata = "Bu e-posta zaten kayıtlı!";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
            $stmt->execute([$email, $hashed]);
            header("Location: login.php");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Kayıt Ol</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="login-container">
        <h2>Kayıt Ol</h2>
        <?php if ($hata) echo "<p class='hata'>$hata</p>"; ?>
        <form method="post">
            <input type="email" name="email" placeholder="E-posta" required>
            <input type="password" name="password" placeholder="Şifre" required>
            <button type="submit">Kayıt Ol</button>
        </form>
        <p>Zaten hesabınız var mı? <a href="login.php">Giriş Yap</a></p>
    </div>
</body>
</html>
