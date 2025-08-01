<?php
require 'inc/db.php';

$email = 'admin@example.com';  // Yeni admin email
$password = 'Sifre123!';        // Güçlü bir şifre

$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$stmt = $db->prepare("INSERT INTO users (email, password, role) VALUES (?, ?, 'admin')");
try {
    $stmt->execute([$email, $hashed_password]);
    echo "Admin kullanıcı oluşturuldu: $email";
} catch (Exception $e) {
    echo "Hata: " . $e->getMessage();
}
