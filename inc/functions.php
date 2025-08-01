<?php
function rename_media_file(PDO $db, int $media_id, string $newNameRaw): bool {
    // Eski dosya adını al
    $stmt = $db->prepare("SELECT filename FROM media WHERE id = ?");
    $stmt->execute([$media_id]);
    $oldFilename = $stmt->fetchColumn();
    if (!$oldFilename) return false;

    $extension = pathinfo($oldFilename, PATHINFO_EXTENSION);

    // Türkçe karakterleri İngilizce'ye çevir, güvenli karakterlere dönüştür
    $turkce = ['ş', 'Ş', 'ı', 'İ', 'ğ', 'Ğ', 'ü', 'Ü', 'ö', 'Ö', 'ç', 'Ç'];
    $ingilizce = ['s', 'S', 'i', 'I', 'g', 'G', 'u', 'U', 'o', 'O', 'c', 'C'];
    $safeName = str_replace($turkce, $ingilizce, $newNameRaw);
    $safeName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $safeName);

    $newFilename = $safeName . '.' . $extension;

    // Dosya var mı kontrolü
    if (!file_exists("uploads/" . $oldFilename)) return false;

    // Yeni dosya adı zaten var mı kontrolü
    if (file_exists("uploads/" . $newFilename)) return false;

    // Dosya adını değiştir
    if (!rename("uploads/" . $oldFilename, "uploads/" . $newFilename)) return false;

    // Veritabanını güncelle
    $stmt = $db->prepare("UPDATE media SET filename = ? WHERE id = ?");
    if (!$stmt->execute([$newFilename, $media_id])) {
        // Hata durumunda dosyayı eski haline döndür
        rename("uploads/" . $newFilename, "uploads/" . $oldFilename);
        return false;
    }
    return true;
}
?>
