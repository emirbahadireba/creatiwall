<?php
session_start();
require 'inc/db.php';

if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'king' && $_SESSION['role'] !== 'admin')) {
    header('Location: login.php');
    exit;
}

// Tema renk ve logo ayarlarını DB'den çek
function get_setting($key) {
    global $db;
    $stmt = $db->prepare("SELECT `value` FROM settings WHERE `key` = ?");
    $stmt->execute([$key]);
    return $stmt->fetchColumn();
}

$theme_color = get_setting('theme_color') ?: '#ff7e29';
$logo_url = get_setting('logo_url') ?: 'uploads/default_logo.png';

$success = '';
$error = '';

// Form gönderildiğinde
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_color = $_POST['theme_color'] ?? '#ff7e29';

    // Renk güncelle
    $stmt = $db->prepare("INSERT INTO settings (`key`, `value`) VALUES ('theme_color', ?) ON DUPLICATE KEY UPDATE value = ?");
    $stmt->execute([$new_color, $new_color]);

    // Logo yükleme varsa işle
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === 0) {
        $allowed_types = ['image/png', 'image/jpeg', 'image/svg+xml', 'image/gif'];
        if (in_array($_FILES['logo']['type'], $allowed_types)) {
            $ext = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
            $new_logo_name = 'logo_' . uniqid() . '.' . $ext;
            $target = 'uploads/' . $new_logo_name;

            if (move_uploaded_file($_FILES['logo']['tmp_name'], $target)) {
                $stmt = $db->prepare("INSERT INTO settings (`key`, `value`) VALUES ('logo_url', ?) ON DUPLICATE KEY UPDATE value = ?");
                $stmt->execute([$target, $target]);
                $logo_url = $target;
            } else {
                $error = 'Logo yüklenirken hata oluştu.';
            }
        } else {
            $error = 'Sadece PNG, JPEG, SVG, GIF dosyaları yükleyebilirsiniz.';
        }
    }

    if (!$error) {
        $theme_color = $new_color;
        $success = 'Tema ayarları başarıyla kaydedildi.';
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8" />
    <title>Tema Ayarları</title>
    <link rel="stylesheet" href="css/style.css" />
    <style>
        label {display:block; margin-top:10px;}
        input[type=color] {width: 120px; height: 35px; border:none; cursor:pointer;}
        input[type=file] {margin-top:5px;}
        .preview-logo {margin-top:15px; max-height: 80px;}
        .message {margin-top: 15px; font-weight: bold;}
        .success {color: green;}
        .error {color: red;}
    </style>
</head>
<body>
<?php include 'inc/header_king.php'; ?>

<div class="main-content">
    <h1>Tema Ayarları</h1>

    <form action="admin_theme.php" method="post" enctype="multipart/form-data">
        <label for="theme_color">Tema Rengi:</label>
        <input type="color" id="theme_color" name="theme_color" value="<?= htmlspecialchars($theme_color) ?>">

        <label for="logo">Logo Yükle:</label>
        <input type="file" id="logo" name="logo" accept="image/*">

        <?php if ($logo_url): ?>
            <img src="<?= htmlspecialchars($logo_url) ?>" alt="Mevcut Logo" class="preview-logo" />
        <?php endif; ?>

        <button type="submit" style="margin-top: 15px;">Kaydet</button>
    </form>

    <?php if ($success): ?>
        <div class="message success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="message error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
</div>

</body>
</html>
