<?php
session_start();
require_once 'inc/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// --- EKRAN SİLME ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_screen'])) {
    $del_id = intval($_POST['delete_screen_id']);
    $stmt = $db->prepare("DELETE FROM screens WHERE id = ? AND user_id = ?");
    $stmt->execute([$del_id, $user_id]);
    header("Location: screens.php");
    exit;
}

// --- EKRAN DÜZENLEME ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_screen'])) {
    $edit_id = intval($_POST['edit_screen_id']);
    $edit_name = trim($_POST['edit_name']);
    $edit_location = trim($_POST['edit_location']);
    $edit_device_id = trim($_POST['edit_device_id']);
    $edit_width = !empty($_POST['edit_width']) ? intval($_POST['edit_width']) : null;
    $edit_height = !empty($_POST['edit_height']) ? intval($_POST['edit_height']) : null;
    $edit_playlist_id = !empty($_POST['edit_playlist_id']) ? intval($_POST['edit_playlist_id']) : null;

    if ($edit_name && $edit_device_id) {
        $stmt = $db->prepare("UPDATE screens SET name = ?, location = ?, device_id = ?, width = ?, height = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$edit_name, $edit_location, $edit_device_id, $edit_width, $edit_height, $edit_id, $user_id]);

        // Playlist ataması
        if ($edit_playlist_id) {
            // Daha önce atanmış mı kontrol et
            $stmt_check = $db->prepare("SELECT id FROM screen_playlists WHERE screen_id = ?");
            $stmt_check->execute([$edit_id]);
            if ($stmt_check->fetch()) {
                $stmt_update = $db->prepare("UPDATE screen_playlists SET playlist_id = ?, assigned_at = CURRENT_TIMESTAMP WHERE screen_id = ?");
                $stmt_update->execute([$edit_playlist_id, $edit_id]);
            } else {
                $stmt_insert = $db->prepare("INSERT INTO screen_playlists (screen_id, playlist_id) VALUES (?, ?)");
                $stmt_insert->execute([$edit_id, $edit_playlist_id]);
            }
        } else {
            // Eğer boş ise önceki atamayı kaldırabiliriz (isteğe bağlı)
            $stmt_delete = $db->prepare("DELETE FROM screen_playlists WHERE screen_id = ?");
            $stmt_delete->execute([$edit_id]);
        }

        header("Location: screens.php");
        exit;
    }
}

// --- EKRAN EKLEME ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_screen'])) {
    $name = trim($_POST['name']);
    $location = trim($_POST['location']);
    $device_id = trim($_POST['device_id']);
    $width = !empty($_POST['width']) ? intval($_POST['width']) : null;
    $height = !empty($_POST['height']) ? intval($_POST['height']) : null;
    $playlist_id = !empty($_POST['playlist_id']) ? intval($_POST['playlist_id']) : null;

    if ($name && $device_id) {
        $stmt = $db->prepare("INSERT INTO screens (user_id, name, location, device_id, width, height) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $name, $location, $device_id, $width, $height]);
        $new_screen_id = $db->lastInsertId();

        if ($playlist_id) {
            $stmt_pl = $db->prepare("INSERT INTO screen_playlists (screen_id, playlist_id) VALUES (?, ?)");
            $stmt_pl->execute([$new_screen_id, $playlist_id]);
        }

        header("Location: screens.php");
        exit;
    }
}

// Kullanıcı ekranlarını çek
$stmt = $db->prepare("SELECT s.*, sp.playlist_id, p.name as playlist_name FROM screens s LEFT JOIN screen_playlists sp ON s.id = sp.screen_id LEFT JOIN playlists p ON sp.playlist_id = p.id WHERE s.user_id = ?");
$stmt->execute([$user_id]);
$screens = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Kullanıcının playlistlerini çek (seçim için)
$stmt2 = $db->prepare("SELECT id, name FROM playlists WHERE user_id = ?");
$stmt2->execute([$user_id]);
$playlists = $stmt2->fetchAll(PDO::FETCH_ASSOC);

// Online kontrol fonksiyonu
function is_online($last_active) {
    if (!$last_active) return false;
    $dt_last = strtotime($last_active);
    return (time() - $dt_last) < 120;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8" />
    <title>Ekranlarım</title>
    <link rel="stylesheet" href="css/style.css" />
    <style>
        input[type="number"], select {
            width: 140px;
            padding: 8px;
            margin-right: 10px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }
        .main-content {
            margin-left: 220px;
            width: calc(100% - 220px);
            padding: 40px 32px;
            overflow-y: auto;
            height: 100vh;
            box-sizing: border-box;
            background-color: #fff;
            position: relative;
            z-index: 1;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border-bottom: 1px solid #ddd;
            padding: 12px;
            text-align: center;
            vertical-align: middle;
        }
        th {
            background: #212121;
            color: #fff;
        }
        .status-dot {
            font-size: 24px;
            vertical-align: middle;
            margin-right: 6px;
        }
        .online {
            color: #00c853;
        }
        .offline {
            color: #bdbdbd;
        }
        .action-button {
            margin: 0 5px;
            padding: 6px 12px;
            cursor: pointer;
            border: none;
            border-radius: 4px;
            color: white;
        }
        .edit-button {
            background-color: #2196F3;
        }
        .delete-button {
            background-color: #f44336;
        }
        .preview-button {
            background-color: #4caf50;
            color: white;
            padding: 6px 12px;
            text-decoration: none;
            border-radius: 4px;
            margin-right: 5px;
        }
        #editForm {
            display: none;
            background: #eee;
            padding: 20px;
            margin: 20px auto;
            width: 100%;
            max-width: 650px;
            border-radius: 6px;
        }
        #editForm input[type="text"], #editForm input[type="number"], #editForm select {
            padding: 8px;
            margin: 5px 10px 10px 0;
            width: 140px;
        }
        #editForm button {
            padding: 8px 16px;
            margin: 5px 10px 0 0;
        }
    </style>
