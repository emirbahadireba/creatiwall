<?php
session_start();
require_once 'inc/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// --- Yeni Playlist Oluştur ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_playlist'])) {
    $name = trim($_POST['playlist_name']);
    if ($name !== '') {
        $stmt = $db->prepare("INSERT INTO playlists (user_id, name) VALUES (?, ?)");
        $stmt->execute([$user_id, $name]);
        header('Location: playlists.php');
        exit;
    }
}

// --- Playlist Sil ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_playlist'])) {
    $playlist_id = intval($_POST['playlist_id']);
    $stmt = $db->prepare("SELECT id FROM playlists WHERE id = ? AND user_id = ?");
    $stmt->execute([$playlist_id, $user_id]);
    if ($stmt->fetch()) {
        $db->prepare("DELETE FROM playlist_items WHERE playlist_id = ?")->execute([$playlist_id]);
        $db->prepare("DELETE FROM playlists WHERE id = ?")->execute([$playlist_id]);
    }
    header('Location: playlists.php');
    exit;
}

// --- Playlist'e Medya Ekle ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_media_to_playlist'])) {
    $playlist_id = intval($_POST['playlist_id']);
    $media_id = intval($_POST['media_id']);

    $stmt = $db->prepare("SELECT MAX(order_no) AS max_order FROM playlist_items WHERE playlist_id = ?");
    $stmt->execute([$playlist_id]);
    $row = $stmt->fetch();
    $max_order = $row ? $row['max_order'] : 0;
    $new_order = $max_order + 1;

    $stmt = $db->prepare("INSERT INTO playlist_items (playlist_id, media_id, order_no) VALUES (?, ?, ?)");
    $stmt->execute([$playlist_id, $media_id, $new_order]);
    header('Location: playlists.php');
    exit;
}

// --- Playlist içeriğinden medya sil ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_media'])) {
    $playlist_item_id = intval($_POST['playlist_item_id']);
    $stmt = $db->prepare("DELETE FROM playlist_items WHERE id = ?");
    $stmt->execute([$playlist_item_id]);
    header('Location: playlists.php');
    exit;
}

