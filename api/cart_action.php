<?php
// ПРАВИЛЬНЫЙ путь к базе данных (выходим из папки api на уровень выше)
require __DIR__ . '/../config/db.php'; 
session_start();

header('Content-Type: application/json');

// Проверяем, авторизован ли пользователь
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Не авторизован']);
    exit;
}

$userId = $_SESSION['user_id'];
$recipeId = isset($_POST['recipe_id']) ? (int)$_POST['recipe_id'] : 0;
$action = $_POST['action'] ?? '';

if ($recipeId > 0) {
    try {
        if ($action === 'add') {
            // INSERT IGNORE не даст добавить дубликат
            $stmt = db()->prepare('INSERT IGNORE INTO user_favorites (user_id, recipe_id) VALUES (?, ?)');
            $stmt->execute([$userId, $recipeId]);
        } elseif ($action === 'remove') {
            $stmt = db()->prepare('DELETE FROM user_favorites WHERE user_id = ? AND recipe_id = ?');
            $stmt->execute([$userId, $recipeId]);
        }
        
        echo json_encode(['status' => 'success']);
        exit;
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        exit;
    }
}

echo json_encode(['status' => 'error', 'message' => 'Неверный ID рецепта']);
exit;