</head>
<body>
<?php include 'inc/header.php'; ?>

<div class="main-content">
    <h1>Ekranlarım</h1>

    <h2>Yeni Ekran Ekle</h2>
    <form action="screens.php" method="post">
        <input type="text" name="name" placeholder="Ekran Adı" required>
        <input type="text" name="location" placeholder="Konum (Opsiyonel)">
        <input type="text" name="device_id" placeholder="Cihaz ID (ör: abc123)" required>
        <input type="number" name="width" placeholder="Genişlik (px)" min="100" step="1">
        <input type="number" name="height" placeholder="Yükseklik (px)" min="100" step="1">
        <select name="playlist_id">
            <option value="">Playlist Seç (Opsiyonel)</option>
            <?php foreach ($playlists as $pl): ?>
                <option value="<?= $pl['id'] ?>"><?= htmlspecialchars($pl['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" name="add_screen">Ekle</button>
    </form>

    <div id="editForm">
        <h3>Ekran Düzenle</h3>
        <form method="post">
            <input type="hidden" name="edit_screen_id" id="edit_screen_id">
            <input type="text" name="edit_name" id="edit_name" placeholder="Ekran Adı" required>
            <input type="text" name="edit_location" id="edit_location" placeholder="Konum (Opsiyonel)">
            <input type="text" name="edit_device_id" id="edit_device_id" placeholder="Cihaz ID" required>
            <input type="number" name="edit_width" id="edit_width" placeholder="Genişlik (px)" min="100" step="1">
            <input type="number" name="edit_height" id="edit_height" placeholder="Yükseklik (px)" min="100" step="1">
            <select name="edit_playlist_id" id="edit_playlist_id">
                <option value="">Playlist Seç (Opsiyonel)</option>
                <?php foreach ($playlists as $pl): ?>
                    <option value="<?= $pl['id'] ?>"><?= htmlspecialchars($pl['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" name="edit_screen">Kaydet</button>
            <button type="button" onclick="document.getElementById('editForm').style.display='none'">İptal</button>
        </form>
    </div>

    <h2>Kayıtlı Ekranlar</h2>
    <table>
        <thead>
            <tr>
                <th>Ekran Adı</th>
                <th>Cihaz ID</th>
                <th>Konum</th>
                <th>Genişlik (px)</th>
                <th>Yükseklik (px)</th>
                <th>Playlist</th>
                <th>Durum</th>
                <th>Son Aktif</th>
                <th>İşlemler</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($screens as $screen): ?>
            <tr>
                <td><?= htmlspecialchars($screen['name']) ?></td>
                <td><?= htmlspecialchars($screen['device_id']) ?></td>
                <td><?= htmlspecialchars($screen['location']) ?></td>
                <td><?= $screen['width'] ?? 'Varsayılan' ?></td>
                <td><?= $screen['height'] ?? 'Varsayılan' ?></td>
                <td><?= $screen['playlist_name'] ? htmlspecialchars($screen['playlist_name']) : '<em>Yok</em>' ?></td>
                <td>
                    <?php if (is_online($screen['last_active'])): ?>
                        <span class="status-dot online">●</span><span style="color:#00c853; font-weight:bold;">Online</span>
                    <?php else: ?>
                        <span class="status-dot offline">●</span><span style="color:#bdbdbd; font-weight:bold;">Offline</span>
                    <?php endif; ?>
                </td>
                <td><?= $screen['last_active'] ? htmlspecialchars($screen['last_active']) : '<em>Yok</em>' ?></td>
                <td>
                    <a href="player.php?screen_id=<?= $screen['id'] ?>" target="_blank" class="preview-button">Önizle</a>
                    <button class="action-button edit-button"
                            onclick="showEditForm(
                              <?= $screen['id'] ?>,
                              '<?= htmlspecialchars(addslashes($screen['name'])) ?>',
                              '<?= htmlspecialchars(addslashes($screen['location'])) ?>',
                              '<?= htmlspecialchars(addslashes($screen['device_id'])) ?>',
                              <?= $screen['width'] ?? 'null' ?>,
                              <?= $screen['height'] ?? 'null' ?>,
                              <?= $screen['playlist_id'] ?? 'null' ?>
                            )">
                        Düzenle
                    </button>
                    <form method="post" style="display:inline;" onsubmit="return confirm('Ekranı silmek istediğinize emin misiniz?');">
                        <input type="hidden" name="delete_screen_id" value="<?= $screen['id'] ?>">
                        <button type="submit" name="delete_screen" class="action-button delete-button">Sil</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
function showEditForm(id, name, location, device_id, width, height, playlist_id) {
    document.getElementById('editForm').style.display = 'block';
    document.getElementById('edit_screen_id').value = id;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_location').value = location;
    document.getElementById('edit_device_id').value = device_id;
    document.getElementById('edit_width').value = width !== null ? width : '';
    document.getElementById('edit_height').value = height !== null ? height : '';
    document.getElementById('edit_playlist_id').value = playlist_id !== null ? playlist_id : '';
}
</script>

</body>
</html>
