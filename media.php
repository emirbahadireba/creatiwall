<?php
require 'inc/db.php';
require_once 'inc/functions.php'; // rename_media_file fonksiyonunu burada tanımla veya include et
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$user_id = $_SESSION['user_id'];

$media_error = "";
$rename_success = "";
$rename_error = "";

// Medya silme işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_media_id'])) {
    $media_id = intval($_POST['delete_media_id']);
    $stmt = $db->prepare("SELECT filename, thumbnail FROM media WHERE id = ? AND user_id = ?");
    $stmt->execute([$media_id, $user_id]);
    $media = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($media) {
        $file_path = 'uploads/' . $media['filename'];
        $thumb_path = isset($media['thumbnail']) ? 'uploads/thumbnails/' . $media['thumbnail'] : null;

        $stmt = $db->prepare("DELETE FROM media WHERE id = ? AND user_id = ?");
        if ($stmt->execute([$media_id, $user_id])) {
            if (file_exists($file_path)) unlink($file_path);
            if ($thumb_path && file_exists($thumb_path)) unlink($thumb_path);
            header("Location: media.php");
            exit;
        } else {
            $media_error = "Veritabanı hatası.";
        }
    } else {
        $media_error = "Medya bulunamadı veya yetkiniz yok.";
    }
}

// Medya yükleme işlemi
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['upload_media'])) {
    if (isset($_FILES['media_file']) && $_FILES['media_file']['error'] == 0) {
        $file = $_FILES['media_file'];
        $newName = uniqid() . "_" . basename($file['name']);
        $target = "uploads/" . $newName;
        if (move_uploaded_file($file['tmp_name'], $target)) {
            $filetype = mime_content_type($target);

            $thumbnail = null;
            if (strpos($filetype, 'video') !== false) {
                // exec kapalı olabilir, thumbnail oluşturma devre dışı
                /*
                $thumbName = uniqid() . ".jpg";
                $thumbPath = "uploads/thumbnails/" . $thumbName;
                $cmd = "ffmpeg -i " . escapeshellarg($target) . " -ss 00:00:01 -vframes 1 " . escapeshellarg($thumbPath);
                @exec($cmd);
                if (file_exists($thumbPath)) {
                    $thumbnail = $thumbName;
                }
                */
                $thumbnail = null;
            }

            $stmt = $db->prepare("INSERT INTO media (user_id, filename, filetype, thumbnail) VALUES (?, ?, ?, ?)");
            $stmt->execute([$user_id, $newName, $filetype, $thumbnail]);
            header("Location: media.php");
            exit;
        } else {
            $media_error = "Upload error!";
        }
    } else {
        $media_error = "Please select a file!";
    }
}

// Dosya ismi değiştirme işlemi AJAX olarak 'rename_media.php' ye de eklenebilir ama buraya örnek kullanım:
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['media_id'], $_POST['new_filename'])) {
    $media_id = intval($_POST['media_id']);
    $new_name_raw = trim($_POST['new_filename']);

    if (rename_media_file($db, $media_id, $new_name_raw)) {
        $rename_success = "Dosya adı başarıyla değiştirildi.";
    } else {
        $rename_error = "Dosya adı değiştirilemedi. Lütfen başka bir isim deneyin veya dosya mevcut olabilir.";
    }
}

// Filtre parametresi
$filter = $_GET['filter'] ?? 'all';
$valid_filters = ['all', 'image', 'video', 'pdf'];
if (!in_array($filter, $valid_filters)) {
    $filter = 'all';
}

if ($filter === 'all') {
    $stmt = $db->prepare("SELECT * FROM media WHERE user_id = ? ORDER BY id DESC");
    $stmt->execute([$user_id]);
} elseif ($filter === 'image') {
    $stmt = $db->prepare("SELECT * FROM media WHERE user_id = ? AND filetype LIKE 'image/%' ORDER BY id DESC");
    $stmt->execute([$user_id]);
} elseif ($filter === 'video') {
    $stmt = $db->prepare("SELECT * FROM media WHERE user_id = ? AND filetype LIKE 'video/%' ORDER BY id DESC");
    $stmt->execute([$user_id]);
} elseif ($filter === 'pdf') {
    $stmt = $db->prepare("SELECT * FROM media WHERE user_id = ? AND filetype LIKE 'application/pdf' ORDER BY id DESC");
    $stmt->execute([$user_id]);
}

