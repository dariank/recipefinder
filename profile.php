<?php
require __DIR__ . '/config/db.php';
session_start();

// Если пользователь не вошел, отправляем на страницу входа
if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}

$pageTitle = 'Мой профиль';
$userId = $_SESSION['user_id'];
$successMsg = '';
$errorMsg = '';

// Получаем текущие данные пользователя
$stmt = db()->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    session_destroy();
    header('Location: /login.php');
    exit;
}

// Обработка формы обновления
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $newPassword = $_POST['password'] ?? '';

    if ($name && $email) {
        // Проверяем, не занят ли новый email другим пользователем
        $stmt = db()->prepare('SELECT id FROM users WHERE email = ? AND id != ?');
        $stmt->execute([$email, $userId]);
        
        if ($stmt->fetch()) {
            $errorMsg = 'Этот email уже занят другим пользователем.';
        } else {
            // Обновляем имя и email
            $stmt = db()->prepare('UPDATE users SET name = ?, email = ? WHERE id = ?');
            $stmt->execute([$name, $email, $userId]);
            
            // Обновляем имя в сессии (чтобы в шапке сразу изменилось)
            $_SESSION['user_name'] = $name;

            // Если введен новый пароль — обновляем и его
            if (!empty($newPassword)) {
                $hash = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt = db()->prepare('UPDATE users SET password = ? WHERE id = ?');
                $stmt->execute([$hash, $userId]);
            }

            $successMsg = 'Данные успешно обновлены!';
            // Перезапрашиваем данные пользователя
            $stmt = db()->prepare('SELECT * FROM users WHERE id = ?');
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
        }
    } else {
        $errorMsg = 'Имя и Email не могут быть пустыми.';
    }
}

require __DIR__ . '/includes/header.php';
?>

<div class="profile-container">
    <h1 class="page-title">Мой профиль</h1>

    <?php if ($successMsg): ?>
        <div style="background: #e8f5e9; color: #2e7d32; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #c8e6c9;">
            <i class="fa-solid fa-check-circle"></i> <?= $successMsg ?>
        </div>
    <?php endif; ?>

    <?php if ($errorMsg): ?>
        <div style="background: #ffebee; color: #c62828; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #ffcdd2;">
            <i class="fa-solid fa-exclamation-circle"></i> <?= $errorMsg ?>
        </div>
    <?php endif; ?>

    <form method="POST" class="profile-form">
        <div class="form-group">
            <label>Ваше имя</label>
            <input type="text" name="name" required value="<?= htmlspecialchars($user['name']) ?>">
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" required value="<?= htmlspecialchars($user['email']) ?>">
        </div>

        <div class="form-group">
            <label>Новый пароль (оставьте пустым, если не хотите менять)</label>
            <input type="password" name="password" placeholder="••••••••">
        </div>

        <div style="display: flex; gap: 16px; margin-top: 30px;">
            <button type="submit" class="btn-primary" style="border: none; cursor: pointer; flex: 1;">
                Сохранить изменения
            </button>
            <a href="/logout.php" class="btn-outline" style="display: flex; justify-content: center; align-items: center; text-decoration: none; color: #e63946; border-color: #e63946;">
                Выйти из аккаунта
            </a>
        </div>
    </form>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>