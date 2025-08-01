<?php
session_start();
require 'inc/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'king') {
    header('Location: login.php');
    exit;
}

$hata = '';
$basari = '';

// Yeni kullanıcı ekleme işlemi (isteğe bağlı eklendi)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    if (filter_var($email, FILTER_VALIDATE_EMAIL) && !empty($password) && in_array($role, ['viewer', 'admin', 'king'])) {
        // Email zaten kayıtlı mı kontrol et
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $hata = "Bu e-posta zaten kayıtlı.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO users (email, password, role, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$email, $hashed_password, $role]);
            $basari = "Kullanıcı başarıyla eklendi.";
        }
    } else {
        $hata = "Lütfen tüm alanları doğru şekilde doldurun.";
    }
}

// Kullanıcı silme işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $del_id = intval($_POST['delete_user']);
    if ($del_id !== $_SESSION['user_id']) {
        $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$del_id]);
        $basari = "Kullanıcı silindi.";
    } else {
        $hata = "Kendi hesabınızı silemezsiniz.";
    }
}

// Kullanıcı rol güncelleme işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_role'])) {
    $user_id = intval($_POST['user_id']);
    $new_role = $_POST['role'];
    if ($user_id !== $_SESSION['user_id'] && in_array($new_role, ['viewer', 'admin', 'king'])) {
        $stmt = $db->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->execute([$new_role, $user_id]);
        $basari = "Kullanıcı rolü güncellendi.";
    } else if ($user_id === $_SESSION['user_id']) {
        $hata = "Kendi rolünüzü değiştiremezsiniz.";
    } else {
        $hata = "Geçersiz rol seçimi.";
    }
}

$stmt = $db->query("SELECT id, email, role, created_at FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8" />
    <title>Kullanıcı Yönetimi</title>
    <link rel="stylesheet" href="css/style.css" />
    <style>
        .main-content {
            margin-left: 220px;
            padding: 30px;
            background: #f8fafc;
            min-height: 100vh;
        }
        table {
            width: 90%;
            margin: 30px auto;
            border-collapse: collapse;
            background: #fff;
            border-radius: 6px;
            overflow: hidden;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 12px 16px;
            border-bottom: 1px solid #ddd;
            text-align: center;
        }
        th {
            background: #212121;
            color: #fff;
        }
        select {
            padding: 6px;
            border-radius: 4px;
            border: 1px solid #ccc;
            min-width: 100px;
        }
        button {
            padding: 6px 12px;
            background: #e67e22;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 4px;
            transition: background 0.3s ease;
        }
        button:hover {
            background: #cf6e18;
        }
        .message {
            max-width: 90%;
            margin: 10px auto;
            padding: 12px 20px;
            border-radius: 6px;
            text-align: center;
            font-weight: bold;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        form.add-user-form {
            max-width: 400px;
            margin: 30px auto;
            background: #fff;
            padding: 20px;
            border-radius: 6px;
            box-shadow: 0 0 8px rgba(0,0,0,0.1);
        }
        form.add-user-form input, form.add-user-form select, form.add-user-form button {
            width: 100%;
            margin-top: 8px;
            padding: 10px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }
        form.add-user-form button {
            background-color: #27ae60;
            margin-top: 15px;
        }
        form.add-user-form button:hover {
            background-color: #219150;
        }
    </style>
</head>
<body>
<?php include 'inc/header_king.php'; ?>

<div class="main-content">
    <h1>Kullanıcı Yönetimi</h1>

    <?php if ($hata): ?>
        <div class="message error"><?= htmlspecialchars($hata) ?></div>
    <?php elseif ($basari): ?>
        <div class="message success"><?= htmlspecialchars($basari) ?></div>
    <?php endif; ?>

    <form method="post" class="add-user-form">
        <h2>Yeni Kullanıcı Ekle</h2>
        <input type="email" name="email" placeholder="E-posta" required>
        <input type="password" name="password" placeholder="Şifre" required>
        <select name="role" required>
            <option value="">Rol seçin</option>
            <option value="viewer">Viewer</option>
            <option value="admin">Admin</option>
            <option value="king">King</option>
        </select>
        <button type="submit" name="add_user">Ekle</button>
    </form>

    <h2>Mevcut Kullanıcılar</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>E-posta</th>
                <th>Rol</th>
                <th>Kayıt Tarihi</th>
                <th>İşlemler</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= htmlspecialchars($u['id']) ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="user_id" value="<?= htmlspecialchars($u['id']) ?>">
                            <select name="role" onchange="this.form.submit()" <?= $u['id'] == $_SESSION['user_id'] ? 'disabled' : '' ?>>
                                <option value="viewer" <?= $u['role'] === 'viewer' ? 'selected' : '' ?>>Viewer</option>
                                <option value="admin" <?= $u['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                <option value="king" <?= $u['role'] === 'king' ? 'selected' : '' ?>>King</option>
                            </select>
                            <input type="hidden" name="update_role" value="1">
                        </form>
                    </td>
                    <td><?= htmlspecialchars($u['created_at']) ?></td>
                    <td>
                        <?php if ($u['id'] != $_SESSION['user_id']): ?>
                        <form method="post" onsubmit="return confirm('Bu kullanıcıyı silmek istediğinize emin misiniz?');" style="display:inline;">
                            <input type="hidden" name="delete_user" value="<?= htmlspecialchars($u['id']) ?>">
                            <button type="submit">Sil</button>
                        </form>
                        <?php else: ?>
                            <em>Kendi hesabınız</em>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