$mediaList = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8" />
    <title>Media Management</title>
    <link rel="stylesheet" href="css/style.css" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .media-list {
            list-style: none;
            padding: 0;
            margin-top: 20px;
            border-top: 1px solid #ddd;
        }
        .media-list li {
            display: flex;
            align-items: center;
            padding: 10px 8px;
            border-bottom: 1px solid #ddd;
            gap: 15px;
        }
        .media-thumb, .media-thumb-video {
            height: 50px;
            width: 80px;
            object-fit: cover;
            border-radius: 4px;
        }
        .media-icon {
            font-size: 30px;
            width: 80px;
            text-align: center;
        }
        .media-filename {
            flex-grow: 1;
            cursor: pointer;
        }
        .media-filename input {
            width: 100%;
            font-size: 14px;
            padding: 4px 6px;
        }
        .delete-btn {
            background: #c0392b;
            border: none;
            padding: 5px 12px;
            color: white;
            cursor: pointer;
            border-radius: 4px;
            font-weight: bold;
        }
        .filter-select {
            margin-top: 15px;
            font-size: 16px;
            padding: 6px 10px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }
        .message {
            font-weight: bold;
            padding: 10px 0;
        }
        .success {
            color: green;
        }
        .error {
            color: red;
        }
    </style>
</head>
<body>
<?php include 'inc/header.php'; ?>

<div class="main-content">
    <h1>Media Management</h1>

    <?php if ($media_error): ?>
        <p class="message error"><?= htmlspecialchars($media_error) ?></p>
    <?php endif; ?>
    <?php if ($rename_success): ?>
        <p class="message success"><?= htmlspecialchars($rename_success) ?></p>
    <?php endif; ?>
    <?php if ($rename_error): ?>
        <p class="message error"><?= htmlspecialchars($rename_error) ?></p>
    <?php endif; ?>

    <h2>Upload Media</h2>
    <form action="media.php" method="post" enctype="multipart/form-data">
        <input type="file" name="media_file" required>
        <button type="submit" name="upload_media">Upload</button>
    </form>

    <label for="filter">Filtre:</label>
    <select id="filter" class="filter-select" onchange="location.href='media.php?filter='+this.value;">
        <option value="all" <?= $filter === 'all' ? 'selected' : '' ?>>Tümü</option>
        <option value="image" <?= $filter === 'image' ? 'selected' : '' ?>>Resim</option>
        <option value="video" <?= $filter === 'video' ? 'selected' : '' ?>>Video</option>
        <option value="pdf" <?= $filter === 'pdf' ? 'selected' : '' ?>>PDF</option>
    </select>

    <ul class="media-list" id="mediaList">
        <?php foreach ($mediaList as $media): ?>
            <li data-id="<?= $media['id'] ?>">
                <?php
                $url = "uploads/" . htmlspecialchars($media['filename']);
                if (!empty($media['thumbnail'])) {
                    $thumb_url = "uploads/thumbnails/" . htmlspecialchars($media['thumbnail']);
                    echo "<img src='$thumb_url' class='media-thumb-video' alt='Video thumbnail'>";
                } else if (strpos($media['filetype'], "image") !== false) {
                    echo "<img src='$url' class='media-thumb' alt='Image'>";
                } else if (strpos($media['filetype'], "video") !== false) {
                    echo "<div class='media-icon'>🎬</div>";
                } else if (strpos($media['filetype'], "pdf") !== false) {
                    echo "<div class='media-icon'>📄</div>";
                } else {
                    echo "<div class='media-icon'>📁</div>";
                }
                ?>
                <div class="media-filename" title="Click to edit"><?= htmlspecialchars($media['filename']) ?></div>
                <form method="post" style="margin-left:auto;" onsubmit="return confirm('Bu medya dosyasını silmek istediğinize emin misiniz?');">
                    <input type="hidden" name="delete_media_id" value="<?= $media['id'] ?>">
                    <button type="submit" class="delete-btn">Sil</button>
                </form>
            </li>
        <?php endforeach; ?>
    </ul>
</div>

<script>
$(document).ready(function() {
    $('.media-filename').on('click', function() {
        if ($(this).find('input').length) return; // already editing
        const div = $(this);
        const originalText = div.text();
        div.html('<input type="text" value="' + originalText + '" />');
        const input = div.find('input');
        input.focus();

        input.on('blur keydown', function(e) {
            if (e.type === 'blur' || (e.type === 'keydown' && e.key === 'Enter')) {
                const newVal = input.val().trim();
                if (newVal === '') {
                    alert('İsim boş olamaz!');
                    input.focus();
                    return;
                }
                if (newVal === originalText) {
                    div.text(originalText);
                    return;
                }
                // AJAX ile isim güncelle
                $.ajax({
                    url: 'rename_media.php',
                    type: 'POST',
                    data: { id: div.parent().data('id'), new_name: newVal },
                    success: function(response) {
                        if (response.success) {
                            div.text(response.new_filename); // veritabanından dönen güvenli isim
                        } else {
                            alert('Güncelleme başarısız: ' + response.message);
                            div.text(originalText);
                        }
                    },
                    error: function() {
                        alert('Sunucu hatası');
                        div.text(originalText);
                    },
                    dataType: 'json'
                });
            }
        });
    });
});
</script>

</body>
</html>
