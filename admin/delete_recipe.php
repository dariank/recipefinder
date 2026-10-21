<?php
require __DIR__ . '/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    $stmt = db()->prepare('DELETE FROM recipes WHERE id = ?');
    $stmt->execute([$id]);
    header('Location: /admin/recipes.php?msg=Рецепт удален');
    exit;
}
header('Location: /admin/recipes.php');
exit;