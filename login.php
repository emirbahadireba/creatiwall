<?php
session_start();
require_once 'inc/db.php';
require_once 'inc/log.php';

$hata = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['email'] = $user['email'];

        log_action($user['id'], "User logged in");

        header("Location: dashboard.php");
        exit;
    } else {
        $hata = "E-posta veya şifre hatalı.";
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Giriş Yap</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="login-container">
        <h2>Giriş Yap</h2>
        <?php if ($hata) echo "<p class='hata'>$hata</p>"; ?>
        <form method="post">
            <input type="email" name="email" placeholder="E-posta" required>
            <input type="password" name="password" placeholder="Şifre" required>
            <button type="submit">Giriş Yap</button>
        </form>
        <p>Hesabınız yok mu? <a href="register.php">Kayıt Ol</a></p>
    </div>
</body>
</html>
