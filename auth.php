<?php
session_start();
require __DIR__ . '/../config/db.php';

// Если не вошел или не админ — выкидываем на главную
if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}

$stmt = db()->prepare('SELECT is_admin FROM users WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user || $user['is_admin'] != 1) {
    die('Доступ запрещен. Вы не администратор. <a href="/">На главную</a>');
}
?>