// Kullanıcının playlistleri
$stmt = $db->prepare("SELECT * FROM playlists WHERE user_id = ?");
$stmt->execute([$user_id]);
$playlists = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Kullanıcının medyaları
$stmt = $db->prepare("SELECT * FROM media WHERE user_id = ?");
$stmt->execute([$user_id]);
$media_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8" />
    <title>Playlist Yönetimi</title>
    <link rel="stylesheet" href="css/style.css" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <style>
        body { font-family: Arial, sans-serif; background: #f9f9f9; margin: 0; padding: 0;}
        .container { max-width: 960px; margin: 20px auto; background: white; padding: 20px; border-radius: 8px; }
        h1 { text-align: center; color: #333; }
        h2 { margin-top: 30px; color: #444; }
        form { margin-bottom: 20px; }
        input[type="text"], select { padding: 8px; border-radius: 4px; border: 1px solid #ccc; margin-right: 10px; }
        button { padding: 8px 14px; background: #ff7e29; border: none; border-radius: 4px; color: white; cursor: pointer; }
        button:hover { background: #e26900; }
        .playlist-container {
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 10px 20px 20px 20px;
            margin-bottom: 40px;
            background: #fefefe;
        }
        .playlist-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .playlist-header h2 {
            margin: 10px 0;
            font-weight: 600;
        }
        .delete-playlist-btn {
            background: #c0392b;
            border: none;
            color: white;
            padding: 6px 12px;
            border-radius: 5px;
            cursor: pointer;
        }
        .delete-playlist-btn:hover {
            background: #992d22;
        }
        .playlist-items {
            list-style: none;
            padding: 0;
            margin-top: 15px;
        }
        .playlist-items li {
            background: #f5f5f5;
            margin-bottom: 8px;
            padding: 10px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: grab;
        }
        .media-info {
            display: flex;
            align-items: center;
        }
        .media-thumb {
            width: 40px;
            height: 40px;
            margin-right: 12px;
            object-fit: cover;
            border-radius: 4px;
        }
        .remove-btn {
            background: #c0392b;
            padding: 4px 8px;
            border: none;
            border-radius: 4px;
            color: white;
            cursor: pointer;
        }
        .remove-btn:hover {
            background: #992d22;
        }
        .duration-input {
            width: 60px;
            margin-left: 15px;
            padding: 4px 6px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }
    </style>
</head>
<body>
<?php include 'inc/header.php'; ?>

<div class="container">
    <h1>Playlist Yönetimi</h1>

    <h2>Yeni Playlist Oluştur</h2>
    <form method="post" action="">
        <input type="text" name="playlist_name" placeholder="Playlist adı" required>
        <button type="submit" name="add_playlist">Oluştur</button>
    </form>

    <?php if (!$playlists): ?>
        <p>Henüz bir playlistiniz yok.</p>
    <?php else: ?>
        <?php foreach ($playlists as $pl): ?>
            <div class="playlist-container">
                <div class="playlist-header">
                    <h2><?= htmlspecialchars($pl['name']) ?></h2>
                    <form method="post" onsubmit="return confirm('Bu playlisti silmek istediğinize emin misiniz?');" style="margin:0;">
                        <input type="hidden" name="playlist_id" value="<?= $pl['id'] ?>">
                        <button type="submit" name="delete_playlist" class="delete-playlist-btn">Playlisti Sil</button>
                    </form>
                </div>

                <form method="post" action="" style="margin-bottom: 10px;">
                    <input type="hidden" name="playlist_id" value="<?= $pl['id'] ?>">
                    <select name="media_id" required>
                        <option value="">Medya Seçin</option>
                        <?php foreach ($media_list as $media): ?>
                            <option value="<?= $media['id'] ?>"><?= htmlspecialchars($media['filename']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" name="add_media_to_playlist">Ekle</button>
                </form>

                <?php
                $stmt = $db->prepare("SELECT pi.id, pi.display_duration, m.filename, m.filetype FROM playlist_items pi JOIN media m ON pi.media_id = m.id WHERE pi.playlist_id = ? ORDER BY pi.order_no ASC");
                $stmt->execute([$pl['id']]);
                $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
                ?>

                <?php if (!$items): ?>
                    <p>Henüz içerik yok.</p>
                <?php else: ?>
                    <ul class="playlist-items" data-playlist-id="<?= $pl['id'] ?>">
                        <?php foreach ($items as $item): ?>
                            <li data-item-id="<?= $item['id'] ?>">
                                <div class="media-info">
                                    <?php if (strpos($item['filetype'], 'image') !== false): ?>
                                        <img src="uploads/<?= htmlspecialchars($item['filename']) ?>" class="media-thumb" alt="img">
                                    <?php elseif (strpos($item['filetype'], 'video') !== false): ?>
                                        🎬
                                    <?php else: ?>
                                        📄
                                    <?php endif; ?>
                                    <?= htmlspecialchars($item['filename']) ?>
                                    <?php if (strpos($item['filetype'], 'image') !== false): ?>
                                        <input type="number" class="duration-input" value="<?= $item['display_duration'] ?? 5 ?>" min="1" max="3600" title="Gösterim Süresi (sn)" placeholder="sn">
                                    <?php endif; ?>
                                </div>
                                <form method="post" style="margin: 0;">
                                    <input type="hidden" name="playlist_item_id" value="<?= $item['id'] ?>">
                                    <button type="submit" name="remove_media" class="remove-btn" title="Kaldır">X</button>
                                </form>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
$(function() {
    $(".playlist-items").sortable({
        update: function(event, ui) {
            const playlistId = $(this).data('playlist-id');
            const order = $(this).children().map(function() {
                return $(this).data('item-id');
            }).get();

            $.ajax({
                url: 'update_order.php',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({ playlist_id: playlistId, order: order }),
                success: function(response) {
                    console.log('Sıralama güncellendi.');
                },
                error: function() {
                    alert('Sıralama güncellenirken hata oluştu.');
                }
            });
        }
    }).disableSelection();

    $(".duration-input").on('change', function() {
        const li = $(this).closest('li');
        const itemId = li.data('item-id');
        const duration = parseInt($(this).val());
        if (duration >= 1) {
            $.ajax({
                url: 'update_duration.php',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({ id: itemId, duration: duration }),
                success: function(response) {
                    console.log('Gösterim süresi güncellendi.');
                },
                error: function() {
                    alert('Gösterim süresi güncellenirken hata oluştu.');
                }
            });
        }
    });
});
</script>

</body>
</html>
