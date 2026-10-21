<?php
require __DIR__ . '/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    
    // Нельзя удалить самого себя
    if ($id != $_SESSION['user_id']) {
        $stmt = db()->prepare('DELETE FROM users WHERE id = ?');
        $stmt->execute([$id]);
        header('Location: /admin/users.php?msg=Пользователь удален');
        exit;
    }
}
header('Location: /admin/users.php');
exit;