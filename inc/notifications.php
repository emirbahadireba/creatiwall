<?php
// inc/notifications.php

/**
 * Bildirimlerin okunma durumunu sorgular.
 * @param int $user_id Kullanıcı ID
 * @return int Okunmamış bildirim sayısı
 */
function get_unread_notifications_count(int $user_id): int {
    global $db;
    $stmt = $db->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->execute([$user_id]);
    return (int)$stmt->fetchColumn();
}

/**
 * Yeni bildirim ekler.
 * @param int $user_id Kullanıcı ID
 * @param string $message Bildirim mesajı
 * @return void
 */
function add_notification(int $user_id, string $message): void {
    global $db;
    $stmt = $db->prepare("INSERT INTO notifications (user_id, message, is_read, created_at) VALUES (?, ?, 0, NOW())");
    $stmt->execute([$user_id, $message]);
}

/**
 * Bildirimleri listeler (opsiyonel: okunmuş veya tüm bildirimler).
 * @param int $user_id Kullanıcı ID
 * @param bool $only_unread Sadece okunmamış bildirimleri getir (default false)
 * @return array Bildirimler dizisi
 */
function get_notifications(int $user_id, bool $only_unread = false): array {
    global $db;
    if ($only_unread) {
        $stmt = $db->prepare("SELECT * FROM notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC");
        $stmt->execute([$user_id]);
    } else {
        $stmt = $db->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$user_id]);
    }
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Bildirimi okunmuş olarak işaretler.
 * @param int $user_id Kullanıcı ID
 * @param int $notification_id Bildirim ID
 * @return bool Güncelleme başarılı mı
 */
function mark_notification_read(int $user_id, int $notification_id): bool {
    global $db;
    $stmt = $db->prepare("UPDATE notifications SET is_read = 1, read_at = NOW() WHERE id = ? AND user_id = ? AND is_read = 0");
    return $stmt->execute([$notification_id, $user_id]);
}
