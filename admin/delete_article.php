<?php
require __DIR__ . '/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    $stmt = db()->prepare('DELETE FROM articles WHERE id = ?');
    $stmt->execute([$id]);
    header('Location: /admin/articles.php?msg=Статья удалена');
    exit;
}
header('Location: /admin/articles.php');
exit;